<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_ebooks_require_admin();
api_require_method(['POST']);

$payload = admin_ebooks_normalize_payload(api_read_json());

if ($payload['title'] === '' || $payload['provider'] === '') {
    api_error('title y provider son obligatorios.', 422, 'validation_error');
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved, updated_at)
     VALUES (:title, :description, :status, :provider, :local_path, :external_url, :min_attendance, :requires_approved, CURRENT_TIMESTAMP)'
);
$stmt->execute([
    'title' => $payload['title'],
    'description' => $payload['description'],
    'status' => $payload['status'],
    'provider' => $payload['provider'],
    'local_path' => $payload['local_path'] !== '' ? $payload['local_path'] : null,
    'external_url' => $payload['external_url'] !== '' ? $payload['external_url'] : null,
    'min_attendance' => $payload['min_attendance'],
    'requires_approved' => $payload['requires_approved'],
]);

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()], 201);
