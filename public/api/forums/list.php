<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(1, min(20, (int)($_GET['per_page'] ?? 6)));
$offset = ($page - 1) * $perPage;

$pdo = api_require_db();
$total = (int)$pdo->query("SELECT COUNT(*) FROM forums WHERE status = 'published'")->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT id, code, title, description, platform_type, platform_url, timezone, status, speaker_json, starts_at
     FROM forums
     WHERE status = 'published'
     ORDER BY strftime('%s', starts_at) ASC
     LIMIT :limit OFFSET :offset"
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

$normalized = array_map(
    static fn(array $forum): array => [
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
    $items
);

api_json([
    'ok' => true,
    'items' => $normalized,
    'pagination' => [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => max(1, (int)ceil($total / $perPage)),
    ],
]);
