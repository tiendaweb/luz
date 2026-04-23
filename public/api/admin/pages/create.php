<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_pages_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$data = admin_pages_normalize_payload(api_read_json());

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'INSERT INTO custom_pages (slug, title, content_html, status, seo_title, seo_description, published_at, updated_at)
     VALUES (:slug, :title, :content_html, :status, :seo_title, :seo_description, :published_at, CURRENT_TIMESTAMP)'
);

try {
    $stmt->execute($data);
} catch (Throwable $e) {
    api_json(['ok' => false, 'error' => 'No se pudo crear la página (slug duplicado o datos inválidos).'], 422);
}

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()], 201);
