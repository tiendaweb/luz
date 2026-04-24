<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET']);

$currentUser = api_current_user();

if (!is_array($currentUser) || ($currentUser['role'] ?? '') !== 'associate') {
    api_error('Solo asociados pueden acceder a este endpoint', 403, 'forbidden');
}

$userId = (int)($currentUser['id'] ?? 0);
if ($userId <= 0) {
    api_error('Usuario no autenticado', 401, 'unauthorized');
}

$country = strtoupper(trim((string)($_GET['country'] ?? 'AR')));
if (!preg_match('/^[A-Z]{2}$/', $country)) {
    $country = 'AR';
}

$scheme = (string)($_SERVER['REQUEST_SCHEME'] ?? 'https');
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$baseUrl = $scheme . '://' . $host;

$pdo = api_require_db();
$stmt = $pdo->prepare('SELECT referral_code FROM associate_offers WHERE user_id = :user_id LIMIT 1');
$stmt->execute(['user_id' => $userId]);
$referralCode = $stmt->fetchColumn();

if (!is_string($referralCode) || trim($referralCode) === '') {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $generatedCode = strtoupper(sprintf('ASC%d%s', $userId, bin2hex(random_bytes(2))));
        $insert = $pdo->prepare(
            'INSERT INTO associate_offers (
                user_id, referral_code, payment_method, payment_link, price_amount, currency_code, updated_at
            ) VALUES (
                :user_id, :referral_code, :payment_method, :payment_link, :price_amount, :currency_code, :updated_at
            ) ON CONFLICT(user_id) DO NOTHING'
        );

        try {
            $insert->execute([
                'user_id' => $userId,
                'referral_code' => $generatedCode,
                'payment_method' => 'Pendiente de configuración',
                'payment_link' => $baseUrl . '/inscripcion',
                'price_amount' => 0,
                'currency_code' => 'USD',
                'updated_at' => gmdate('c'),
            ]);
        } catch (PDOException $error) {
            continue;
        }

        $stmt->execute(['user_id' => $userId]);
        $createdCode = $stmt->fetchColumn();
        if (is_string($createdCode) && trim($createdCode) !== '') {
            $referralCode = $createdCode;
            break;
        }
    }
}

if (!is_string($referralCode) || trim($referralCode) === '') {
    api_error('No se pudo generar el código de referido persistente.', 500, 'referral_code_generation_failed');
}

$referralLink = $baseUrl . '/inscripcion?ref=' . urlencode($referralCode) . '&country=' . urlencode($country);

api_json([
    'ok' => true,
    'referralLink' => $referralLink,
    'referralCode' => $referralCode,
    'country' => $country,
    'message' => 'Link de referido persistente generado exitosamente',
]);
