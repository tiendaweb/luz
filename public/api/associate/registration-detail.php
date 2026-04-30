<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_registration_state.php';
require_once __DIR__ . '/../../../app/Services/SignatureGenerator.php';

api_require_method(['GET']);

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'associate') {
    api_error('Acceso denegado', 403, 'forbidden');
}

$associateId = (int)($user['id'] ?? 0);
if ($associateId < 1) {
    api_error('Asociado inválido', 403, 'forbidden');
}

$pdo = api_require_db();
$registrationId = (int)($_GET['id'] ?? 0);
if ($registrationId < 1) {
    api_error('Registro inválido', 422, 'validation_error');
}

$stmt = $pdo->prepare(
    'SELECT registrations.id, registrations.full_name, registrations.document_id, registrations.forum_slot,
            registrations.needs_cert, registrations.created_at,
            registrations.payment_proof_name, registrations.payment_proof_mime,
            registrations.payment_proof_size, registrations.payment_proof_base64,
            registrations.signature_data_url, registrations.signature_data,
            registrations.user_id, registrations.forum_id,
            forums.code AS forum_code, forums.title AS forum_title,
            COALESCE(registration_admin_state.status, "pending") AS status,
            registration_admin_state.note,
            registration_admin_state.updated_by_user_id,
            registration_admin_state.updated_by_role,
            registration_admin_state.updated_at,
            registration_meta.referral_code,
            registration_meta.referrer_user_id,
            registration_meta.payment_method,
            registration_meta.payment_link,
            registration_meta.price_amount,
            registration_meta.currency_code
     FROM registrations
     INNER JOIN registration_meta ON registration_meta.registration_id = registrations.id
     LEFT JOIN forums ON forums.id = registrations.forum_id
     LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
     WHERE registrations.id = :id
       AND registration_meta.referrer_user_id = :associate_id
     LIMIT 1'
);
$stmt->execute(['id' => $registrationId, 'associate_id' => $associateId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    api_error('Registro no encontrado o sin permiso', 404, 'not_found');
}

$proofBase64 = trim((string)($row['payment_proof_base64'] ?? ''));
$proofMime = trim((string)($row['payment_proof_mime'] ?? ''));
$row['has_payment_proof'] = $proofBase64 !== '';
$row['payment_proof_preview'] = ($proofBase64 !== '' && $proofMime !== '')
    ? ('data:' . $proofMime . ';base64,' . $proofBase64)
    : null;

$row['signature_preview'] = SignatureGenerator::getSignatureDataUrl([
    'full_name' => $row['full_name'] ?? '',
    'signature_data_url' => $row['signature_data_url'] ?? '',
    'signature_data' => $row['signature_data'] ?? '',
]);
$row['has_real_signature'] = !empty($row['signature_data_url']) || !empty($row['signature_data']);

unset($row['payment_proof_base64'], $row['signature_data_url'], $row['signature_data']);

$rows = api_attach_registration_history($pdo, [$row]);

api_json(['ok' => true, 'item' => $rows[0]]);
