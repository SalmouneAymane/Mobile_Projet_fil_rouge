<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/ApiException.php';
require_once __DIR__ . '/../src/JsonDatabase.php';
require_once __DIR__ . '/../src/Models/Course.php';
require_once __DIR__ . '/../src/Models/SchoolSubject.php';
require_once __DIR__ . '/../src/Repositories/CourseRepository.php';
require_once __DIR__ . '/../src/Repositories/SubjectRepository.php';
require_once __DIR__ . '/../src/Services/CourseService.php';
require_once __DIR__ . '/../src/Services/SubjectService.php';

try {
    $database = new JsonDatabase(__DIR__ . '/../storage/database.json');
    $controller = new ApiController(
        new CourseService(new CourseRepository($database)),
        new SubjectService(new SubjectRepository($database))
    );
    $controller->handle();
} catch (Throwable $exception) {
    error_log($exception->__toString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'The request could not be completed.']);
}

final class ApiController
{
    public function __construct(
        private CourseService $courses,
        private SubjectService $subjects
    ) {
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        try {
            $result = $this->dispatch();
            if ($result === null) {
                http_response_code(204);
                return;
            }

            $status = is_array($result) && isset($result['_status'])
                ? (int) $result['_status']
                : 200;
            unset($result['_status']);
            http_response_code($status);
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (ApiException $exception) {
            http_response_code($exception->statusCode);
            echo json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            http_response_code(500);
            echo json_encode(['error' => 'The request could not be completed.']);
        }
    }

    private function dispatch(): ?array
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
        $resource = $segments[1] ?? '';
        if (($segments[0] ?? '') !== 'api' || !in_array($resource, ['courses', 'subjects'], true)) {
            throw new ApiException('Endpoint not found.', 404);
        }

        $id = null;
        if (isset($segments[2])) {
            if (!ctype_digit($segments[2]) || (int) $segments[2] < 1) {
                throw new ApiException('Resource ID must be a positive integer.', 400);
            }
            $id = (int) $segments[2];
        }
        if (count($segments) > 3) {
            throw new ApiException('Endpoint not found.', 404);
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $service = $resource === 'courses' ? $this->courses : $this->subjects;

        if ($method === 'GET') {
            if ($id !== null) {
                throw new ApiException('Individual resource lookup is not supported.', 404);
            }

            return ['data' => $service->all()];
        }
        if ($method === 'POST' && $id === null) {
            $created = $service->create($this->readPayload());
            http_response_code(201);

            return ['data' => $created, '_status' => 201];
        }
        if ($method === 'PUT' && $id !== null) {
            return ['data' => $service->update($id, $this->readPayload())];
        }
        if ($method === 'DELETE' && $id !== null) {
            $service->delete($id);

            return null;
        }

        throw new ApiException('Method not allowed for this endpoint.', 405);
    }

    private function readPayload(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || strlen($raw) > 100_000) {
            throw new ApiException('Request body is invalid or too large.', 400);
        }

        try {
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ApiException('Request body must contain valid JSON.', 400);
        }

        if (!is_array($payload)) {
            throw new ApiException('Request body must be a JSON object.', 400);
        }

        return $payload;
    }
}
