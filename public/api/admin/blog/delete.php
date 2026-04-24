<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'DELETE') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    api_json(['ok' => false, 'error' => 'ID inválido.'], 422);
}

$pdo = api_require_db();
$stmt = $pdo->prepare('DELETE FROM blog_posts WHERE id = :id');
$stmt->execute(['id' => $id]);

api_json(['ok' => true]);
