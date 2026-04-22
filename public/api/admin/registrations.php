<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

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
                COALESCE(registration_admin_state.status, "pending") AS status,
                registration_admin_state.note,
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
    if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
        api_json(['ok' => false, 'error' => 'Estado inválido.'], 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO registration_admin_state (registration_id, status, note, updated_at)
         VALUES (:registration_id, :status, :note, :updated_at)
         ON CONFLICT(registration_id) DO UPDATE SET
          status = excluded.status,
          note = excluded.note,
          updated_at = excluded.updated_at'
    );
    $stmt->execute([
        'registration_id' => $registrationId,
        'status' => $status,
        'note' => $note === '' ? null : $note,
        'updated_at' => gmdate('c'),
    ]);

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
