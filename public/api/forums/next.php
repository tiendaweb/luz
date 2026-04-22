<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    "SELECT id, code, title, description, platform_type, platform_url, timezone, status, speaker_json, starts_at
     FROM forums
     WHERE status = 'published'
       AND strftime('%s', starts_at) > strftime('%s', 'now')
     ORDER BY strftime('%s', starts_at) ASC
     LIMIT 1"
);
$stmt->execute();
$forum = $stmt->fetch();

if (!is_array($forum)) {
    api_json(['ok' => true, 'forum' => null]);
}

api_json([
    'ok' => true,
    'forum' => [
        'id' => (int)$forum['id'],
        'code' => (string)$forum['code'],
        'title' => (string)$forum['title'],
        'description' => (string)$forum['description'],
        'platformType' => (string)$forum['platform_type'],
        'platformUrl' => $forum['platform_url'] !== null ? (string)$forum['platform_url'] : null,
        'timezone' => (string)$forum['timezone'],
        'status' => (string)$forum['status'],
        'speakerJson' => $forum['speaker_json'] !== null ? json_decode((string)$forum['speaker_json'], true) : null,
        'startsAt' => (string)$forum['starts_at'],
    ],
]);
