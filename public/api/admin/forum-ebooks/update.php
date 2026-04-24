<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_forum_ebooks_require_admin();
api_require_method(['PATCH']);

$input = api_read_json();
$id = (int)($input['id'] ?? 0);
$isActive = array_key_exists('is_active', $input) ? ((int)(bool)$input['is_active']) : -1;

if ($id < 1 || $isActive < 0) {
    api_error('id e is_active son obligatorios.', 422, 'validation_error');
}

$pdo = api_require_db();
$stmt = $pdo->prepare('UPDATE forum_ebooks SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
$stmt->execute(['id' => $id, 'is_active' => $isActive]);

api_json(['ok' => true]);
