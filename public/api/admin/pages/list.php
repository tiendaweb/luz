<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_pages_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$rows = $pdo->query(
    'SELECT id, slug, title, content_html, status, seo_title, seo_description, published_at, created_at, updated_at
     FROM custom_pages
     ORDER BY updated_at DESC, id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

api_json(['ok' => true, 'items' => $rows]);
