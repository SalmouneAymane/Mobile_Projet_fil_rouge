<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($requestPath === '/api' || str_starts_with($requestPath, '/api/')) {
    require __DIR__ . '/backend/api/index.php';
    exit;
}

if (str_starts_with($requestPath, '/backend/')) {
    http_response_code(404);
    exit('Not found.');
}

if ($requestPath === '/' || $requestPath === '/index.php') {
    readfile(__DIR__ . '/frontend/index.html');
    exit;
}

$staticFile = realpath(__DIR__ . $requestPath);
$frontendRoot = realpath(__DIR__ . '/frontend');
if (
    str_starts_with($requestPath, '/frontend/assets/')
    && $staticFile !== false
    && $frontendRoot !== false
    && str_starts_with($staticFile, $frontendRoot . DIRECTORY_SEPARATOR)
) {
    return false;
}

http_response_code(404);
echo 'Not found.';
