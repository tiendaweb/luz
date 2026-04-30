<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_registration_state.php';
require_once __DIR__ . '/../../../app/Services/SignatureGenerator.php';

api_require_method(['GET', 'PATCH']);

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_error('Acceso denegado', 403, 'forbidden');
}

$pdo = api_require_db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'PATCH') {
    $input = api_read_json();
    $registrationId = (int)($input['registrationId'] ?? 0);
    $asset = trim((string)($input['asset'] ?? ''));
    $nextStatus = trim((string)($input['status'] ?? ''));
    $reason = trim((string)($input['reason'] ?? ''));

    if ($registrationId < 1) {
        api_error('Registro inválido', 422, 'validation_error');
    }
    if (!in_array($asset, ['payment_proof', 'signature'], true)) {
        api_error('Activo inválido', 422, 'validation_error');
    }
    if (!in_array($nextStatus, ['approved', 'rejected'], true) || $reason === '') {
        api_error('Estado o motivo inválido. El motivo es obligatorio.', 422, 'validation_error');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS registration_asset_reviews (
      registration_id INTEGER PRIMARY KEY,
      payment_proof_status TEXT NOT NULL DEFAULT "pending",
      signature_status TEXT NOT NULL DEFAULT "pending",
      payment_proof_note TEXT,
      signature_note TEXT,
      updated_by_user_id INTEGER,
      updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS registration_review_audit (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      registration_id INTEGER NOT NULL,
      asset TEXT NOT NULL,
      previous_status TEXT,
      next_status TEXT NOT NULL,
      reason TEXT NOT NULL,
      actor_user_id INTEGER,
      actor_role TEXT,
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $current = $pdo->prepare('SELECT * FROM registration_asset_reviews WHERE registration_id = :id LIMIT 1');
    $current->execute(['id' => $registrationId]);
    $state = $current->fetch(PDO::FETCH_ASSOC) ?: [];
    $column = $asset === 'payment_proof' ? 'payment_proof_status' : 'signature_status';
    $noteColumn = $asset === 'payment_proof' ? 'payment_proof_note' : 'signature_note';
    $prev = (string)($state[$column] ?? 'pending');

    $upsert = $pdo->prepare("INSERT INTO registration_asset_reviews (registration_id, {$column}, {$noteColumn}, updated_by_user_id, updated_at)
        VALUES (:id, :status, :note, :user_id, CURRENT_TIMESTAMP)
        ON CONFLICT(registration_id) DO UPDATE SET {$column} = excluded.{$column}, {$noteColumn} = excluded.{$noteColumn}, updated_by_user_id = excluded.updated_by_user_id, updated_at = CURRENT_TIMESTAMP");
    $upsert->execute(['id' => $registrationId, 'status' => $nextStatus, 'note' => $reason, 'user_id' => (int)($user['id'] ?? 0)]);

    $audit = $pdo->prepare('INSERT INTO registration_review_audit (registration_id, asset, previous_status, next_status, reason, actor_user_id, actor_role, created_at)
      VALUES (:registration_id, :asset, :previous_status, :next_status, :reason, :actor_user_id, :actor_role, CURRENT_TIMESTAMP)');
    $audit->execute([
      'registration_id' => $registrationId,
      'asset' => $asset,
      'previous_status' => $prev,
      'next_status' => $nextStatus,
      'reason' => $reason,
      'actor_user_id' => (int)($user['id'] ?? 0),
      'actor_role' => 'admin',
    ]);
    api_json(['ok' => true]);
}

$registrationId = (int)($_GET['id'] ?? 0);
if ($registrationId < 1) api_error('Registro inválido', 422, 'validation_error');

$stmt = $pdo->prepare(
    'SELECT registrations.id, registrations.full_name, registrations.document_id, registrations.forum_slot,
            registrations.needs_cert, registrations.created_at,
            registrations.payment_proof_name, registrations.payment_proof_mime,
            registrations.payment_proof_size, registrations.payment_proof_base64,
            registrations.signature_data_url, registrations.signature_data,
            registrations.user_id, registrations.forum_id,
            forums.code AS forum_code, forums.title AS forum_title,
            users.email AS user_email,
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
            registration_meta.currency_code,
            COALESCE(rar.payment_proof_status, "pending") AS payment_proof_status,
            COALESCE(rar.signature_status, "pending") AS signature_status,
            rar.payment_proof_note,
            rar.signature_note,
            referrer.full_name AS referrer_name,
            referrer.email AS referrer_email
     FROM registrations
     LEFT JOIN forums ON forums.id = registrations.forum_id
     LEFT JOIN users ON users.id = registrations.user_id
     LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
     LEFT JOIN registration_meta ON registration_meta.registration_id = registrations.id
     LEFT JOIN registration_asset_reviews rar ON rar.registration_id = registrations.id
     LEFT JOIN users referrer ON referrer.id = registration_meta.referrer_user_id
     WHERE registrations.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $registrationId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    api_error('Registro no encontrado', 404, 'not_found');
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
$auditStmt = $pdo->prepare('SELECT asset, previous_status, next_status, reason, actor_user_id, actor_role, created_at
    FROM registration_review_audit WHERE registration_id = :id ORDER BY id DESC LIMIT 50');
$auditStmt->execute(['id' => $registrationId]);
$rows[0]['review_history'] = $auditStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

api_json(['ok' => true, 'item' => $rows[0]]);
