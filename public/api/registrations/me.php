<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$user = api_current_user();
$userId = is_array($user) ? (int)($user['id'] ?? 0) : 0;
if ($userId < 1) {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();

$registrationStmt = $pdo->prepare(
    'SELECT registrations.id,
            registrations.forum_id,
            registrations.forum_slot,
            COALESCE(registration_admin_state.status, "pending") AS admin_status
     FROM registrations
     LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
     WHERE registrations.user_id = :user_id
     ORDER BY registrations.id DESC'
);
$registrationStmt->execute(['user_id' => $userId]);
$registrations = $registrationStmt->fetchAll(PDO::FETCH_ASSOC);

$attendanceStmt = $pdo->prepare(
    'SELECT registration_id,
            COUNT(*) AS sessions_total,
            SUM(CASE WHEN status IN ("present", "partial") THEN 1 ELSE 0 END) AS sessions_with_attendance
     FROM forum_attendance
     WHERE registration_id = :registration_id
     GROUP BY registration_id'
);

$items = [];
foreach ($registrations as $row) {
    $registrationId = (int)($row['id'] ?? 0);
    $attendanceStmt->execute(['registration_id' => $registrationId]);
    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

    $sessionsTotal = (int)($attendance['sessions_total'] ?? 0);
    $sessionsWithAttendance = (int)($attendance['sessions_with_attendance'] ?? 0);
    $attendancePercent = $sessionsTotal > 0 ? (int)floor(($sessionsWithAttendance / $sessionsTotal) * 100) : 0;
    $approved = (string)($row['admin_status'] ?? 'pending') === 'approved';

    $items[] = [
        'registration_id' => $registrationId,
        'forum_id' => (int)($row['forum_id'] ?? 0),
        'forum_slot' => (string)($row['forum_slot'] ?? ''),
        'admin_status' => (string)($row['admin_status'] ?? 'pending'),
        'attendance_percent' => $attendancePercent,
        'sessions_total' => $sessionsTotal,
        'sessions_with_attendance' => $sessionsWithAttendance,
        'benefits' => [
            'ebooks_enabled' => $approved,
            'certificate_enabled' => $approved && $attendancePercent >= 75,
        ],
    ];
}

api_json(['ok' => true, 'items' => $items]);
