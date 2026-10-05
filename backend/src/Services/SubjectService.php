<?php

declare(strict_types=1);

final class SubjectService
{
    public function __construct(private SubjectRepository $subjects)
    {
    }

    public function all(): array
    {
        return $this->subjects->all();
    }

    public function create(array $input): array
    {
        [$name, $description] = $this->validate($input);

        return $this->subjects->create($name, $description);
    }

    public function update(int $id, array $input): array
    {
        [$name, $description] = $this->validate($input);

        return $this->subjects->update($id, $name, $description);
    }

    public function delete(int $id): void
    {
        $this->subjects->delete($id);
    }

    private function validate(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($name === '') {
            throw new ApiException('Subject name is required.', 422);
        }
        if (strlen($name) > 150) {
            throw new ApiException('Subject name must be 150 characters or fewer.', 422);
        }
        if (strlen($description) > 2000) {
            throw new ApiException('Subject description must be 2,000 characters or fewer.', 422);
        }

        return [$name, $description];
    }
}
