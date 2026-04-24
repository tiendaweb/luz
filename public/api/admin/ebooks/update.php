<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_ebooks_require_admin();
api_require_method(['PATCH']);

$input = api_read_json();
$id = (int)($input['id'] ?? 0);
if ($id < 1) {
    api_error('ID inválido.', 422, 'validation_error', ['field' => 'id']);
}

$pdo = api_require_db();
$stmt = $pdo->prepare('SELECT * FROM ebooks WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if (!is_array($existing)) {
    api_error('Ebook no encontrado.', 404, 'not_found');
}

$merged = [
    'title' => $input['title'] ?? $existing['title'],
    'description' => $input['description'] ?? $existing['description'],
    'status' => $input['status'] ?? $existing['status'],
    'provider' => $input['provider'] ?? $existing['provider'],
    'local_path' => $input['local_path'] ?? $existing['local_path'],
    'external_url' => $input['external_url'] ?? $existing['external_url'],
    'min_attendance' => $input['min_attendance'] ?? $existing['min_attendance'],
    'requires_approved' => $input['requires_approved'] ?? $existing['requires_approved'],
];
$payload = admin_ebooks_normalize_payload($merged);

$update = $pdo->prepare(
    'UPDATE ebooks
     SET title = :title,
         description = :description,
         status = :status,
         provider = :provider,
         local_path = :local_path,
         external_url = :external_url,
         min_attendance = :min_attendance,
         requires_approved = :requires_approved,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = :id'
);
$update->execute([
    'id' => $id,
    'title' => $payload['title'],
    'description' => $payload['description'],
    'status' => $payload['status'],
    'provider' => $payload['provider'],
    'local_path' => $payload['local_path'] !== '' ? $payload['local_path'] : null,
    'external_url' => $payload['external_url'] !== '' ? $payload['external_url'] : null,
    'min_attendance' => $payload['min_attendance'],
    'requires_approved' => $payload['requires_approved'],
]);

api_json(['ok' => true]);
