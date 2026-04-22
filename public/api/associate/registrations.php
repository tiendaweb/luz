<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_registration_state.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'associate') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$associateId = (int)($user['id'] ?? 0);
if ($associateId < 1) {
    api_json(['ok' => false, 'error' => 'Asociado inválido'], 403);
}

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $statusFilter = trim((string)($_GET['status'] ?? 'all'));
    $allowedStatuses = ['pending', 'approved', 'rejected'];

    $sql = 'SELECT registrations.id, registrations.full_name, registrations.document_id, registrations.forum_slot,
                   registrations.needs_cert, registrations.created_at,
                   COALESCE(registration_admin_state.status, "pending") AS status,
                   registration_admin_state.note,
                   registration_admin_state.updated_by_user_id,
                   registration_admin_state.updated_by_role,
                   registration_meta.referral_code,
                   registration_meta.payment_method,
                   registration_meta.payment_link,
                   registration_meta.price_amount,
                   registration_meta.currency_code
            FROM registrations
            INNER JOIN registration_meta ON registration_meta.registration_id = registrations.id
            LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
            WHERE registration_meta.referrer_user_id = :associate_id';

    $params = ['associate_id' => $associateId];
    if (in_array($statusFilter, $allowedStatuses, true)) {
        $sql .= ' AND COALESCE(registration_admin_state.status, "pending") = :status_filter';
        $params['status_filter'] = $statusFilter;
    }

    $sql .= ' ORDER BY registrations.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = api_attach_registration_history($pdo, $stmt->fetchAll());

    api_json(['ok' => true, 'items' => $items]);
}

if ($method === 'PATCH') {
    $input = api_read_json();
    $registrationId = (int)($input['registrationId'] ?? 0);
    $status = trim((string)($input['status'] ?? ''));
    $note = trim((string)($input['note'] ?? ''));

    $result = api_set_registration_status($pdo, $registrationId, $status, $note, [
        'role' => 'associate',
        'id' => $associateId,
    ]);

    if (!($result['ok'] ?? false)) {
        $error = (string)($result['error'] ?? 'No se pudo actualizar estado.');
        $statusCode = str_contains($error, 'No autorizado') || str_contains($error, 'Rol no autorizado') ? 403 : 422;
        api_json(['ok' => false, 'error' => $error], $statusCode);
    }

    api_json(['ok' => true]);
}

api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
