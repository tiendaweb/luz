<?php

declare(strict_types=1);

/**
 * @param array{role:string,id?:int} $actor
 */
function api_attendance_can_manage(PDO $pdo, int $registrationId, int $forumId, array $actor): bool
{
    $role = (string)($actor['role'] ?? '');
    $actorId = (int)($actor['id'] ?? 0);

    if ($registrationId < 1 || $forumId < 1) {
        return false;
    }

    $registrationStmt = $pdo->prepare('SELECT id, forum_id FROM registrations WHERE id = :id LIMIT 1');
    $registrationStmt->execute(['id' => $registrationId]);
    $registration = $registrationStmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($registration) || (int)$registration['forum_id'] !== $forumId) {
        return false;
    }

    if ($role === 'admin') {
        return true;
    }

    if ($role !== 'associate' || $actorId < 1) {
        return false;
    }

    $ownerStmt = $pdo->prepare(
        'SELECT 1
         FROM registration_meta
         WHERE registration_id = :registration_id
           AND referrer_user_id = :associate_id
         LIMIT 1'
    );
    $ownerStmt->execute([
        'registration_id' => $registrationId,
        'associate_id' => $actorId,
    ]);

    return $ownerStmt->fetchColumn() !== false;
}

/**
 * @param array{role:string,id?:int} $actor
 * @return array<int,array<string,mixed>>
 */
function api_attendance_list(PDO $pdo, array $actor, ?int $forumId, ?int $registrationId): array
{
    $role = (string)($actor['role'] ?? '');
    $actorId = (int)($actor['id'] ?? 0);

    $sql = 'SELECT forum_attendance.id,
                   forum_attendance.registration_id,
                   forum_attendance.forum_id,
                   forum_attendance.session_key,
                   forum_attendance.session_date,
                   forum_attendance.status,
                   forum_attendance.minutes_attended,
                   forum_attendance.recorded_by_user_id,
                   forum_attendance.recorded_at,
                   forum_attendance.notes
            FROM forum_attendance
            INNER JOIN registrations ON registrations.id = forum_attendance.registration_id
            LEFT JOIN registration_meta ON registration_meta.registration_id = registrations.id
            WHERE 1 = 1';

    $params = [];
    if ($forumId !== null && $forumId > 0) {
        $sql .= ' AND forum_attendance.forum_id = :forum_id';
        $params['forum_id'] = $forumId;
    }
    if ($registrationId !== null && $registrationId > 0) {
        $sql .= ' AND forum_attendance.registration_id = :registration_id';
        $params['registration_id'] = $registrationId;
    }

    if ($role === 'associate') {
        $sql .= ' AND registration_meta.referrer_user_id = :associate_id';
        $params['associate_id'] = $actorId;
    }

    $sql .= ' ORDER BY COALESCE(forum_attendance.session_date, forum_attendance.recorded_at) DESC, forum_attendance.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
