<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

api_require_method(['POST']);

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_error('Acceso denegado', 403, 'forbidden');
}

$pdo = api_require_db();
$input = api_read_json();
$forumId = (int)($input['forumId'] ?? 0);

if ($forumId < 1) {
    api_error('Foro inválido', 422, 'validation_error');
}

$forumStmt = $pdo->prepare('SELECT id, code, title FROM forums WHERE id = :id LIMIT 1');
$forumStmt->execute(['id' => $forumId]);
$forum = $forumStmt->fetch();
if (!$forum) {
    api_error('Foro no encontrado', 404, 'not_found');
}

$eligibleStmt = $pdo->prepare('
    SELECT DISTINCT registrations.user_id
    FROM registrations
    INNER JOIN forum_attendance
      ON forum_attendance.registration_id = registrations.id
     AND forum_attendance.forum_id = registrations.forum_id
    WHERE registrations.forum_id = :forum_id
      AND forum_attendance.status IN ("present", "partial")
');
$eligibleStmt->execute(['forum_id' => $forumId]);
$userIds = array_map('intval', $eligibleStmt->fetchAll(PDO::FETCH_COLUMN) ?: []);

if (empty($userIds)) {
    api_json([
        'ok' => true,
        'created' => 0,
        'skipped' => 0,
        'forum' => ['id' => (int)$forum['id'], 'code' => $forum['code'], 'title' => $forum['title']],
        'message' => 'No hay asistentes registrados en este foro.',
    ]);
}

$created = 0;
$skipped = 0;
$adminId = (int)($user['id'] ?? 0);
$now = gmdate('c');

$existsStmt = $pdo->prepare('
    SELECT 1 FROM user_certificates
    WHERE user_id = :user_id AND forum_id = :forum_id AND type = "attendance"
    LIMIT 1
');
$insertStmt = $pdo->prepare('
    INSERT INTO user_certificates (user_id, forum_id, type, created_at, created_by_user_id)
    VALUES (:user_id, :forum_id, "attendance", :created_at, :created_by_user_id)
');

try {
    $pdo->beginTransaction();
    foreach ($userIds as $uid) {
        $existsStmt->execute(['user_id' => $uid, 'forum_id' => $forumId]);
        if ($existsStmt->fetch()) {
            $skipped++;
            continue;
        }
        $insertStmt->execute([
            'user_id' => $uid,
            'forum_id' => $forumId,
            'created_at' => $now,
            'created_by_user_id' => $adminId,
        ]);
        $created++;
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log(sprintf('[api/admin/certificates/bulk-attendance] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    api_error('Error interno del servidor', 500, 'database_error');
}

api_json([
    'ok' => true,
    'created' => $created,
    'skipped' => $skipped,
    'forum' => ['id' => (int)$forum['id'], 'code' => $forum['code'], 'title' => $forum['title']],
]);
