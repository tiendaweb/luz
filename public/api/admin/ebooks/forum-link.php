<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

api_require_role('admin');
api_require_method(['POST', 'DELETE']);

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'];

$input = $method === 'POST' ? api_read_json() : $_GET;
$ebookId = (int)($input['ebookId'] ?? 0);
$forumId = (int)($input['forumId'] ?? 0);

if ($ebookId < 1 || $forumId < 1) {
    api_error('ebookId y forumId son obligatorios', 422, 'validation_error');
}

if ($method === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO forum_ebooks (forum_id, ebook_id, is_active, created_at)
         VALUES (:forum_id, :ebook_id, 1, :created_at)
         ON CONFLICT(forum_id, ebook_id) DO UPDATE SET is_active = 1'
    );
    $stmt->execute([
        'forum_id' => $forumId,
        'ebook_id' => $ebookId,
        'created_at' => gmdate('c'),
    ]);
    api_json(['ok' => true, 'message' => 'Ebook vinculado al foro']);
}

// DELETE
$stmt = $pdo->prepare('DELETE FROM forum_ebooks WHERE forum_id = :forum_id AND ebook_id = :ebook_id');
$stmt->execute(['forum_id' => $forumId, 'ebook_id' => $ebookId]);

api_json(['ok' => true, 'message' => 'Vínculo eliminado']);
