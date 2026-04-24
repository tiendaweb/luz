<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

$user = admin_content_prompts_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$name = trim((string)($input['name'] ?? ''));
$objective = trim((string)($input['objective'] ?? ''));
$audience = trim((string)($input['audience'] ?? ''));
$tone = trim((string)($input['tone'] ?? ''));
$channel = trim((string)($input['channel'] ?? ''));
$length = trim((string)($input['length'] ?? ''));
$cta = trim((string)($input['cta'] ?? ''));
$keywords = trim((string)($input['keywords'] ?? ''));
$legal = trim((string)($input['legal'] ?? ''));

if ($name === '' || $objective === '' || $audience === '' || $tone === '' || $channel === '' || $length === '' || $cta === '' || $keywords === '' || $legal === '') {
    api_json(['ok' => false, 'error' => 'Todos los campos del preset son obligatorios.'], 422);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'INSERT INTO content_prompt_templates
      (name, objective, audience, tone, channel, length, cta, keywords, legal, created_by_user_id, updated_at)
     VALUES
      (:name, :objective, :audience, :tone, :channel, :length, :cta, :keywords, :legal, :created_by_user_id, CURRENT_TIMESTAMP)'
);

$stmt->execute([
    'name' => $name,
    'objective' => $objective,
    'audience' => $audience,
    'tone' => $tone,
    'channel' => $channel,
    'length' => $length,
    'cta' => $cta,
    'keywords' => $keywords,
    'legal' => $legal,
    'created_by_user_id' => (int)($user['id'] ?? 0) ?: null,
]);

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()], 201);
