<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

api_require_role('admin');
api_require_method(['PATCH', 'PUT']);

$input = api_read_json();
api_require_fields($input, ['id', 'slug', 'title', 'excerpt', 'content_html']);

$id = (int)($input['id'] ?? 0);
$slug = api_input_string($input, 'slug', true);
$title = api_input_string($input, 'title', true);
$excerpt = api_input_string($input, 'excerpt', true);
$contentHtml = api_input_string($input, 'content_html', true);
$status = api_input_string($input, 'status') ?: 'draft';
$publishedAt = api_input_string($input, 'published_at');

if ($id < 1) {
    api_error('ID inválido.', 422, 'validation_error', ['field' => 'id']);
}
if (!api_validate_slug($slug)) {
    api_error('Slug inválido.', 422, 'validation_error', ['field' => 'slug']);
}
if (!in_array($status, ['draft', 'published'], true)) {
    api_error('Estado inválido.', 422, 'validation_error', ['field' => 'status']);
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
    api_error('No se pudo actualizar el artículo.', 422, 'blog_update_failed');
}

if ($stmt->rowCount() < 1) {
    api_error('Artículo no encontrado.', 404, 'not_found');
}

api_json(['ok' => true]);
