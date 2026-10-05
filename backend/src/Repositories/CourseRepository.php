<?php

declare(strict_types=1);

final class CourseRepository
{
    public function __construct(private JsonDatabase $database)
    {
    }

    public function all(): array
    {
        return $this->database->read(static function (array $data): array {
            $subjectNames = [];
            foreach ($data['subjects'] as $subject) {
                $subjectNames[(int) $subject['id']] = (string) $subject['name'];
            }

            $courses = array_map(static function (array $record) use ($subjectNames): array {
                $course = Course::fromArray($record)->toArray();
                $course['subjectName'] = $subjectNames[$course['subjectId']] ?? '';

                return $course;
            }, $data['courses']);

            usort(
                $courses,
                static fn (array $left, array $right): int => strcmp($right['createdAt'], $left['createdAt'])
            );

            return $courses;
        });
    }

    public function create(array $values): array
    {
        return $this->database->transaction(function (array &$data) use ($values): array {
            $this->assertReferencesAndSlug($data, $values['subjectId'], $values['slug']);
            $now = gmdate(DATE_ATOM);
            $course = new Course(
                (int) $data['nextIds']['courses'],
                $values['title'],
                $values['slug'],
                $values['description'],
                $values['teacherName'],
                $values['subjectId'],
                $values['status'],
                $now,
                $now
            );
            $data['nextIds']['courses']++;
            $data['courses'][] = $course->toArray();
            $result = $course->toArray();
            $result['subjectName'] = $this->subjectName($data['subjects'], $course->subjectId);

            return $result;
        });
    }

    public function update(int $id, array $values): array
    {
        return $this->database->transaction(function (array &$data) use ($id, $values): array {
            $index = self::findIndex($data['courses'], $id);
            if ($index === null) {
                throw new ApiException('Course not found.', 404);
            }
            $this->assertReferencesAndSlug($data, $values['subjectId'], $values['slug'], $id);

            $record = $data['courses'][$index];
            $course = new Course(
                $id,
                $values['title'],
                $values['slug'],
                $values['description'],
                $values['teacherName'],
                $values['subjectId'],
                $values['status'],
                (string) $record['createdAt'],
                gmdate(DATE_ATOM)
            );
            $data['courses'][$index] = $course->toArray();
            $result = $course->toArray();
            $result['subjectName'] = $this->subjectName($data['subjects'], $course->subjectId);

            return $result;
        });
    }

    public function delete(int $id): void
    {
        $this->database->transaction(static function (array &$data) use ($id): void {
            $index = self::findIndex($data['courses'], $id);
            if ($index === null) {
                throw new ApiException('Course not found.', 404);
            }

            array_splice($data['courses'], $index, 1);
        });
    }

    private function assertReferencesAndSlug(
        array $data,
        int $subjectId,
        string $slug,
        ?int $exceptId = null
    ): void {
        if ($this->subjectName($data['subjects'], $subjectId) === null) {
            throw new ApiException('Please select an existing subject.', 422);
        }

        foreach ($data['courses'] as $course) {
            if (strtolower((string) $course['slug']) === strtolower($slug)
                && (int) $course['id'] !== $exceptId) {
                throw new ApiException('A course with this slug already exists.', 409);
            }
        }
    }

    private function subjectName(array $subjects, int $id): ?string
    {
        foreach ($subjects as $subject) {
            if ((int) $subject['id'] === $id) {
                return (string) $subject['name'];
            }
        }

        return null;
    }

    private static function findIndex(array $courses, int $id): ?int
    {
        foreach ($courses as $index => $course) {
            if ((int) $course['id'] === $id) {
                return $index;
            }
        }

        return null;
    }
}
