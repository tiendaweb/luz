<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_pages_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'DELETE') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    api_json(['ok' => false, 'error' => 'ID inválido.'], 422);
}

$pdo = api_require_db();
$stmt = $pdo->prepare('DELETE FROM custom_pages WHERE id = :id');
$stmt->execute(['id' => $id]);

api_json(['ok' => true]);
