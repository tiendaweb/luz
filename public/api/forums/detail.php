<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$forumId = (int)($_GET['id'] ?? 0);
if ($forumId <= 0) {
    api_json(['ok' => false, 'error' => 'Parámetro id inválido'], 400);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    "SELECT id, code, title, description, platform_type, platform_url, timezone, status, speaker_json, starts_at,
            objective, topics_json, modality, requirements, seats_total, seats_available, cta_label, cta_url
     FROM forums
     WHERE id = :id
       AND status = 'published'
     LIMIT 1"
);
$stmt->execute(['id' => $forumId]);
$forum = $stmt->fetch();

if (!is_array($forum)) {
    api_json(['ok' => false, 'error' => 'Foro no encontrado'], 404);
}

$guestStmt = $pdo->prepare(
    'SELECT full_name, role, bio
     FROM forum_guests
     WHERE forum_id = :forum_id
     ORDER BY sort_order ASC, id ASC'
);
$guestStmt->execute(['forum_id' => $forumId]);
$guests = $guestStmt->fetchAll();

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
        'objective' => (string)$forum['objective'],
        'topics' => $forum['topics_json'] !== null ? json_decode((string)$forum['topics_json'], true) : [],
        'guests' => array_map(static fn(array $guest): array => [
            'name' => (string)$guest['full_name'],
            'role' => (string)$guest['role'],
            'bio' => (string)$guest['bio'],
        ], $guests),
        'modality' => (string)$forum['modality'],
        'requirements' => $forum['requirements'] !== null ? (string)$forum['requirements'] : '',
        'seatsTotal' => (int)$forum['seats_total'],
        'seatsAvailable' => (int)$forum['seats_available'],
        'ctaLabel' => (string)$forum['cta_label'],
        'ctaUrl' => $forum['cta_url'] !== null ? (string)$forum['cta_url'] : null,
    ],
]);
