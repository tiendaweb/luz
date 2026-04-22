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

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
