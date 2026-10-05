<?php

declare(strict_types=1);

final class SubjectRepository
{
    public function __construct(private JsonDatabase $database)
    {
    }

    public function all(): array
    {
        return $this->database->read(
            static fn (array $data): array => array_map(
                static fn (array $record): array => SchoolSubject::fromArray($record)->toArray(),
                $data['subjects']
            )
        );
    }

    public function create(string $name, string $description): array
    {
        return $this->database->transaction(function (array &$data) use ($name, $description): array {
            $this->assertNameAvailable($data['subjects'], $name);
            $now = gmdate(DATE_ATOM);
            $subject = new SchoolSubject(
                (int) $data['nextIds']['subjects'],
                $name,
                $description,
                $now,
                $now
            );
            $data['nextIds']['subjects']++;
            $data['subjects'][] = $subject->toArray();

            return $subject->toArray();
        });
    }

    public function update(int $id, string $name, string $description): array
    {
        return $this->database->transaction(function (array &$data) use ($id, $name, $description): array {
            $index = self::findIndex($data['subjects'], $id);
            if ($index === null) {
                throw new ApiException('Subject not found.', 404);
            }
            $this->assertNameAvailable($data['subjects'], $name, $id);

            $record = $data['subjects'][$index];
            $subject = new SchoolSubject(
                $id,
                $name,
                $description,
                (string) $record['createdAt'],
                gmdate(DATE_ATOM)
            );
            $data['subjects'][$index] = $subject->toArray();

            return $subject->toArray();
        });
    }

    public function delete(int $id): void
    {
        $this->database->transaction(static function (array &$data) use ($id): void {
            $index = self::findIndex($data['subjects'], $id);
            if ($index === null) {
                throw new ApiException('Subject not found.', 404);
            }

            foreach ($data['courses'] as $course) {
                if ((int) $course['subjectId'] === $id) {
                    throw new ApiException('This subject is assigned to a course and cannot be deleted.', 409);
                }
            }

            array_splice($data['subjects'], $index, 1);
        });
    }

    private function assertNameAvailable(array $subjects, string $name, ?int $exceptId = null): void
    {
        foreach ($subjects as $subject) {
            $sameName = function_exists('mb_strtolower')
                ? mb_strtolower((string) $subject['name']) === mb_strtolower($name)
                : strtolower((string) $subject['name']) === strtolower($name);
            if ($sameName && (int) $subject['id'] !== $exceptId) {
                throw new ApiException('A subject with this name already exists.', 409);
            }
        }
    }

    private static function findIndex(array $subjects, int $id): ?int
    {
        foreach ($subjects as $index => $subject) {
            if ((int) $subject['id'] === $id) {
                return $index;
            }
        }

        return null;
    }
}
