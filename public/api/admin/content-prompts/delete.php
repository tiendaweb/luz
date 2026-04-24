<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_content_prompts_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'DELETE') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    api_json(['ok' => false, 'error' => 'ID inválido.'], 422);
}

$pdo = api_require_db();
$stmt = $pdo->prepare('DELETE FROM content_prompt_templates WHERE id = :id');
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    api_json(['ok' => false, 'error' => 'Preset no encontrado.'], 404);
}

api_json(['ok' => true]);
