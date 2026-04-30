<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_ebook_access.php';

$user = api_require_role('admin');
api_require_method(['POST', 'DELETE']);

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'];

$input = $method === 'POST' ? api_read_json() : $_GET;
$userId = (int)($input['userId'] ?? 0);
$ebookId = (int)($input['ebookId'] ?? 0);

if ($userId < 1 || $ebookId < 1) {
    api_error('userId y ebookId son obligatorios', 422, 'validation_error');
}

if ($method === 'POST') {
    $reason = trim((string)($input['reason'] ?? 'Otorgado manualmente por admin'));
    $durationDays = (int)($input['durationDays'] ?? 0);
    $expiresAtInput = trim((string)($input['expiresAt'] ?? ''));

    $expiresAt = null;
    if ($expiresAtInput !== '') {
        $ts = strtotime($expiresAtInput);
        if ($ts === false) {
            api_error('expiresAt debe tener un formato de fecha válido', 422, 'validation_error');
        }
        $expiresAt = gmdate('c', $ts);
    } elseif ($durationDays > 0) {
        $expiresAt = gmdate('c', time() + ($durationDays * 86400));
    }

    $stmt = $pdo->prepare(
        'INSERT INTO user_ebook_access (user_id, ebook_id, access_granted, reason, granted_by_user_id, expires_at, created_at)
         VALUES (:user_id, :ebook_id, 1, :reason, :granted_by, :expires_at, :created_at)
         ON CONFLICT(user_id, ebook_id) DO UPDATE SET
           access_granted = 1,
           reason = excluded.reason,
           granted_by_user_id = excluded.granted_by_user_id,
           expires_at = excluded.expires_at'
    );
    $stmt->execute([
        'user_id' => $userId,
        'ebook_id' => $ebookId,
        'reason' => $reason,
        'granted_by' => (int)($user['id'] ?? 0),
        'expires_at' => $expiresAt,
        'created_at' => gmdate('c'),
    ]);

    api_ebook_log_access(
        $pdo,
        $userId,
        null,
        $ebookId,
        true,
        sprintf('Acceso manual otorgado por admin_id=%d; expira=%s; motivo=%s', (int)($user['id'] ?? 0), $expiresAt ?? 'sin vencimiento', $reason),
        'grant_manual'
    );

    api_json(['ok' => true, 'message' => 'Acceso otorgado', 'expires_at' => $expiresAt, 'granted_by_user_id' => (int)($user['id'] ?? 0)]);
}

$stmt = $pdo->prepare('DELETE FROM user_ebook_access WHERE user_id = :user_id AND ebook_id = :ebook_id');
$stmt->execute(['user_id' => $userId, 'ebook_id' => $ebookId]);

api_ebook_log_access(
    $pdo,
    $userId,
    null,
    $ebookId,
    false,
    sprintf('Acceso manual revocado por admin_id=%d', (int)($user['id'] ?? 0)),
    'grant_manual'
);

api_json(['ok' => true, 'message' => 'Acceso revocado']);
