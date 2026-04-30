<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_pages_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    api_json(['ok' => false, 'error' => 'ID inválido.'], 422);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'SELECT id, slug, title, content_html, status, seo_title, seo_description, published_at, created_at, updated_at
     FROM custom_pages
     WHERE id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($page)) {
    api_json(['ok' => false, 'error' => 'Página no encontrada.'], 404);
}

api_json(['ok' => true, 'item' => $page]);
