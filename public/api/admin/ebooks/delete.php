<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_ebooks_require_admin();
api_require_method(['DELETE']);

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    api_error('ID inválido.', 422, 'validation_error', ['field' => 'id']);
}

$pdo = api_require_db();
$stmt = $pdo->prepare('DELETE FROM ebooks WHERE id = :id');
$stmt->execute(['id' => $id]);

api_json(['ok' => true]);
