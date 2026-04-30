<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_content_prompts_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$rows = $pdo->query(
    'SELECT id, name, objective, audience, tone, channel, length, cta, keywords, legal, created_by_user_id, created_at, updated_at
     FROM content_prompt_templates
     ORDER BY updated_at DESC, id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

api_json(['ok' => true, 'items' => $rows]);
