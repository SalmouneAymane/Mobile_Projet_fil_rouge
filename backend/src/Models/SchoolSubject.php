<?php

declare(strict_types=1);

final class SchoolSubject
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromArray(array $record): self
    {
        return new self(
            (int) $record['id'],
            (string) $record['name'],
            (string) ($record['description'] ?? ''),
            (string) $record['createdAt'],
            (string) $record['updatedAt']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
