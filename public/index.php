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
    'api/forums/list' => __DIR__ . '/api/forums/list.php',
    'api/forums/next' => __DIR__ . '/api/forums/next.php',
    'api/forums/detail' => __DIR__ . '/api/forums/detail.php',
    'api/referrals/offer' => __DIR__ . '/api/referrals/offer.php',
    'api/associate/offer' => __DIR__ . '/api/associate/offer.php',
    'api/associate/registrations' => __DIR__ . '/api/associate/registrations.php',
    'api/admin/registrations' => __DIR__ . '/api/admin/registrations.php',
    'api/admin/associates' => __DIR__ . '/api/admin/associates.php',
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


if (preg_match('#^p/([a-z0-9]+(?:-[a-z0-9]+)*)$#', $path, $matches) === 1) {
    require_once $projectRoot . '/app/Database/connection.php';
    require_once $projectRoot . '/public/api/_bootstrap.php';

    $slug = (string)$matches[1];
    $pdo = api_require_db();
    $stmt = $pdo->prepare(
        'SELECT id, slug, title, content_html, seo_title, seo_description, published_at, created_at, updated_at
         FROM custom_pages
         WHERE slug = :slug AND status = "published"
         LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($page)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Página no encontrada';
        exit;
    }

    require $projectRoot . '/app/Views/pages/show.php';
    exit;
}

if ($path === '' || $path === 'index.php') {
    require $projectRoot . '/app/Views/home.php';
    exit;
}

$publicAsset = $path;
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
