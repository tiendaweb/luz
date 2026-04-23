<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'SELECT id, slug, title, excerpt, content_html, published_at, created_at
     FROM blog_posts
     WHERE status = :status
     ORDER BY COALESCE(published_at, created_at) DESC, id DESC'
);
$stmt->execute(['status' => 'published']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

api_json(['ok' => true, 'items' => $rows]);
