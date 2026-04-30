<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

const ADMIN_THEME_SCHEMA_VERSION = 1;
const ADMIN_THEME_KEY = 'theme_v1';

function admin_theme_defaults(): array
{
    return [
        'colors' => [
            'primary' => '#faf5f0',
            'secondary' => '#d9b9a0',
            'accent' => '#8a5a2b',
            'surface' => '#ffffff',
            'text' => '#0f172a',
        ],
        'typography' => [
            'font_family' => 'Plus Jakarta Sans',
            'font_size_base' => '16px',
        ],
        'radius' => ['sm' => '8px', 'md' => '16px', 'lg' => '24px'],
        'shadows' => ['card' => '0 10px 25px rgba(15,23,42,0.1)', 'modal' => '0 20px 45px rgba(15,23,42,0.2)'],
        'spacing' => ['sm' => '8px', 'md' => '16px', 'lg' => '24px'],
        'buttons' => ['size' => 'md', 'padding_y' => '12px', 'padding_x' => '20px'],
    ];
}

function admin_theme_allowed_fonts(): array { return ['Plus Jakarta Sans', 'Inter', 'Roboto', 'Montserrat', 'Lato']; }
function admin_theme_allowed_button_sizes(): array { return ['sm', 'md', 'lg']; }

function admin_theme_validate_hex(string $path, string $value): string {
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        api_error(sprintf('Color inválido en "%s".', $path), 422, 'validation_error', ['field' => $path]);
    }
    return strtolower($value);
}

function admin_theme_validate_css_size(string $path, string $value): string {
    if (!preg_match('/^(\d+(\.\d+)?)(px|rem|em|%)$/', $value)) {
        api_error(sprintf('Medida inválida en "%s".', $path), 422, 'validation_error', ['field' => $path]);
    }
    return $value;
}

function admin_theme_validate_shadow(string $path, string $value): string {
    if (mb_strlen($value) > 100 || !preg_match('/^[a-zA-Z0-9#(),.%\s-]+$/', $value)) {
        api_error(sprintf('Sombra inválida en "%s".', $path), 422, 'validation_error', ['field' => $path]);
    }
    return $value;
}

function admin_theme_validate(array $input): array {
    $defaults = admin_theme_defaults();
    $theme = array_replace_recursive($defaults, $input);

    foreach (['primary', 'secondary', 'accent', 'surface', 'text'] as $k) {
        $theme['colors'][$k] = admin_theme_validate_hex('colors.' . $k, (string)($theme['colors'][$k] ?? ''));
    }

    $font = (string)($theme['typography']['font_family'] ?? '');
    if (!in_array($font, admin_theme_allowed_fonts(), true)) {
        api_error('Tipografía no permitida.', 422, 'validation_error', ['field' => 'typography.font_family']);
    }
    $theme['typography']['font_family'] = $font;
    $theme['typography']['font_size_base'] = admin_theme_validate_css_size('typography.font_size_base', (string)($theme['typography']['font_size_base'] ?? ''));

    foreach (['sm', 'md', 'lg'] as $k) {
        $theme['radius'][$k] = admin_theme_validate_css_size('radius.' . $k, (string)($theme['radius'][$k] ?? ''));
        $theme['spacing'][$k] = admin_theme_validate_css_size('spacing.' . $k, (string)($theme['spacing'][$k] ?? ''));
    }
    $theme['shadows']['card'] = admin_theme_validate_shadow('shadows.card', (string)($theme['shadows']['card'] ?? ''));
    $theme['shadows']['modal'] = admin_theme_validate_shadow('shadows.modal', (string)($theme['shadows']['modal'] ?? ''));

    $size = (string)($theme['buttons']['size'] ?? '');
    if (!in_array($size, admin_theme_allowed_button_sizes(), true)) {
        api_error('Tamaño de botón inválido.', 422, 'validation_error', ['field' => 'buttons.size']);
    }
    $theme['buttons']['size'] = $size;
    $theme['buttons']['padding_y'] = admin_theme_validate_css_size('buttons.padding_y', (string)($theme['buttons']['padding_y'] ?? ''));
    $theme['buttons']['padding_x'] = admin_theme_validate_css_size('buttons.padding_x', (string)($theme['buttons']['padding_x'] ?? ''));

    return $theme;
}

function admin_theme_read(PDO $pdo): array {
    $stmt = $pdo->prepare('SELECT value_text FROM site_settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute(['key' => ADMIN_THEME_KEY]);
    $raw = $stmt->fetchColumn();
    if (!is_string($raw) || $raw === '') {
        return admin_theme_defaults();
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return admin_theme_defaults();
    }
    return array_replace_recursive(admin_theme_defaults(), $decoded);
}

$method = api_require_method(['GET', 'PUT', 'DELETE']);
$pdo = api_require_db();

if ($method === 'PUT') {
    api_require_role('admin');
    $input = api_read_json();
    $theme = admin_theme_validate((array)($input['theme'] ?? []));
    $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, value_type, value_text, updated_at) VALUES (:setting_key, :value_type, :value_text, CURRENT_TIMESTAMP) ON CONFLICT(setting_key) DO UPDATE SET value_text = excluded.value_text, value_type = excluded.value_type, updated_at = CURRENT_TIMESTAMP');
    $stmt->execute(['setting_key' => ADMIN_THEME_KEY, 'value_type' => 'json', 'value_text' => json_encode($theme, JSON_UNESCAPED_UNICODE)]);
}

if ($method === 'DELETE') {
    api_require_role('admin');
    $pdo->prepare('DELETE FROM site_settings WHERE setting_key = :key')->execute(['key' => ADMIN_THEME_KEY]);
}

api_json(['ok' => true, 'schema_version' => ADMIN_THEME_SCHEMA_VERSION, 'theme' => admin_theme_read($pdo), 'meta' => ['allowed_fonts' => admin_theme_allowed_fonts(), 'allowed_button_sizes' => admin_theme_allowed_button_sizes()]]);
