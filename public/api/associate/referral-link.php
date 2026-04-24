<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET']);

$currentUser = api_current_user();

// Only associates can access this endpoint
if (!is_array($currentUser) || ($currentUser['role'] ?? '') !== 'associate') {
    api_error('Solo asociados pueden acceder a este endpoint', 403, 'forbidden');
}

$userId = (int)($currentUser['id'] ?? 0);
if ($userId <= 0) {
    api_error('Usuario no autenticado', 401, 'unauthorized');
}

// Generate a unique referral token
$token = bin2hex(random_bytes(16));
$referralCode = $userId . '_' . $token;

// Get the application base URL
$scheme = (string)($_SERVER['REQUEST_SCHEME'] ?? 'https');
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$baseUrl = $scheme . '://' . $host;

$referralLink = $baseUrl . '/inscripcion?ref=' . urlencode($referralCode);

api_json([
    'ok' => true,
    'referralLink' => $referralLink,
    'referralCode' => $referralCode,
    'message' => 'Link de referido generado exitosamente',
]);
