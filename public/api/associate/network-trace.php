<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'associate') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$associateId = (int)($user['id'] ?? 0);
if ($associateId < 1) {
    api_json(['ok' => false, 'error' => 'Asociado inválido'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'SELECT registrations.id AS registration_id,
            registrations.full_name AS referred_name,
            registrations.document_id AS referred_document,
            registrations.created_at AS referred_at,
            users.id AS inviter_user_id,
            users.full_name AS inviter_name,
            users.email AS inviter_email,
            registration_meta.referral_code,
            COALESCE(registration_admin_state.status, "pending") AS status
     FROM registration_meta
     INNER JOIN registrations ON registrations.id = registration_meta.registration_id
     INNER JOIN users ON users.id = registration_meta.referrer_user_id
     LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
     WHERE registration_meta.referrer_user_id = :associate_id
     ORDER BY registrations.created_at DESC, registrations.id DESC'
);
$stmt->execute(['associate_id' => $associateId]);

api_json(['ok' => true, 'items' => $stmt->fetchAll()]);
