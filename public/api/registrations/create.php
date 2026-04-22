<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/RegistrationService.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$input = api_read_json();

try {
    $service = new RegistrationService();
    $payload = $service->validateAndNormalize($input);
} catch (InvalidArgumentException $error) {
    api_json(['ok' => false, 'error' => $error->getMessage()], 422);
}

$stmt = $pdo->prepare(
    'INSERT INTO registrations (
      forum_slot, full_name, document_id, needs_cert,
      payment_proof_name, payment_proof_mime, payment_proof_size, payment_proof_base64,
      acceptance_checked, signature_data_url, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $payload['forumSlot'],
    $payload['fullName'],
    $payload['documentId'],
    $payload['needsCert'] ? 1 : 0,
    $payload['paymentProofName'],
    $payload['paymentProofMime'],
    $payload['paymentProofSize'],
    $payload['paymentProofBase64'],
    1,
    $payload['signatureDataUrl'],
    gmdate('c'),
]);

$registrationId = (int)$pdo->lastInsertId();
$referrerUserId = null;
$offer = null;

if (is_string($payload['referralCode'] ?? null) && $payload['referralCode'] !== '') {
    $offerStmt = $pdo->prepare(
        'SELECT user_id, referral_code, payment_method, payment_link, price_amount, currency_code
         FROM associate_offers
         WHERE referral_code = :code
         LIMIT 1'
    );
    $offerStmt->execute(['code' => $payload['referralCode']]);
    $offer = $offerStmt->fetch();
    if (is_array($offer)) {
        $referrerUserId = (int)$offer['user_id'];
    }
}

$metaStmt = $pdo->prepare(
    'INSERT INTO registration_meta (
      registration_id, referral_code, referrer_user_id, payment_method, payment_link, price_amount, currency_code
    ) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$metaStmt->execute([
    $registrationId,
    $payload['referralCode'],
    $referrerUserId,
    is_array($offer) ? (string)$offer['payment_method'] : null,
    is_array($offer) ? (string)$offer['payment_link'] : null,
    is_array($offer) ? (float)$offer['price_amount'] : null,
    is_array($offer) ? (string)$offer['currency_code'] : null,
]);

$adminStateStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO registration_admin_state (registration_id, status, note, updated_at) VALUES (?, ?, ?, ?)'
);
$adminStateStmt->execute([$registrationId, 'pending', null, gmdate('c')]);

$historyStmt = $pdo->prepare(
    'INSERT INTO registration_status_history (
      registration_id, from_status, to_status, note, reviewed_by_user_id, reviewed_by_role, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$historyStmt->execute([$registrationId, null, 'pending', 'Estado inicial al registrar inscripción.', null, 'system', gmdate('c')]);

api_json([
    'ok' => true,
    'id' => $registrationId,
    'appliedOffer' => is_array($offer) ? [
        'referralCode' => (string)$offer['referral_code'],
        'paymentMethod' => (string)$offer['payment_method'],
        'paymentLink' => (string)$offer['payment_link'],
        'priceAmount' => (float)$offer['price_amount'],
        'currencyCode' => (string)$offer['currency_code'],
    ] : null,
]);
