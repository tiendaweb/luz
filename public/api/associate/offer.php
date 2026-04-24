<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user = api_current_user();

if (!is_array($user) || ($user['role'] ?? '') !== 'associate') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$userId = (int)$user['id'];

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT referral_code, payment_method, payment_link, price_amount, currency_code FROM associate_offers WHERE user_id = :user_id LIMIT 1');
    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch();
    api_json([
        'ok' => true,
        'offer' => is_array($row) ? [
            'referralCode' => (string)$row['referral_code'],
            'paymentMethod' => (string)$row['payment_method'],
            'paymentLink' => (string)$row['payment_link'],
            'priceAmount' => (float)$row['price_amount'],
            'currencyCode' => (string)$row['currency_code'],
        ] : null,
    ]);
}

if ($method !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$referralCode = strtoupper(trim((string)($input['referralCode'] ?? '')));
$paymentMethod = trim((string)($input['paymentMethod'] ?? ''));
$paymentLink = trim((string)($input['paymentLink'] ?? ''));
$priceAmount = (float)($input['priceAmount'] ?? 0);
$currencyCode = strtoupper(trim((string)($input['currencyCode'] ?? '')));

if (!preg_match('/^[A-Z0-9\-_]{4,32}$/', $referralCode)) {
    api_json(['ok' => false, 'error' => 'Código de referido inválido.'], 422);
}
if ($paymentMethod === '' || mb_strlen($paymentMethod) > 80) {
    api_json(['ok' => false, 'error' => 'Método de cobro inválido.'], 422);
}
if (!preg_match('/^https?:\/\//i', $paymentLink)) {
    api_json(['ok' => false, 'error' => 'El enlace de cobro debe iniciar con http:// o https://'], 422);
}
if ($priceAmount <= 0 || $priceAmount > 999999) {
    api_json(['ok' => false, 'error' => 'Precio inválido.'], 422);
}
if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
    api_json(['ok' => false, 'error' => 'Moneda inválida (usa código ISO de 3 letras).'], 422);
}

$stmt = $pdo->prepare(
    'INSERT INTO associate_offers (user_id, referral_code, payment_method, payment_link, price_amount, currency_code, updated_at)
     VALUES (:user_id, :referral_code, :payment_method, :payment_link, :price_amount, :currency_code, :updated_at)
     ON CONFLICT(user_id) DO UPDATE SET
      referral_code = excluded.referral_code,
      payment_method = excluded.payment_method,
      payment_link = excluded.payment_link,
      price_amount = excluded.price_amount,
      currency_code = excluded.currency_code,
      updated_at = excluded.updated_at'
);

try {
    $stmt->execute([
        'user_id' => $userId,
        'referral_code' => $referralCode,
        'payment_method' => $paymentMethod,
        'payment_link' => $paymentLink,
        'price_amount' => $priceAmount,
        'currency_code' => $currencyCode,
        'updated_at' => gmdate('c'),
    ]);
} catch (PDOException $error) {
    api_json(['ok' => false, 'error' => 'El código de referido ya está en uso por otro asociado.'], 422);
}

api_json(['ok' => true]);
