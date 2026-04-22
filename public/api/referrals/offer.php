<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$code = strtoupper(trim((string)($_GET['code'] ?? '')));
if ($code === '') {
    api_json(['ok' => true, 'offer' => null]);
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'SELECT associate_offers.referral_code, associate_offers.payment_method, associate_offers.payment_link,
            associate_offers.price_amount, associate_offers.currency_code, users.full_name AS associate_name
     FROM associate_offers
     INNER JOIN users ON users.id = associate_offers.user_id
     WHERE associate_offers.referral_code = :code
     LIMIT 1'
);
$stmt->execute(['code' => $code]);
$row = $stmt->fetch();

if (!is_array($row)) {
    api_json(['ok' => true, 'offer' => null]);
}

api_json([
    'ok' => true,
    'offer' => [
        'referralCode' => (string)$row['referral_code'],
        'paymentMethod' => (string)$row['payment_method'],
        'paymentLink' => (string)$row['payment_link'],
        'priceAmount' => (float)$row['price_amount'],
        'currencyCode' => (string)$row['currency_code'],
        'associateName' => (string)$row['associate_name'],
    ],
]);
