<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$rows = $pdo->query(
    'SELECT registrations.id AS registration_id,
            registrations.full_name AS referred_name,
            registrations.document_id AS referred_document,
            registrations.created_at AS referred_at,
            inviters.id AS inviter_user_id,
            inviters.full_name AS inviter_name,
            inviters.email AS inviter_email,
            inviter_roles.slug AS inviter_role,
            registration_meta.referral_code,
            COALESCE(registration_admin_state.status, "pending") AS status
     FROM registration_meta
     INNER JOIN registrations ON registrations.id = registration_meta.registration_id
     INNER JOIN users AS inviters ON inviters.id = registration_meta.referrer_user_id
     INNER JOIN roles AS inviter_roles ON inviter_roles.id = inviters.role_id
     LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
     ORDER BY registrations.created_at DESC, registrations.id DESC'
)->fetchAll();

api_json(['ok' => true, 'items' => $rows]);
