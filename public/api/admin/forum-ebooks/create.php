<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_forum_ebooks_require_admin();
api_require_method(['POST']);

$input = api_read_json();
$forumId = (int)($input['forum_id'] ?? 0);
$ebookId = (int)($input['ebook_id'] ?? 0);
$isActive = array_key_exists('is_active', $input) ? ((int)(bool)$input['is_active']) : 1;

if ($forumId < 1 || $ebookId < 1) {
    api_error('forum_id y ebook_id son obligatorios.', 422, 'validation_error');
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'INSERT INTO forum_ebooks (forum_id, ebook_id, is_active, updated_at)
     VALUES (:forum_id, :ebook_id, :is_active, CURRENT_TIMESTAMP)
     ON CONFLICT(forum_id, ebook_id) DO UPDATE SET
        is_active = excluded.is_active,
        updated_at = CURRENT_TIMESTAMP'
);
$stmt->execute([
    'forum_id' => $forumId,
    'ebook_id' => $ebookId,
    'is_active' => $isActive,
]);

$idStmt = $pdo->prepare('SELECT id FROM forum_ebooks WHERE forum_id = :forum_id AND ebook_id = :ebook_id LIMIT 1');
$idStmt->execute(['forum_id' => $forumId, 'ebook_id' => $ebookId]);
$id = (int)($idStmt->fetchColumn() ?: 0);

api_json(['ok' => true, 'id' => $id], 201);
