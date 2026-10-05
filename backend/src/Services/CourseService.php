<?php

declare(strict_types=1);

final class CourseService
{
    public function __construct(private CourseRepository $courses)
    {
    }

    public function all(): array
    {
        return $this->courses->all();
    }

    public function create(array $input): array
    {
        return $this->courses->create($this->validate($input));
    }

    public function update(int $id, array $input): array
    {
        return $this->courses->update($id, $this->validate($input));
    }

    public function delete(int $id): void
    {
        $this->courses->delete($id);
    }

    private function validate(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $slug = $this->slugify(trim((string) ($input['slug'] ?? '')));
        if ($slug === '') {
            $slug = $this->slugify($title);
        }

        $values = [
            'title' => $title,
            'slug' => $slug,
            'description' => trim((string) ($input['description'] ?? '')),
            'teacherName' => trim((string) ($input['teacherName'] ?? '')),
            'subjectId' => filter_var($input['subjectId'] ?? null, FILTER_VALIDATE_INT),
            'status' => (string) ($input['status'] ?? 'draft'),
        ];

        if ($values['title'] === '') {
            throw new ApiException('Course title is required.', 422);
        }
        if (strlen($values['title']) > 255) {
            throw new ApiException('Course title must be 255 characters or fewer.', 422);
        }
        if ($values['slug'] === '') {
            throw new ApiException('A valid slug is required.', 422);
        }
        if (strlen($values['slug']) > 255) {
            throw new ApiException('Slug must be 255 characters or fewer.', 422);
        }
        if ($values['teacherName'] === '') {
            throw new ApiException('Teacher name is required.', 422);
        }
        if (strlen($values['teacherName']) > 200) {
            throw new ApiException('Teacher name must be 200 characters or fewer.', 422);
        }
        if (strlen($values['description']) > 5000) {
            throw new ApiException('Course description must be 5,000 characters or fewer.', 422);
        }
        if ($values['subjectId'] === false || $values['subjectId'] < 1) {
            throw new ApiException('Please select a valid subject.', 422);
        }
        if (!in_array($values['status'], ['draft', 'published'], true)) {
            throw new ApiException('Status must be draft or published.', 422);
        }

        $values['subjectId'] = (int) $values['subjectId'];

        return $values;
    }

    private function slugify(string $value): string
    {
        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($transliterated !== false) {
                $value = $transliterated;
            }
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}
