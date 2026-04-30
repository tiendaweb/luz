<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_registration_state.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT registrations.id, registrations.full_name, registrations.document_id, registrations.forum_slot,
                registrations.needs_cert, registrations.created_at,
                registrations.payment_proof_name, registrations.payment_proof_mime,
                registrations.payment_proof_size, registrations.payment_proof_base64,
                COALESCE(registration_admin_state.status, "pending") AS status,
                registration_admin_state.note,
                registration_admin_state.updated_by_user_id,
                registration_admin_state.updated_by_role,
                registration_meta.referral_code,
                registration_meta.payment_method,
                registration_meta.payment_link,
                registration_meta.price_amount,
                registration_meta.currency_code,
                users.full_name AS referrer_name
         FROM registrations
         LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
         LEFT JOIN registration_meta ON registration_meta.registration_id = registrations.id
         LEFT JOIN users ON users.id = registration_meta.referrer_user_id
         ORDER BY registrations.id DESC'
    )->fetchAll();
    $rows = api_attach_registration_history($pdo, $rows);
    foreach ($rows as &$row) {
        $proofBase64 = trim((string)($row['payment_proof_base64'] ?? ''));
        $proofMime = trim((string)($row['payment_proof_mime'] ?? ''));
        $row['has_payment_proof'] = $proofBase64 !== '';
        $row['payment_proof_preview'] = ($proofBase64 !== '' && $proofMime !== '')
            ? ('data:' . $proofMime . ';base64,' . $proofBase64)
            : null;
    }
    unset($row);

    api_json(['ok' => true, 'items' => $rows]);
}

if ($method === 'PATCH') {
    $input = api_read_json();
    $registrationId = (int)($input['registrationId'] ?? 0);
    $status = trim((string)($input['status'] ?? ''));
    $note = trim((string)($input['note'] ?? ''));

    if ($registrationId < 1) {
        api_json(['ok' => false, 'error' => 'Registro inválido.'], 422);
    }
    if (!in_array($status, ['pending', 'payment_submitted', 'approved', 'rejected'], true)) {
        api_json(['ok' => false, 'error' => 'Estado inválido.'], 422);
    }

    $result = api_set_registration_status($pdo, $registrationId, $status, $note, [
        'role' => 'admin',
        'id' => (int)($user['id'] ?? 0),
    ]);

    if (!($result['ok'] ?? false)) {
        $error = (string)($result['error'] ?? 'No se pudo actualizar estado.');
        $statusCode = str_contains($error, 'No autorizado') || str_contains($error, 'Rol no autorizado') ? 403 : 422;
        api_json(['ok' => false, 'error' => $error], $statusCode);
    }

    api_json(['ok' => true]);
}

if ($method === 'DELETE') {
    $registrationId = (int)($_GET['id'] ?? 0);
    if ($registrationId < 1) {
        api_json(['ok' => false, 'error' => 'Registro inválido.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM registrations WHERE id = :id');
    $stmt->execute(['id' => $registrationId]);

    api_json(['ok' => true]);
}

api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
