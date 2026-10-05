<?php

declare(strict_types=1);

final class JsonDatabase
{
    public function __construct(private string $filePath)
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the JSON database directory.');
        }

        if (!is_file($this->filePath)) {
            $handle = @fopen($this->filePath, 'x');
            if ($handle !== false) {
                fwrite($handle, $this->encode($this->emptyDatabase()));
                fclose($handle);
            }
        }
    }

    public function read(callable $callback): mixed
    {
        return $this->withData(LOCK_SH, $callback, false);
    }

    public function transaction(callable $callback): mixed
    {
        return $this->withData(LOCK_EX, $callback, true);
    }

    private function withData(int $lockType, callable $callback, bool $persist): mixed
    {
        $handle = @fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Could not open the JSON database.');
        }

        if (!flock($handle, $lockType)) {
            fclose($handle);
            throw new RuntimeException('Could not lock the JSON database.');
        }

        try {
            rewind($handle);
            $contents = stream_get_contents($handle);
            if ($contents === false || trim($contents) === '') {
                throw new RuntimeException('The JSON database is empty or unreadable.');
            }

            try {
                $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException('The JSON database contains invalid JSON.', 0, $exception);
            }

            if (!is_array($data) || !isset($data['subjects'], $data['courses'], $data['nextIds'])) {
                throw new RuntimeException('The JSON database has an invalid structure.');
            }

            $result = $callback($data);
            if ($persist) {
                rewind($handle);
                if (!ftruncate($handle, 0)) {
                    throw new RuntimeException('Could not update the JSON database.');
                }
                $json = $this->encode($data);
                if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
                    throw new RuntimeException('Could not write the JSON database.');
                }
            }

            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
    }

    private function emptyDatabase(): array
    {
        return [
            'subjects' => [],
            'courses' => [],
            'nextIds' => ['subjects' => 1, 'courses' => 1],
        ];
    }
}
