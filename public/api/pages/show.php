<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') {
    api_json(['ok' => false, 'error' => 'slug es obligatorio.'], 422);
}

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
    api_json(['ok' => false, 'error' => 'Página no encontrada o no publicada.'], 404);
}

api_json(['ok' => true, 'item' => $page]);
