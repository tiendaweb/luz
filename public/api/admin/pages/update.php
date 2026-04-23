<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_pages_require_admin();

api_require_method(['PATCH', 'PUT']);

$input = api_read_json();
$id = (int)($input['id'] ?? 0);
if ($id < 1) {
    api_json(['ok' => false, 'error' => 'ID inválido.'], 422);
}

$data = admin_pages_normalize_payload($input);
$data['id'] = $id;

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'UPDATE custom_pages
     SET slug = :slug,
         title = :title,
         content_html = :content_html,
         status = :status,
         seo_title = :seo_title,
         seo_description = :seo_description,
         published_at = CASE WHEN :status = "published" THEN COALESCE(published_at, :published_at) ELSE NULL END,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = :id'
);

try {
    $stmt->execute($data);
} catch (Throwable $e) {
    api_json(['ok' => false, 'error' => 'No se pudo actualizar la página.'], 422);
}

if ($stmt->rowCount() < 1) {
    api_json(['ok' => false, 'error' => 'Página no encontrada.'], 404);
}

api_json(['ok' => true]);
