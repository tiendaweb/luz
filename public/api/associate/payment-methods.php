<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET', 'POST', 'PUT']);

$pdo = api_require_db();
$currentUser = api_current_user();

if (!is_array($currentUser) || ($currentUser['role'] ?? '') !== 'associate') {
    api_error('Solo asociados pueden acceder a este endpoint', 403, 'forbidden');
}

$userId = (int)($currentUser['id'] ?? 0);
if ($userId <= 0) {
    api_error('Usuario no autenticado', 401, 'unauthorized');
}

function payment_validation_error(string $field, string $message): void {
    api_json(['ok' => false, 'error' => $message, 'code' => 'validation_error', 'field' => $field], 422);
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, country_code, method_type, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, payment_email, is_active, created_at, updated_at
         FROM associate_payment_methods
         WHERE user_id = :user_id AND is_active = 1
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $paymentMethod = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($paymentMethod)) {
        api_json(['ok' => true, 'data' => [
            'id' => (int)$paymentMethod['id'],
            'associateId' => (int)$paymentMethod['user_id'],
            'countryCode' => (string)($paymentMethod['country_code'] ?? 'AR'),
            'methodType' => (string)($paymentMethod['method_type'] ?? 'bank_transfer'),
            'bankName' => (string)($paymentMethod['bank_name'] ?? ''),
            'accountHolder' => (string)($paymentMethod['account_holder'] ?? ''),
            'accountNumber' => (string)($paymentMethod['account_number'] ?? ''),
            'accountType' => (string)($paymentMethod['account_type'] ?? ''),
            'currency' => (string)($paymentMethod['currency'] ?? 'ARS'),
            'aliasOrReference' => (string)($paymentMethod['alias_or_reference'] ?? ''),
            'paymentEmail' => (string)($paymentMethod['payment_email'] ?? ''),
            'isActive' => (int)$paymentMethod['is_active'] === 1,
            'createdAt' => (string)($paymentMethod['created_at'] ?? ''),
            'updatedAt' => (string)($paymentMethod['updated_at'] ?? ''),
        ]]);
    }
    api_json(['ok' => true, 'data' => null]);
}

$input = api_read_json();
$countryCode = strtoupper(trim((string)($input['countryCode'] ?? 'AR')));
$methodType = strtolower(trim((string)($input['methodType'] ?? 'bank_transfer')));
$bankName = trim((string)($input['bankName'] ?? ''));
$accountHolder = trim((string)($input['accountHolder'] ?? ''));
$accountNumber = trim((string)($input['accountNumber'] ?? ''));
$accountType = trim((string)($input['accountType'] ?? ''));
$currency = strtoupper(trim((string)($input['currency'] ?? 'ARS')));
$aliasOrReference = trim((string)($input['aliasOrReference'] ?? ''));
$paymentEmail = strtolower(trim((string)($input['paymentEmail'] ?? '')));

if (!preg_match('/^[A-Z]{2}$/', $countryCode)) payment_validation_error('countryCode', 'País inválido.');
$allowedMethods = ['bank_transfer', 'email_payment', 'wallet'];
if (!in_array($methodType, $allowedMethods, true)) payment_validation_error('methodType', 'Método de pago inválido.');

if ($methodType === 'bank_transfer') {
    if ($bankName === '') payment_validation_error('bankName', 'Nombre del banco es obligatorio.');
    if ($accountHolder === '') payment_validation_error('accountHolder', 'Titular de la cuenta es obligatorio.');
    if ($accountNumber === '') payment_validation_error('accountNumber', 'Número de cuenta/CBU es obligatorio.');
    if ($countryCode === 'AR') {
        if (!preg_match('/^\d{22}$/', preg_replace('/\D+/', '', $accountNumber))) {
            payment_validation_error('accountNumber', 'CBU inválido: debe tener 22 dígitos.');
        }
        if ($aliasOrReference !== '' && !preg_match('/^[a-z0-9.-]{6,20}$/i', $aliasOrReference)) {
            payment_validation_error('aliasOrReference', 'Alias inválido (6-20 caracteres alfanuméricos, punto o guion).');
        }
    }
}

if ($methodType === 'email_payment') {
    if ($paymentEmail === '') payment_validation_error('paymentEmail', 'El email de pago es obligatorio.');
    if (!filter_var($paymentEmail, FILTER_VALIDATE_EMAIL)) payment_validation_error('paymentEmail', 'Email de pago inválido.');
}

if ($methodType === 'wallet') {
    if ($aliasOrReference === '') payment_validation_error('aliasOrReference', 'La wallet es obligatoria.');
    if (!preg_match('/^[a-zA-Z0-9._:-]{4,120}$/', $aliasOrReference)) {
        payment_validation_error('aliasOrReference', 'Formato de wallet inválido.');
    }
}

$paymentSummary = implode(' · ', array_values(array_filter([
    'País: ' . $countryCode,
    'Método: ' . $methodType,
    $bankName !== '' ? 'Banco: ' . $bankName : null,
    $accountHolder !== '' ? 'Titular: ' . $accountHolder : null,
    $accountNumber !== '' ? 'Cuenta/CBU: ' . $accountNumber : null,
    $aliasOrReference !== '' ? 'Alias/Ref: ' . $aliasOrReference : null,
    $paymentEmail !== '' ? 'Email: ' . $paymentEmail : null,
    $currency !== '' ? 'Moneda: ' . $currency : null,
], static fn($v): bool => is_string($v) && trim($v) !== '')));

try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE associate_payment_methods SET is_active = 0, deactivated_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id AND is_active = 1')
        ->execute(['user_id' => $userId]);

    $stmt = $pdo->prepare('INSERT INTO associate_payment_methods (user_id, country_code, method_type, bank_name, account_holder, account_number, account_type, currency, alias_or_reference, payment_email, is_active, activated_at, created_at) VALUES (:user_id, :country_code, :method_type, :bank_name, :account_holder, :account_number, :account_type, :currency, :alias_or_reference, :payment_email, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
    $stmt->execute([
        'user_id' => $userId,
        'country_code' => $countryCode,
        'method_type' => $methodType,
        'bank_name' => $bankName,
        'account_holder' => $accountHolder,
        'account_number' => $accountNumber,
        'account_type' => $accountType,
        'currency' => $currency,
        'alias_or_reference' => $aliasOrReference,
        'payment_email' => $paymentEmail,
    ]);

    $pdo->prepare('INSERT INTO associate_offers (user_id, referral_code, payment_method, payment_link, price_amount, currency_code, updated_at) VALUES (:user_id, :referral_code, :payment_method, :payment_link, :price_amount, :currency_code, CURRENT_TIMESTAMP) ON CONFLICT(user_id) DO UPDATE SET payment_method = excluded.payment_method, currency_code = excluded.currency_code, updated_at = CURRENT_TIMESTAMP')
        ->execute([
            'user_id' => $userId,
            'referral_code' => strtoupper(sprintf('ASC%d%s', $userId, bin2hex(random_bytes(2)))),
            'payment_method' => $paymentSummary,
            'payment_link' => '/inscripcion',
            'price_amount' => 0,
            'currency_code' => $currency !== '' ? $currency : 'USD',
        ]);

    $pdo->commit();
    api_json(['ok' => true, 'message' => 'Datos de pago guardados exitosamente']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log(sprintf('[api/associate/payment-methods] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    api_error('Error interno del servidor', 500, 'database_error');
}
