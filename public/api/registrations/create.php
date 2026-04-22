<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();

$input = api_read_json();
$forumSlot = trim((string)($input['forumSlot'] ?? ''));
$fullName = trim((string)($input['fullName'] ?? ''));
$documentId = trim((string)($input['documentId'] ?? ''));
$needsCert = !empty($input['needsCert']);
$acceptanceChecked = !empty($input['acceptanceChecked']);
$signatureDataUrl = trim((string)($input['signatureDataUrl'] ?? ''));

if ($forumSlot === '' || $fullName === '' || $documentId === '' || !$acceptanceChecked || $signatureDataUrl === '') {
    api_json(['ok' => false, 'error' => 'Faltan datos obligatorios de inscripción.'], 422);
}

$paymentProof = $input['paymentProof'] ?? null;
$proofName = is_array($paymentProof) ? ($paymentProof['name'] ?? null) : null;
$proofMime = is_array($paymentProof) ? ($paymentProof['mime'] ?? null) : null;
$proofSize = is_array($paymentProof) ? ($paymentProof['size'] ?? null) : null;
$proofBase64 = is_array($paymentProof) ? ($paymentProof['base64'] ?? null) : null;

if ($needsCert && (!$proofName || !$proofBase64)) {
    api_json(['ok' => false, 'error' => 'Debe adjuntar comprobante si solicita certificación.'], 422);
}

$stmt = $pdo->prepare(
    'INSERT INTO registrations (
      forum_slot, full_name, document_id, needs_cert,
      payment_proof_name, payment_proof_mime, payment_proof_size, payment_proof_base64,
      acceptance_checked, signature_data_url, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)' 
);
$stmt->execute([
    $forumSlot,
    $fullName,
    $documentId,
    $needsCert ? 1 : 0,
    $proofName,
    $proofMime,
    is_numeric($proofSize) ? (int)$proofSize : null,
    $proofBase64,
    1,
    $signatureDataUrl,
    gmdate('c'),
]);

api_json(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
