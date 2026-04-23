<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'PATCH' && $method !== 'PUT') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$id = (int)($input['id'] ?? 0);
$slug = trim((string)($input['slug'] ?? ''));
$title = trim((string)($input['title'] ?? ''));
$excerpt = trim((string)($input['excerpt'] ?? ''));
$contentHtml = trim((string)($input['content_html'] ?? ''));
$status = trim((string)($input['status'] ?? 'draft'));
$publishedAt = trim((string)($input['published_at'] ?? ''));

if ($id < 1 || $slug === '' || $title === '' || $excerpt === '' || $contentHtml === '') {
    api_json(['ok' => false, 'error' => 'id, slug, title, excerpt y content_html son obligatorios.'], 422);
}

if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
    api_json(['ok' => false, 'error' => 'Slug inválido.'], 422);
}

if (!in_array($status, ['draft', 'published'], true)) {
    api_json(['ok' => false, 'error' => 'Estado inválido.'], 422);
}

if ($status === 'published' && $publishedAt === '') {
    $publishedAt = gmdate('Y-m-d\TH:i:s\Z');
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'UPDATE blog_posts
     SET slug = :slug,
         title = :title,
         excerpt = :excerpt,
         content_html = :content_html,
         status = :status,
         published_at = :published_at,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = :id'
);

try {
    $stmt->execute([
        'id' => $id,
        'slug' => $slug,
        'title' => $title,
        'excerpt' => $excerpt,
        'content_html' => $contentHtml,
        'status' => $status,
        'published_at' => $publishedAt !== '' ? $publishedAt : null,
    ]);
} catch (Throwable $e) {
    api_json(['ok' => false, 'error' => 'No se pudo actualizar el artículo.'], 422);
}

if ($stmt->rowCount() < 1) {
    api_json(['ok' => false, 'error' => 'Artículo no encontrado.'], 404);
}

api_json(['ok' => true]);
