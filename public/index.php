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
    'api/registrations/me' => __DIR__ . '/api/registrations/me.php',
    'api/dashboard/summary' => __DIR__ . '/api/dashboard/summary.php',
    'api/forums/list' => __DIR__ . '/api/forums/list.php',
    'api/forums/next' => __DIR__ . '/api/forums/next.php',
    'api/forums/detail' => __DIR__ . '/api/forums/detail.php',
    'api/referrals/offer' => __DIR__ . '/api/referrals/offer.php',
    'api/associate/offer' => __DIR__ . '/api/associate/offer.php',
    'api/associate/referral-link' => __DIR__ . '/api/associate/referral-link.php',
    'api/associate/payment-methods' => __DIR__ . '/api/associate/payment-methods.php',
    'api/associate/registrations' => __DIR__ . '/api/associate/registrations.php',
    'api/associate/network-trace' => __DIR__ . '/api/associate/network-trace.php',
    'api/admin/registrations' => __DIR__ . '/api/admin/registrations.php',
    'api/admin/associates' => __DIR__ . '/api/admin/associates.php',
    'api/admin/network-trace' => __DIR__ . '/api/admin/network-trace.php',
    'api/admin/attendance' => __DIR__ . '/api/admin/attendance.php',
    'api/associate/attendance' => __DIR__ . '/api/associate/attendance.php',
    'api/user/ebooks' => __DIR__ . '/api/user/ebooks.php',
    'api/user/ebooks_download' => __DIR__ . '/api/user/ebooks_download.php',
    'api/blog/list' => __DIR__ . '/api/blog/list.php',
    'api/admin/pages/list' => __DIR__ . '/api/admin/pages/list.php',
    'api/admin/pages/show' => __DIR__ . '/api/admin/pages/show.php',
    'api/admin/pages/create' => __DIR__ . '/api/admin/pages/create.php',
    'api/admin/pages/update' => __DIR__ . '/api/admin/pages/update.php',
    'api/admin/pages/delete' => __DIR__ . '/api/admin/pages/delete.php',
    'api/admin/blog/list' => __DIR__ . '/api/admin/blog/list.php',
    'api/admin/blog/create' => __DIR__ . '/api/admin/blog/create.php',
    'api/admin/blog/update' => __DIR__ . '/api/admin/blog/update.php',
    'api/admin/blog/delete' => __DIR__ . '/api/admin/blog/delete.php',
    'api/admin/content-prompts/list' => __DIR__ . '/api/admin/content-prompts/list.php',
    'api/admin/content-prompts/create' => __DIR__ . '/api/admin/content-prompts/create.php',
    'api/admin/content-prompts/delete' => __DIR__ . '/api/admin/content-prompts/delete.php',
    'api/admin/content-blocks' => __DIR__ . '/api/admin/content-blocks.php',
    'api/admin/settings' => __DIR__ . '/api/admin/settings.php',
    'api/admin/users' => __DIR__ . '/api/admin/users.php',
    'api/admin/certificates' => __DIR__ . '/api/admin/certificates.php',
    'api/admin/signatures' => __DIR__ . '/api/admin/signatures.php',
];

$routeKey = null;

if (is_string($action) && isset($routes[$action])) {
    $routeKey = $action;
} else {
    $normalizedPath = str_starts_with($path, 'public/') ? substr($path, 7) : $path;
    $normalizedPath = trim($normalizedPath, '/');
    if (str_ends_with($normalizedPath, '.php')) {
        $normalizedPath = substr($normalizedPath, 0, -4);
    }
    if (isset($routes[$normalizedPath])) {
        $routeKey = $normalizedPath;
    }
}

if ($routeKey !== null) {
    require $routes[$routeKey];
    exit;
}

$normalizedPath = str_starts_with($path, 'public/') ? substr($path, 7) : $path;
$normalizedPath = trim($normalizedPath, '/');

if ($normalizedPath === 'admin/certificate-view' || $normalizedPath === 'certificate-view') {
    require __DIR__ . '/api/admin/certificate-view.php';
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


$pageRoutes = [
    'index'       => $projectRoot . '/index.php',
    'login'       => $projectRoot . '/login.php',
    'foros'       => $projectRoot . '/app/Views/pages/forums.php',
    'directora'   => $projectRoot . '/app/Views/pages/about.php',
    'blog'        => $projectRoot . '/app/Views/pages/blog.php',
    'dashboard'   => $projectRoot . '/app/Views/pages/dashboard.php',
    'inscripcion' => $projectRoot . '/app/Views/pages/inscription.php',
];

$legacyRedirects = [
    'contacto'          => '/directora',
    'dashboard-admin'   => '/dashboard',
    'dashboard-asociado'=> '/dashboard',
    'dashboard-usuario' => '/dashboard',
];

$pageKey = $path === '' ? 'index' : trim($path, '/');
if (str_ends_with($pageKey, '.php')) {
    $pageKey = substr($pageKey, 0, -4);
}

if (isset($legacyRedirects[$pageKey])) {
    header('Location: ' . $legacyRedirects[$pageKey], true, 302);
    exit;
}

if (isset($pageRoutes[$pageKey])) {
    require $pageRoutes[$pageKey];
    exit;
}

$publicAsset = $path;
$candidate = realpath($projectRoot . '/' . $publicAsset);
$rootRealpath = realpath($projectRoot);
$isInsideProject = $candidate !== false && $rootRealpath !== false && str_starts_with($candidate, $rootRealpath . DIRECTORY_SEPARATOR);

if ($isInsideProject && is_file($candidate)) {
    if (pathinfo($candidate, PATHINFO_EXTENSION) === 'php') {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'Ruta PHP no encontrada',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

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
