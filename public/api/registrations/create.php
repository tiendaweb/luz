<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/RegistrationService.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

/**
 * @return array{id:int,slot:string}|null
 */
function api_resolve_forum(PDO $pdo, ?int $forumId, string $forumSlot): ?array
{
    if ($forumId !== null && $forumId > 0) {
        $stmt = $pdo->prepare('SELECT id, title FROM forums WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $forumId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            return ['id' => (int)$row['id'], 'slot' => trim((string)$row['title'])];
        }
    }

    $slot = trim($forumSlot);
    if ($slot === '') {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, title, code FROM forums ORDER BY starts_at ASC, id ASC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $title = mb_strtolower(trim((string)($row['title'] ?? '')));
        $code = mb_strtolower(trim((string)($row['code'] ?? '')));
        $candidate = mb_strtolower($slot);
        if ($candidate === $title || $candidate === $code || str_contains($candidate, $title) || str_contains($candidate, $code)) {
            return ['id' => (int)$row['id'], 'slot' => trim((string)$row['title'])];
        }
    }

    return null;
}

$pdo = api_require_db();
$input = api_read_json();
$currentUser = api_current_user();
$currentUserId = is_array($currentUser) ? (int)($currentUser['id'] ?? 0) : 0;

try {
    $service = new RegistrationService();
    $payload = $service->validateAndNormalize($input);
} catch (InvalidArgumentException $error) {
    api_json(['ok' => false, 'error' => $error->getMessage()], 422);
}

$resolvedForum = api_resolve_forum($pdo, isset($payload['forumId']) ? (int)$payload['forumId'] : null, (string)$payload['forumSlot']);
if (!is_array($resolvedForum)) {
    api_json(['ok' => false, 'error' => 'No se pudo resolver el foro seleccionado.'], 422);
}

if ($currentUserId > 0) {
    $duplicateStmt = $pdo->prepare(
        'SELECT id
         FROM registrations
         WHERE user_id = :user_id
           AND (
             forum_id = :forum_id
             OR (
               forum_id IS NULL
               AND forum_slot = :forum_slot
             )
           )
         LIMIT 1'
    );
    $duplicateStmt->execute([
        'user_id' => $currentUserId,
        'forum_id' => (int)$resolvedForum['id'],
        'forum_slot' => (string)$resolvedForum['slot'],
    ]);
    if ($duplicateStmt->fetchColumn() !== false) {
        api_json([
            'ok' => false,
            'error' => 'Ya existe una inscripción para este usuario en el foro seleccionado.',
        ], 409);
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO registrations (
      user_id, forum_id, forum_slot, full_name, document_id, needs_cert,
      payment_proof_name, payment_proof_mime, payment_proof_size, payment_proof_base64,
      acceptance_checked, signature_data_url, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $currentUserId > 0 ? $currentUserId : null,
    (int)$resolvedForum['id'],
    (string)$resolvedForum['slot'],
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
