<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET']);

$user = api_current_user();
if (!is_array($user)) {
    api_error('Debes iniciar sesión', 401, 'unauthorized');
}

$pdo = api_require_db();
$userId = (int)($user['id'] ?? 0);
if ($userId < 1) {
    api_error('Usuario inválido', 401, 'unauthorized');
}

$stmt = $pdo->prepare(
    'SELECT user_certificates.id,
            user_certificates.type,
            user_certificates.created_at,
            forums.id AS forum_id,
            forums.code AS forum_code,
            forums.title AS forum_title
     FROM user_certificates
     INNER JOIN forums ON forums.id = user_certificates.forum_id
     WHERE user_certificates.user_id = :user_id
     ORDER BY user_certificates.created_at DESC, user_certificates.id DESC'
);
$stmt->execute(['user_id' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$items = [];
foreach ($rows as $row) {
    $type = (string)($row['type'] ?? 'completion');
    $items[] = [
        'id' => (int)$row['id'],
        'type' => $type,
        'type_label' => $type === 'attendance' ? 'Asistencia' : 'Conclusión',
        'forum_id' => (int)$row['forum_id'],
        'forum_code' => (string)$row['forum_code'],
        'forum_title' => (string)$row['forum_title'],
        'created_at' => (string)$row['created_at'],
        'view_url' => sprintf('/api/admin/certificate-view.php?id=%d&type=%s', (int)$row['id'], $type),
    ];
}

api_json(['ok' => true, 'items' => $items]);
