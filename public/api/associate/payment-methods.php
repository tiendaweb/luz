<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET', 'POST', 'PUT']);

$pdo = api_require_db();
$currentUser = api_current_user();

// Only associates can access this endpoint
if (!is_array($currentUser) || ($currentUser['role'] ?? '') !== 'associate') {
    api_error('Solo asociados pueden acceder a este endpoint', 403, 'forbidden');
}

$userId = (int)($currentUser['id'] ?? 0);
if ($userId <= 0) {
    api_error('Usuario no autenticado', 401, 'unauthorized');
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method === 'GET') {
    // Retrieve payment method for this associate
    $stmt = $pdo->prepare(
        'SELECT id, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, payment_email, is_active
         FROM associate_payment_methods
         WHERE user_id = :user_id LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $paymentMethod = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($paymentMethod)) {
        api_json([
            'ok' => true,
            'data' => [
                'id' => (int)$paymentMethod['id'],
                'bankName' => (string)$paymentMethod['bank_name'],
                'accountHolder' => (string)$paymentMethod['account_holder'],
                'accountNumber' => (string)$paymentMethod['account_number'],
                'accountType' => (string)($paymentMethod['account_type'] ?? ''),
                'currency' => (string)($paymentMethod['currency'] ?? 'ARS'),
                'aliasOrReference' => (string)($paymentMethod['alias_or_reference'] ?? ''),
                'paymentEmail' => (string)($paymentMethod['payment_email'] ?? ''),
                'isActive' => (int)$paymentMethod['is_active'] === 1,
            ],
        ]);
    } else {
        api_json(['ok' => true, 'data' => null]);
    }
}

if ($method === 'POST' || $method === 'PUT') {
    // Create or update payment method
    $input = api_read_json();

    $bankName = trim((string)($input['bankName'] ?? ''));
    $accountHolder = trim((string)($input['accountHolder'] ?? ''));
    $accountNumber = trim((string)($input['accountNumber'] ?? ''));
    $accountType = trim((string)($input['accountType'] ?? ''));
    $currency = trim((string)($input['currency'] ?? 'ARS'));
    $aliasOrReference = trim((string)($input['aliasOrReference'] ?? ''));
    $paymentEmail = trim((string)($input['paymentEmail'] ?? ''));
    if ($paymentEmail !== '' && !filter_var($paymentEmail, FILTER_VALIDATE_EMAIL)) {
        api_error('Email de pago inválido', 422, 'validation_error');
    }
    $paymentSummary = implode(' · ', array_values(array_filter([
        $bankName !== '' ? 'Banco: ' . $bankName : null,
        $accountHolder !== '' ? 'Titular: ' . $accountHolder : null,
        $accountNumber !== '' ? 'Cuenta: ' . $accountNumber : null,
        $aliasOrReference !== '' ? 'Ref: ' . $aliasOrReference : null,
        $paymentEmail !== '' ? 'Email: ' . $paymentEmail : null,
        $currency !== '' ? 'Moneda: ' . $currency : null,
    ], static fn($value): bool => is_string($value) && trim($value) !== '')));

    if ($bankName === '') {
        api_error('Nombre del banco es obligatorio', 422, 'validation_error');
    }
    if ($accountHolder === '') {
        api_error('Titular de la cuenta es obligatorio', 422, 'validation_error');
    }
    if ($accountNumber === '') {
        api_error('Número de cuenta es obligatorio', 422, 'validation_error');
    }

    try {
        $pdo->beginTransaction();

        // Check if record exists
        $checkStmt = $pdo->prepare(
            'SELECT id FROM associate_payment_methods WHERE user_id = :user_id LIMIT 1'
        );
        $checkStmt->execute(['user_id' => $userId]);
        $existingRecord = $checkStmt->fetch();

        if (is_array($existingRecord)) {
            // Update existing record
            $stmt = $pdo->prepare(
                'UPDATE associate_payment_methods
                 SET bank_name = :bank_name,
                     account_holder = :account_holder,
                     account_number = :account_number,
                     account_type = :account_type,
                     currency = :currency,
                     alias_or_reference = :alias_or_reference,
                     payment_email = :payment_email,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id'
            );
            $stmt->execute([
                'bank_name' => $bankName,
                'account_holder' => $accountHolder,
                'account_number' => $accountNumber,
                'account_type' => $accountType,
                'currency' => $currency,
                'alias_or_reference' => $aliasOrReference,
                'payment_email' => $paymentEmail,
                'user_id' => $userId,
            ]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare(
                'INSERT INTO associate_payment_methods
                 (user_id, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, payment_email, is_active, created_at)
                 VALUES (:user_id, :bank_name, :account_holder, :account_number, :account_type, :currency, :alias_or_reference, :payment_email, 1, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'bank_name' => $bankName,
                'account_holder' => $accountHolder,
                'account_number' => $accountNumber,
                'account_type' => $accountType,
                'currency' => $currency,
                'alias_or_reference' => $aliasOrReference,
                'payment_email' => $paymentEmail,
            ]);
        }

        $offerStmt = $pdo->prepare(
            'SELECT id FROM associate_offers WHERE user_id = :user_id LIMIT 1'
        );
        $offerStmt->execute(['user_id' => $userId]);
        $offerExists = $offerStmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($offerExists)) {
            $syncOffer = $pdo->prepare(
                'UPDATE associate_offers
                 SET payment_method = :payment_method,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id'
            );
            $syncOffer->execute([
                'payment_method' => $paymentSummary,
                'user_id' => $userId,
            ]);
        } else {
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $generatedCode = strtoupper(sprintf('ASC%d%s', $userId, bin2hex(random_bytes(2))));
                $insertOffer = $pdo->prepare(
                    'INSERT INTO associate_offers (
                        user_id, referral_code, payment_method, payment_link, price_amount, currency_code, updated_at
                    ) VALUES (
                        :user_id, :referral_code, :payment_method, :payment_link, :price_amount, :currency_code, CURRENT_TIMESTAMP
                    ) ON CONFLICT(user_id) DO NOTHING'
                );

                $insertOffer->execute([
                    'user_id' => $userId,
                    'referral_code' => $generatedCode,
                    'payment_method' => $paymentSummary,
                    'payment_link' => '/inscripcion',
                    'price_amount' => 0,
                    'currency_code' => $currency !== '' ? $currency : 'USD',
                ]);

                $offerStmt->execute(['user_id' => $userId]);
                if (is_array($offerStmt->fetch(PDO::FETCH_ASSOC))) {
                    break;
                }
            }
        }

        $pdo->commit();

        api_json([
            'ok' => true,
            'message' => 'Datos de pago guardados exitosamente',
            'data' => [
                'bankName' => $bankName,
                'accountHolder' => $accountHolder,
                'accountNumber' => $accountNumber,
                'accountType' => $accountType,
                'currency' => $currency,
                'aliasOrReference' => $aliasOrReference,
                'paymentEmail' => $paymentEmail,
            ],
        ]);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_error('Error al guardar datos de pago: ' . $e->getMessage(), 500, 'database_error');
    }
}
