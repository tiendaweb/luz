<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$action = $_GET['action'] ?? null;
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');

$routes = [
    'api/auth/login' => __DIR__ . '/api/auth/login.php',
    'api/auth/logout' => __DIR__ . '/api/auth/logout.php',
    'api/auth/me' => __DIR__ . '/api/auth/me.php',
    'api/registrations/create' => __DIR__ . '/api/registrations/create.php',
    'api/dashboard/summary' => __DIR__ . '/api/dashboard/summary.php',
];

$routeKey = null;

if (is_string($action) && isset($routes[$action])) {
    $routeKey = $action;
} else {
    $normalizedPath = str_starts_with($path, 'public/') ? substr($path, 7) : $path;
    $normalizedPath = trim($normalizedPath, '/');
    if (isset($routes[$normalizedPath])) {
        $routeKey = $normalizedPath;
    }
}

if ($routeKey !== null) {
    require $routes[$routeKey];
    exit;
}

$publicAsset = $path === '' ? 'index.html' : $path;
$candidate = realpath($projectRoot . '/' . $publicAsset);
$rootRealpath = realpath($projectRoot);
$isInsideProject = $candidate !== false && $rootRealpath !== false && str_starts_with($candidate, $rootRealpath . DIRECTORY_SEPARATOR);

if ($isInsideProject && is_file($candidate)) {
    $mimeType = mime_content_type($candidate) ?: 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    readfile($candidate);
    exit;
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => false,
    'error' => 'Ruta no encontrada',
    'hint' => 'Use /api/*, ?action=api/... o recursos web existentes',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
