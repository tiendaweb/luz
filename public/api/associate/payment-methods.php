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
        'SELECT id, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, is_active
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
                'user_id' => $userId,
            ]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare(
                'INSERT INTO associate_payment_methods
                 (user_id, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, is_active, created_at)
                 VALUES (:user_id, :bank_name, :account_holder, :account_number, :account_type, :currency, :alias_or_reference, 1, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'bank_name' => $bankName,
                'account_holder' => $accountHolder,
                'account_number' => $accountNumber,
                'account_type' => $accountType,
                'currency' => $currency,
                'alias_or_reference' => $aliasOrReference,
            ]);
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
            ],
        ]);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_error('Error al guardar datos de pago: ' . $e->getMessage(), 500, 'database_error');
    }
}
