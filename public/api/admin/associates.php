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
            associate_offers.updated_at,
            apm.country_code AS payment_country_code,
            apm.method_type AS payment_method_type,
            apm.bank_name AS payment_bank_name,
            apm.account_holder AS payment_account_holder,
            apm.account_number AS payment_account_number,
            apm.account_type AS payment_account_type,
            apm.alias_or_reference AS payment_alias_or_reference,
            apm.payment_email AS payment_email,
            apm.currency AS payment_currency,
            apm.is_active AS payment_is_active,
            apm.activated_at AS payment_activated_at,
            apm.deactivated_at AS payment_deactivated_at
     FROM users
     INNER JOIN roles ON roles.id = users.role_id
     LEFT JOIN associate_offers ON associate_offers.user_id = users.id
     LEFT JOIN associate_payment_methods apm ON apm.id = (
       SELECT apm2.id FROM associate_payment_methods apm2
       WHERE apm2.user_id = users.id
       ORDER BY apm2.is_active DESC, apm2.id DESC LIMIT 1
     )
     WHERE roles.slug = "associate"
     ORDER BY users.id ASC'
)->fetchAll();

api_json(['ok' => true, 'items' => $rows]);
