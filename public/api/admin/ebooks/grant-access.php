<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

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
    $stmt = $pdo->prepare(
        'INSERT INTO user_ebook_access (user_id, ebook_id, access_granted, reason, granted_by_user_id, created_at)
         VALUES (:user_id, :ebook_id, 1, :reason, :granted_by, :created_at)
         ON CONFLICT(user_id, ebook_id) DO UPDATE SET
           access_granted = 1,
           reason = excluded.reason,
           granted_by_user_id = excluded.granted_by_user_id'
    );
    $stmt->execute([
        'user_id' => $userId,
        'ebook_id' => $ebookId,
        'reason' => $reason,
        'granted_by' => (int)($user['id'] ?? 0),
        'created_at' => gmdate('c'),
    ]);
    api_json(['ok' => true, 'message' => 'Acceso otorgado']);
}

// DELETE
$stmt = $pdo->prepare('DELETE FROM user_ebook_access WHERE user_id = :user_id AND ebook_id = :ebook_id');
$stmt->execute(['user_id' => $userId, 'ebook_id' => $ebookId]);

api_json(['ok' => true, 'message' => 'Acceso revocado']);
