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

$country = strtoupper(trim((string)($_GET['country'] ?? '')));
if (!preg_match('/^[A-Z]{2}$/', $country)) {
    $country = '*';
}

$sql = <<<'SQL'
WITH associate_ref AS (
    SELECT
        associate_offers.user_id,
        associate_offers.referral_code,
        associate_offers.payment_method,
        associate_offers.payment_link,
        associate_offers.price_amount,
        associate_offers.currency_code,
        users.full_name AS associate_name
    FROM associate_offers
    INNER JOIN users ON users.id = associate_offers.user_id
    WHERE associate_offers.referral_code = :code
    LIMIT 1
),
candidates AS (
    -- 1) Oferta regional específica del asociado
    SELECT
        associate_ref.referral_code,
        associate_offer_regions.payment_method,
        associate_offer_regions.payment_link,
        associate_offer_regions.price_amount,
        associate_offer_regions.currency_code,
        associate_ref.associate_name,
        associate_offer_regions.country_code,
        1 AS priority,
        'associate_country' AS source
    FROM associate_ref
    INNER JOIN associate_offer_regions
        ON associate_offer_regions.associate_user_id = associate_ref.user_id
       AND associate_offer_regions.is_active = 1
       AND associate_offer_regions.country_code = :country

    UNION ALL

    -- 2) Default del asociado
    SELECT
        associate_ref.referral_code,
        associate_offer_regions.payment_method,
        associate_offer_regions.payment_link,
        associate_offer_regions.price_amount,
        associate_offer_regions.currency_code,
        associate_ref.associate_name,
        associate_offer_regions.country_code,
        2 AS priority,
        'associate_default' AS source
    FROM associate_ref
    INNER JOIN associate_offer_regions
        ON associate_offer_regions.associate_user_id = associate_ref.user_id
       AND associate_offer_regions.is_active = 1
       AND associate_offer_regions.country_code = '*'

    UNION ALL

    -- 3) Fallback global por país
    SELECT
        associate_ref.referral_code,
        associate_offer_regions.payment_method,
        associate_offer_regions.payment_link,
        associate_offer_regions.price_amount,
        associate_offer_regions.currency_code,
        associate_ref.associate_name,
        associate_offer_regions.country_code,
        3 AS priority,
        'global_country' AS source
    FROM associate_ref
    INNER JOIN associate_offer_regions
        ON associate_offer_regions.associate_user_id IS NULL
       AND associate_offer_regions.is_active = 1
       AND associate_offer_regions.country_code = :country

    UNION ALL

    -- 4) Fallback global default
    SELECT
        associate_ref.referral_code,
        associate_offer_regions.payment_method,
        associate_offer_regions.payment_link,
        associate_offer_regions.price_amount,
        associate_offer_regions.currency_code,
        associate_ref.associate_name,
        associate_offer_regions.country_code,
        4 AS priority,
        'global_default' AS source
    FROM associate_ref
    INNER JOIN associate_offer_regions
        ON associate_offer_regions.associate_user_id IS NULL
       AND associate_offer_regions.is_active = 1
       AND associate_offer_regions.country_code = '*'

    UNION ALL

    -- 5) Compatibilidad legado
    SELECT
        associate_ref.referral_code,
        associate_ref.payment_method,
        associate_ref.payment_link,
        associate_ref.price_amount,
        associate_ref.currency_code,
        associate_ref.associate_name,
        NULL AS country_code,
        5 AS priority,
        'legacy_associate_offer' AS source
    FROM associate_ref
)
SELECT
    referral_code, payment_method, payment_link, price_amount,
    currency_code, associate_name, country_code, source
FROM candidates
ORDER BY priority
LIMIT 1
SQL;

$pdo = api_require_db();
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'code' => $code,
    'country' => $country,
]);
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
        'countryCode' => is_string($row['country_code']) ? (string)$row['country_code'] : null,
        'source' => (string)$row['source'],
    ],
]);
