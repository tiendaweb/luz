<?php

declare(strict_types=1);

require_once __DIR__ . '/../Database/connection.php';

/**
 * @return array<string, array{type:string, value:string, updated_at:string|null}>
 */
function app_site_settings_map(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }

    $pdo = app_db_connection();
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (filename TEXT PRIMARY KEY, executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');

    $tableExists = (int)$pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'site_settings'")->fetchColumn() > 0;
    if (!$tableExists) {
        return [];
    }

    $stmt = $pdo->query('SELECT setting_key, value_type, value_text, updated_at FROM site_settings');
    if (!$stmt) {
        return [];
    }

    $settings = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = (string)($row['setting_key'] ?? '');
        if ($key === '') {
            continue;
        }
        $settings[$key] = [
            'type' => (string)($row['value_type'] ?? 'string'),
            'value' => (string)($row['value_text'] ?? ''),
            'updated_at' => isset($row['updated_at']) ? (string)$row['updated_at'] : null,
        ];
    }

    $cache = $settings;
    return $cache;
}

/**
 * @return array<string, string>
 */
function app_public_site_settings(): array
{
    $rows = app_site_settings_map();
    $defaults = [
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
        'theme_v1' => '',
        'brand_logo_path' => '/uploads/logo.jpg',
    ];

    $result = [];
    foreach ($defaults as $key => $fallback) {
        $result[$key] = $rows[$key]['value'] ?? $fallback;
    }

    return $result;
}
