<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_forum_ebooks_require_admin();
api_require_method(['GET']);

$pdo = api_require_db();
$rows = $pdo->query(
    "SELECT forum_ebooks.id,
            forum_ebooks.forum_id,
            forum_ebooks.ebook_id,
            forum_ebooks.is_active,
            forum_ebooks.created_at,
            forum_ebooks.updated_at,
            forums.code AS forum_code,
            forums.title AS forum_title,
            ebooks.title AS ebook_title
     FROM forum_ebooks
     INNER JOIN forums ON forums.id = forum_ebooks.forum_id
     INNER JOIN ebooks ON ebooks.id = forum_ebooks.ebook_id
     ORDER BY forum_ebooks.id DESC"
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = array_map(static fn(array $row): array => [
    'id' => (int)$row['id'],
    'forum_id' => (int)$row['forum_id'],
    'ebook_id' => (int)$row['ebook_id'],
    'is_active' => (int)$row['is_active'] === 1,
    'forum_code' => (string)$row['forum_code'],
    'forum_title' => (string)$row['forum_title'],
    'ebook_title' => (string)$row['ebook_title'],
    'created_at' => (string)$row['created_at'],
    'updated_at' => (string)($row['updated_at'] ?? ''),
], $rows);

api_json(['ok' => true, 'items' => $items]);
