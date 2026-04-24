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

$stmt = $pdo->query(
    "SELECT id, title, description, status, provider, min_attendance, requires_approved, created_at
     FROM ebooks
     WHERE status = 'published'
     ORDER BY created_at DESC, id DESC"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = [];
foreach ($rows as $ebook) {
    $permission = api_user_ebook_permission($pdo, $userId, $ebook);
    $ebookId = (int)$ebook['id'];

    $downloadUrl = null;
    if (($permission['has_access'] ?? false) === true) {
        $expiresAt = time() + (10 * 60);
        $token = api_ebook_sign_token($userId, $ebookId, $expiresAt);
        $downloadUrl = sprintf('/api/user/ebooks_download.php?ebook_id=%d&expires=%d&token=%s', $ebookId, $expiresAt, $token);
    }

    $items[] = [
        'id' => $ebookId,
        'title' => (string)$ebook['title'],
        'description' => (string)($ebook['description'] ?? ''),
        'provider' => (string)$ebook['provider'],
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
        'grant_if' => 'status=approved OR attendance>=umbral',
    ],
]);
