<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

$user = api_require_role('admin');
api_require_method(['POST']);

$input = api_read_json();
api_require_fields($input, ['slug', 'title', 'excerpt', 'content_html']);

$slug = api_input_string($input, 'slug', true);
$title = api_input_string($input, 'title', true);
$excerpt = api_input_string($input, 'excerpt', true);
$contentHtml = api_input_string($input, 'content_html', true);
$status = api_input_string($input, 'status') ?: 'draft';
$publishedAt = api_input_string($input, 'published_at');

if (!api_validate_slug($slug)) {
    api_error('Slug inválido. Usa minúsculas, números y guiones.', 422, 'validation_error', ['field' => 'slug']);
}

if (!in_array($status, ['draft', 'published'], true)) {
    api_error('Estado inválido.', 422, 'validation_error', ['field' => 'status']);
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
    api_error('No se pudo crear el artículo (slug duplicado o datos inválidos).', 422, 'blog_create_failed');
}

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()], 201);
