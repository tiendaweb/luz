<?php

declare(strict_types=1);

require_once __DIR__ . '/../_ebook_access.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$user = api_current_user();
if (!$user) {
    api_json(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
}

$pdo = api_require_db();
$userId = (int)$user['id'];

$stmt = $pdo->prepare(
    "SELECT
        ebooks.id,
        ebooks.title,
        ebooks.description,
        ebooks.status,
        ebooks.provider,
        ebooks.min_attendance,
        ebooks.requires_approved,
        ebooks.created_at,
        forums.id AS forum_id,
        forums.title AS forum_title,
        forums.code AS forum_code
     FROM forum_ebooks
     INNER JOIN ebooks
       ON ebooks.id = forum_ebooks.ebook_id
      AND ebooks.status = 'published'
     INNER JOIN forums
       ON forums.id = forum_ebooks.forum_id
     WHERE forum_ebooks.is_active = 1
     GROUP BY ebooks.id, forums.id
     ORDER BY ebooks.created_at DESC, ebooks.id DESC, forums.starts_at DESC, forums.id DESC"
);
$stmt->execute(['user_id' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = [];
foreach ($rows as $ebook) {
    $forumId = (int)$ebook['forum_id'];
    $permission = api_user_ebook_permission($pdo, $userId, $ebook, $forumId);
    $ebookId = (int)$ebook['id'];

    api_ebook_log_access(
        $pdo,
        $userId,
        $forumId,
        $ebookId,
        (bool)($permission['has_access'] ?? false),
        (string)($permission['reason'] ?? 'Sin motivo explícito'),
        'list'
    );

    $downloadUrl = null;
    if (($permission['has_access'] ?? false) === true) {
        $expiresAt = time() + (10 * 60);
        $token = api_ebook_sign_token($userId, $ebookId, $forumId, $expiresAt);
        $downloadUrl = sprintf('/api/user/ebooks_download.php?ebook_id=%d&forum_id=%d&expires=%d&token=%s', $ebookId, $forumId, $expiresAt, $token);
    }

    $items[] = [
        'id' => $ebookId,
        'title' => (string)$ebook['title'],
        'description' => (string)($ebook['description'] ?? ''),
        'provider' => (string)$ebook['provider'],
        'forum_id' => $forumId,
        'forum_title' => (string)($ebook['forum_title'] ?? ''),
        'forum_code' => (string)($ebook['forum_code'] ?? ''),
        'min_attendance' => (float)$ebook['min_attendance'],
        'requires_approved' => (int)$ebook['requires_approved'] === 1,
        'has_access' => (bool)($permission['has_access'] ?? false),
        'access_reason' => (string)($permission['reason'] ?? ''),
        'access_via' => (string)($permission['via'] ?? 'none'),
        'attendance_percent' => (float)($permission['attendance_percent'] ?? 0),
        'download_url' => $downloadUrl,
    ];
}

api_json([
    'ok' => true,
    'items' => $items,
    'policy' => [
        'grant_if' => 'attendance>=umbral OR autorización manual OR estado premium',
    ],
]);
