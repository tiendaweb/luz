<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

const ADMIN_SETTINGS_ALLOWED = [
    'public_phone_primary' => 'phone',
    'public_phone_secondary' => 'phone',
    'public_email_primary' => 'email',
    'public_email_support' => 'email',
    'director_name' => 'string',
    'director_title' => 'string',
    'director_location' => 'string',
    'contact_short_text' => 'text',
    'contact_cta_text' => 'text',
    'brand_color_primary' => 'color',
    'brand_color_accent' => 'color',
];

function admin_settings_defaults(): array
{
    return [
        'public_phone_primary' => '+54 9 11 4000-0000',
        'public_phone_secondary' => '+54 9 11 4000-0001',
        'public_email_primary' => 'contacto@forospsme.com',
        'public_email_support' => 'soporte@forospsme.com',
        'director_name' => 'María Luz Genovese',
        'director_title' => 'Psicóloga Social especializada en Salud Mental y Emocional (SmE)',
        'director_location' => 'Buenos Aires, Argentina',
        'contact_short_text' => 'Comunidad de debate y fortalecimiento psicosocial en Latinoamérica.',
        'contact_cta_text' => 'Escribinos para coordinar entrevistas, consultas o información de próximos foros.',
        'brand_color_primary' => '#0d9488',
        'brand_color_accent' => '#0f766e',
    ];
}

function admin_settings_validate(string $key, string $type, string $value): string
{
    if ($value === '') {
        api_error(sprintf('El campo "%s" no puede estar vacío.', $key), 422, 'validation_error', ['field' => $key]);
    }

    if ($type === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        api_error(sprintf('Email inválido para "%s".', $key), 422, 'validation_error', ['field' => $key]);
    }

    if ($type === 'color' && !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        api_error(sprintf('Color inválido para "%s".', $key), 422, 'validation_error', ['field' => $key]);
    }

    if ($type === 'phone' && !preg_match('/^[0-9+()\-\s]{7,30}$/', $value)) {
        api_error(sprintf('Teléfono inválido para "%s".', $key), 422, 'validation_error', ['field' => $key]);
    }

    return $value;
}

$method = api_require_method(['GET', 'PATCH']);
$pdo = api_require_db();

if ($method === 'PATCH') {
    api_require_role('admin');
    $input = api_read_json();
    $rawSettings = $input['settings'] ?? null;
    if (!is_array($rawSettings)) {
        api_error('Debes enviar un objeto "settings".', 422, 'validation_error', ['field' => 'settings']);
    }

    $allowed = ADMIN_SETTINGS_ALLOWED;
    $updateStmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, value_type, value_text, updated_at)
         VALUES (:setting_key, :value_type, :value_text, CURRENT_TIMESTAMP)
         ON CONFLICT(setting_key) DO UPDATE SET
           value_type = excluded.value_type,
           value_text = excluded.value_text,
           updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($allowed as $key => $type) {
        if (!array_key_exists($key, $rawSettings)) {
            continue;
        }
        $value = trim((string)$rawSettings[$key]);
        $value = admin_settings_validate($key, $type, $value);
        $updateStmt->execute([
            'setting_key' => $key,
            'value_type' => $type,
            'value_text' => $value,
        ]);
    }
}

$defaults = admin_settings_defaults();
$rows = $pdo->query('SELECT setting_key, value_type, value_text, updated_at FROM site_settings')->fetchAll(PDO::FETCH_ASSOC);
$items = [];
foreach ($defaults as $key => $fallback) {
    $type = ADMIN_SETTINGS_ALLOWED[$key] ?? 'string';
    $items[$key] = ['key' => $key, 'type' => $type, 'value' => $fallback, 'updated_at' => null];
}
foreach ($rows as $row) {
    $key = (string)($row['setting_key'] ?? '');
    if (!isset($items[$key])) {
        continue;
    }

    $items[$key] = [
        'key' => $key,
        'type' => (string)($row['value_type'] ?? $items[$key]['type']),
        'value' => (string)($row['value_text'] ?? $items[$key]['value']),
        'updated_at' => isset($row['updated_at']) ? (string)$row['updated_at'] : null,
    ];
}

api_json([
    'ok' => true,
    'items' => array_values($items),
    'settings' => array_reduce($items, static function (array $carry, array $item): array {
        $carry[$item['key']] = $item['value'];
        return $carry;
    }, []),
]);
