<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$rows = $pdo->query(
    'SELECT users.id, users.full_name, users.email,
            associate_offers.referral_code, associate_offers.payment_method,
            associate_offers.payment_link, associate_offers.price_amount, associate_offers.currency_code,
            associate_offers.updated_at
     FROM users
     INNER JOIN roles ON roles.id = users.role_id
     LEFT JOIN associate_offers ON associate_offers.user_id = users.id
     WHERE roles.slug = "associate"
     ORDER BY users.id ASC'
)->fetchAll();

api_json(['ok' => true, 'items' => $rows]);
