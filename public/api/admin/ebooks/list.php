<?php

declare(strict_types=1);

require_once __DIR__ . '/_common.php';

admin_ebooks_require_admin();
api_require_method(['GET']);

$pdo = api_require_db();
$rows = $pdo->query(
    "SELECT ebooks.id,
            ebooks.title,
            ebooks.description,
            ebooks.status,
            ebooks.provider,
            ebooks.local_path,
            ebooks.external_url,
            ebooks.min_attendance,
            ebooks.requires_approved,
            ebooks.created_at,
            ebooks.updated_at,
            GROUP_CONCAT(forums.code, ',') AS forum_codes,
            GROUP_CONCAT(forums.id, ',') AS forum_ids
     FROM ebooks
     LEFT JOIN forum_ebooks ON forum_ebooks.ebook_id = ebooks.id AND forum_ebooks.is_active = 1
     LEFT JOIN forums ON forums.id = forum_ebooks.forum_id
     GROUP BY ebooks.id
     ORDER BY ebooks.created_at DESC, ebooks.id DESC"
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = array_map(static function (array $row): array {
    $forumCodesRaw = trim((string)($row['forum_codes'] ?? ''));
    $forumIdsRaw = trim((string)($row['forum_ids'] ?? ''));

    return [
        'id' => (int)$row['id'],
        'title' => (string)$row['title'],
        'description' => (string)($row['description'] ?? ''),
        'status' => (string)$row['status'],
        'provider' => (string)$row['provider'],
        'local_path' => (string)($row['local_path'] ?? ''),
        'external_url' => (string)($row['external_url'] ?? ''),
        'min_attendance' => (float)$row['min_attendance'],
        'requires_approved' => (int)$row['requires_approved'] === 1,
        'forum_codes' => $forumCodesRaw === '' ? [] : explode(',', $forumCodesRaw),
        'forum_ids' => $forumIdsRaw === '' ? [] : array_map('intval', explode(',', $forumIdsRaw)),
        'created_at' => (string)$row['created_at'],
        'updated_at' => (string)($row['updated_at'] ?? ''),
    ];
}, $rows);

api_json(['ok' => true, 'items' => $items]);
