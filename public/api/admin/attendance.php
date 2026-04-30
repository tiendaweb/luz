<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_attendance.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $forumId = isset($_GET['forum_id']) ? (int)$_GET['forum_id'] : null;
    $registrationId = isset($_GET['registration_id']) ? (int)$_GET['registration_id'] : null;
    $items = api_attendance_list($pdo, ['role' => 'admin', 'id' => (int)$user['id']], $forumId, $registrationId);
    api_json(['ok' => true, 'items' => $items]);
}

if ($method === 'POST' || $method === 'PATCH') {
    $input = api_read_json();
    $registrationId = (int)($input['registrationId'] ?? 0);
    $forumId = (int)($input['forumId'] ?? 0);
    $status = trim((string)($input['status'] ?? ''));
    $sessionKey = trim((string)($input['sessionKey'] ?? ''));
    $sessionDate = trim((string)($input['sessionDate'] ?? ''));
    $minutesAttended = isset($input['minutesAttended']) ? (int)$input['minutesAttended'] : null;
    $notes = trim((string)($input['notes'] ?? ''));

    if (!in_array($status, ['present', 'absent', 'partial'], true)) {
        api_json(['ok' => false, 'error' => 'Estado de asistencia inválido.'], 422);
    }
    if ($registrationId < 1 || $forumId < 1) {
        api_json(['ok' => false, 'error' => 'Registro o foro inválido.'], 422);
    }
    if ($sessionKey === '' && $sessionDate === '') {
        api_json(['ok' => false, 'error' => 'Debe indicar sessionKey o sessionDate.'], 422);
    }

    if (!api_attendance_can_manage($pdo, $registrationId, $forumId, ['role' => 'admin', 'id' => (int)$user['id']])) {
        api_json(['ok' => false, 'error' => 'No autorizado para registrar asistencia.'], 403);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO forum_attendance (
            registration_id, forum_id, session_key, session_date, status, minutes_attended, recorded_by_user_id, recorded_at, notes
         ) VALUES (
            :registration_id, :forum_id, :session_key, :session_date, :status, :minutes_attended, :recorded_by_user_id, :recorded_at, :notes
         )
         ON CONFLICT DO UPDATE SET
            status = excluded.status,
            minutes_attended = excluded.minutes_attended,
            recorded_by_user_id = excluded.recorded_by_user_id,
            recorded_at = excluded.recorded_at,
            notes = excluded.notes'
    );
    $stmt->execute([
        'registration_id' => $registrationId,
        'forum_id' => $forumId,
        'session_key' => $sessionKey === '' ? null : $sessionKey,
        'session_date' => $sessionDate === '' ? null : $sessionDate,
        'status' => $status,
        'minutes_attended' => $minutesAttended,
        'recorded_by_user_id' => (int)$user['id'],
        'recorded_at' => gmdate('c'),
        'notes' => $notes === '' ? null : $notes,
    ]);

    api_json(['ok' => true]);
}

api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
