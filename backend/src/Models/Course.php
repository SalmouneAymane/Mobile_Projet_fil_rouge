<?php

declare(strict_types=1);

final class Course
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $description,
        public string $teacherName,
        public int $subjectId,
        public string $status,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromArray(array $record): self
    {
        return new self(
            (int) $record['id'],
            (string) $record['title'],
            (string) $record['slug'],
            (string) ($record['description'] ?? ''),
            (string) $record['teacherName'],
            (int) $record['subjectId'],
            (string) $record['status'],
            (string) $record['createdAt'],
            (string) $record['updatedAt']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'teacherName' => $this->teacherName,
            'subjectId' => $this->subjectId,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
