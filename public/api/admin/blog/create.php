<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$slug = trim((string)($input['slug'] ?? ''));
$title = trim((string)($input['title'] ?? ''));
$excerpt = trim((string)($input['excerpt'] ?? ''));
$contentHtml = trim((string)($input['content_html'] ?? ''));
$status = trim((string)($input['status'] ?? 'draft'));
$publishedAt = trim((string)($input['published_at'] ?? ''));

if ($slug === '' || $title === '' || $excerpt === '' || $contentHtml === '') {
    api_json(['ok' => false, 'error' => 'slug, title, excerpt y content_html son obligatorios.'], 422);
}

if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
    api_json(['ok' => false, 'error' => 'Slug inválido. Usa minúsculas, números y guiones.'], 422);
}

if (!in_array($status, ['draft', 'published'], true)) {
    api_json(['ok' => false, 'error' => 'Estado inválido.'], 422);
}

if ($status === 'published' && $publishedAt === '') {
    $publishedAt = gmdate('Y-m-d\TH:i:s\Z');
}
if ($status === 'draft') {
    $publishedAt = $publishedAt !== '' ? $publishedAt : null;
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'INSERT INTO blog_posts (slug, title, excerpt, content_html, status, author_user_id, published_at, updated_at)
     VALUES (:slug, :title, :excerpt, :content_html, :status, :author_user_id, :published_at, CURRENT_TIMESTAMP)'
);

try {
    $stmt->execute([
        'slug' => $slug,
        'title' => $title,
        'excerpt' => $excerpt,
        'content_html' => $contentHtml,
        'status' => $status,
        'author_user_id' => (int)($user['id'] ?? 0) ?: null,
        'published_at' => $publishedAt,
    ]);
} catch (Throwable $e) {
    api_json(['ok' => false, 'error' => 'No se pudo crear el artículo (slug duplicado o datos inválidos).'], 422);
}

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()], 201);
