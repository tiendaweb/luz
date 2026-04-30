<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['POST']);

$pdo = api_require_db();
$input = api_read_json();

// Validate required fields
$email = trim((string)($input['email'] ?? ''));
$password = (string)($input['password'] ?? '');
$fullName = trim((string)($input['fullName'] ?? ''));
$documentId = trim((string)($input['documentId'] ?? ''));
$forumId = isset($input['forumId']) ? (int)$input['forumId'] : 0;
$forumSlot = trim((string)($input['forumSlot'] ?? ''));
$referralCode = trim((string)($input['referralCode'] ?? ($input['ref_code'] ?? '')));
$countryCode = strtoupper(trim((string)($input['countryCode'] ?? ($input['country_code'] ?? ''))));
$signatureDataUrl = trim((string)($input['signatureDataUrl'] ?? ''));
$needsCert = (bool)($input['needsCert'] ?? false);

// Check if user is already authenticated (modal flow for logged-in users)
$sessionUser = api_current_user();
$isAuthenticated = is_array($sessionUser) && !empty($sessionUser['id']);

// Only require email/password for unauthenticated registrations (standalone page)
if (!$isAuthenticated) {
    if ($email === '') {
        api_error('Email es obligatorio', 422, 'validation_error');
    }
    if ($password === '') {
        api_error('Contraseña es obligatoria', 422, 'validation_error');
    }
    if (strlen($password) < 6) {
        api_error('La contraseña debe tener al menos 6 caracteres', 422, 'validation_error');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error('Email inválido', 422, 'validation_error');
    }
}

if ($fullName === '') {
    api_error('Nombre es obligatorio', 422, 'validation_error');
}
if ($documentId === '') {
    api_error('Documento es obligatorio', 422, 'validation_error');
}
if ($forumId <= 0 && $forumSlot === '') {
    api_error('Foro es obligatorio', 422, 'validation_error');
}
if ($signatureDataUrl === '') {
    api_error('Firma digital es obligatoria', 422, 'validation_error');
}
if ($needsCert && empty($input['paymentProof'])) {
    api_error('Comprobante de pago es obligatorio cuando solicitas certificación', 422, 'validation_error');
}

// Resolve forum
$resolvedForum = null;
if ($forumId > 0) {
    $stmt = $pdo->prepare('SELECT id, title FROM forums WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $forumId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (is_array($row)) {
        $resolvedForum = ['id' => (int)$row['id'], 'slot' => trim((string)$row['title'])];
    }
}
if (!is_array($resolvedForum) && $forumSlot !== '') {
    $stmt = $pdo->prepare('SELECT id, title FROM forums ORDER BY starts_at ASC, id ASC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $title = mb_strtolower(trim((string)($row['title'] ?? '')));
        $candidate = mb_strtolower($forumSlot);
        if ($candidate === $title || str_contains($candidate, $title)) {
            $resolvedForum = ['id' => (int)$row['id'], 'slot' => trim((string)$row['title'])];
            break;
        }
    }
}
if (!is_array($resolvedForum)) {
    api_error('No se pudo resolver el foro seleccionado.', 422, 'validation_error');
}

try {
    $pdo->beginTransaction();

    // Resolve user: use session user if authenticated, otherwise find/create by email
    $userId = null;

    if ($isAuthenticated) {
        $userId = (int)$sessionUser['id'];
        // Fill fullName/documentId from session if not provided
        if ($fullName === '' && !empty($sessionUser['full_name'])) {
            $fullName = (string)$sessionUser['full_name'];
        }
        if ($documentId === '' && !empty($sessionUser['document_id'])) {
            $documentId = (string)$sessionUser['document_id'];
        }
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($existingUser)) {
            $userId = (int)$existingUser['id'];
        } else {
            // Auto-create user with chosen password
            $hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $passwordHash = password_hash($password, $hashAlgo);

            $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
            $roleStmt->execute(['slug' => 'user']);
            $roleRow = $roleStmt->fetch();
            if (!is_array($roleRow)) {
                throw new RuntimeException('Rol de usuario no configurado');
            }
            $roleId = (int)$roleRow['id'];

            $insertStmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, document_id, role_id, password_hash, created_at, updated_at)
                 VALUES (:full_name, :email, :document_id, :role_id, :password_hash, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $insertStmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'document_id' => $documentId,
                'role_id' => $roleId,
                'password_hash' => $passwordHash,
            ]);

            $userId = (int)$pdo->lastInsertId();
        }
    }

    // Check for duplicate registration
    $duplicateStmt = $pdo->prepare(
        'SELECT id FROM registrations
         WHERE user_id = :user_id AND forum_id = :forum_id LIMIT 1'
    );
    $duplicateStmt->execute([
        'user_id' => $userId,
        'forum_id' => (int)$resolvedForum['id'],
    ]);
    if ($duplicateStmt->fetchColumn() !== false) {
        api_error('Ya existe una inscripción para este usuario en el foro seleccionado.', 409, 'duplicate_registration');
    }

    // Create uploads directory if needed
    $uploadsDir = __DIR__ . '/../../uploads';
    $signaturesDir = $uploadsDir . '/signatures';
    $paymentsDir = $uploadsDir . '/payments';

    if (!is_dir($signaturesDir) && !@mkdir($signaturesDir, 0755, true)) {
        throw new RuntimeException('No se pudo crear directorio de firmas');
    }
    if (!is_dir($paymentsDir) && !@mkdir($paymentsDir, 0755, true)) {
        throw new RuntimeException('No se pudo crear directorio de pagos');
    }

    // Prepare signature and payment proof data (will save after getting registration ID)
    $signatureImageData = null;
    $paymentProofData = null;
    $paymentProofExt = null;

    if (str_starts_with($signatureDataUrl, 'data:image/png;base64,')) {
        $base64Data = substr($signatureDataUrl, 22);
        $imageData = base64_decode($base64Data, true);
        if ($imageData === false) {
            throw new RuntimeException('Firma digital inválida');
        }
        $signatureImageData = $imageData;
    }

    if (!empty($input['paymentProof']) && is_array($input['paymentProof'])) {
        $proof = $input['paymentProof'];
        $proofBase64 = (string)($proof['base64'] ?? '');
        $proofName = (string)($proof['name'] ?? 'proof');

        if ($proofBase64 !== '') {
            $proofData = base64_decode($proofBase64, true);
            if ($proofData === false) {
                throw new RuntimeException('Comprobante de pago inválido');
            }

            $ext = pathinfo($proofName, PATHINFO_EXTENSION);
            $ext = preg_replace('/[^a-z0-9]/', '', strtolower($ext));
            if ($ext === '') {
                $ext = 'bin';
            }

            $paymentProofData = $proofData;
            $paymentProofExt = $ext;
        }
    }

    // Insert registration (files will be saved after getting registration ID)
    $stmt = $pdo->prepare(
        'INSERT INTO registrations (
          user_id, forum_id, forum_slot, full_name, document_id,
          referral_code, needs_cert, acceptance_checked, signature_data_url, created_at
        ) VALUES (
          :user_id, :forum_id, :forum_slot, :full_name, :document_id,
          :referral_code, :needs_cert, :acceptance_checked, :signature_data_url, CURRENT_TIMESTAMP
        )'
    );
    $stmt->execute([
        'user_id' => $userId,
        'forum_id' => (int)$resolvedForum['id'],
        'forum_slot' => (string)$resolvedForum['slot'],
        'full_name' => $fullName,
        'document_id' => $documentId,
        'referral_code' => $referralCode !== '' ? $referralCode : null,
        'needs_cert' => $needsCert ? 1 : 0,
        'acceptance_checked' => 1,
        'signature_data_url' => $signatureDataUrl,
    ]);

    $registrationId = (int)$pdo->lastInsertId();

    // Now save files using the actual registration ID
    $signatureDataPath = null;
    if ($signatureImageData !== null) {
        $signatureFile = $signaturesDir . '/' . $registrationId . '.png';
        if (!file_put_contents($signatureFile, $signatureImageData)) {
            throw new RuntimeException('No se pudo guardar la firma');
        }
        $signatureDataPath = '/uploads/signatures/' . $registrationId . '.png';
    }

    $paymentProofPath = null;
    if ($paymentProofData !== null && $paymentProofExt !== null) {
        $proofFile = $paymentsDir . '/' . $registrationId . '_proof.' . $paymentProofExt;
        if (!file_put_contents($proofFile, $paymentProofData)) {
            throw new RuntimeException('No se pudo guardar el comprobante de pago');
        }
        $paymentProofPath = '/uploads/payments/' . $registrationId . '_proof.' . $paymentProofExt;
    }

    // Update registration with file paths if needed
    if ($signatureDataPath !== null || $paymentProofPath !== null) {
        $updateStmt = $pdo->prepare(
            'UPDATE registrations
             SET signature_data = COALESCE(:signature_data, signature_data),
                 payment_proof_path = COALESCE(:payment_proof_path, payment_proof_path)
             WHERE id = :id'
        );
        $updateStmt->execute([
            'signature_data' => $signatureDataPath,
            'payment_proof_path' => $paymentProofPath,
            'id' => $registrationId,
        ]);
    }

    // Track referral if applicable - create registration_meta with referrer info
    $referrerUserId = null;
    $networkId = null;
    if ($referralCode !== '') {
        $refStmt = $pdo->prepare(
            'SELECT user_id, id FROM associate_offers
             WHERE referral_code = :referral_code
             LIMIT 1'
        );
        $refStmt->execute(['referral_code' => $referralCode]);
        $refRow = $refStmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($refRow)) {
            $referrerUserId = (int)$refRow['user_id'];
            $networkId = (int)$refRow['id'];
        }
    }

    // Always create registration_meta record for this registration
    $metaStmt = $pdo->prepare(
        'INSERT INTO registration_meta (registration_id, referral_code, referrer_user_id, network_id, country_code)
         VALUES (:registration_id, :referral_code, :referrer_user_id, :network_id, :country_code)
         ON CONFLICT(registration_id) DO UPDATE SET
          referral_code = excluded.referral_code,
          referrer_user_id = excluded.referrer_user_id,
          network_id = excluded.network_id,
          country_code = excluded.country_code'
    );
    $metaStmt->execute([
        'registration_id' => $registrationId,
        'referral_code' => $referralCode !== '' ? $referralCode : null,
        'referrer_user_id' => $referrerUserId,
        'network_id' => $networkId,
        'country_code' => $countryCode !== '' ? $countryCode : null,
    ]);

    $pdo->commit();

    api_json([
        'ok' => true,
        'id' => $registrationId,
        'userId' => $userId,
        'message' => 'Inscripción registrada exitosamente',
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log(sprintf('[api/registrations/create] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    api_error('Error interno del servidor', 500, 'registration_error');
}
