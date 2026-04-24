<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

function admin_ebooks_require_admin(): array
{
    return api_require_role('admin');
}

function admin_ebooks_storage_base_path(): string
{
    $resolved = realpath(__DIR__ . '/../../../../storage/ebooks');
    if ($resolved !== false) {
        return $resolved;
    }

    return __DIR__ . '/../../../../storage/ebooks';
}

function admin_ebooks_validate_https_url(string $url): bool
{
    $parts = parse_url($url);
    if (!is_array($parts)) {
        return false;
    }

    return strtolower((string)($parts['scheme'] ?? '')) === 'https' && trim((string)($parts['host'] ?? '')) !== '';
}

/**
 * @return array<string,mixed>
 */
function admin_ebooks_normalize_payload(array $input, bool $isUpdate = false): array
{
    $title = api_input_string($input, 'title', !$isUpdate);
    $description = trim((string)($input['description'] ?? ''));
    $provider = trim((string)($input['provider'] ?? ''));
    $status = trim((string)($input['status'] ?? 'published'));
    $localPath = trim((string)($input['local_path'] ?? ''));
    $externalUrl = trim((string)($input['external_url'] ?? ''));

    if ($provider === '' && $isUpdate) {
        $provider = '';
    }

    if ($provider !== '' && !in_array($provider, ['local', 'external'], true)) {
        api_error('Proveedor inválido. Usa local o external.', 422, 'validation_error', ['field' => 'provider']);
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        api_error('Estado inválido. Usa draft o published.', 422, 'validation_error', ['field' => 'status']);
    }

    $minAttendance = array_key_exists('min_attendance', $input)
        ? (float)$input['min_attendance']
        : 75.0;

    if ($minAttendance < 0 || $minAttendance > 100) {
        api_error('El umbral de asistencia debe estar entre 0 y 100.', 422, 'validation_error', ['field' => 'min_attendance']);
    }

    $requiresApproved = array_key_exists('requires_approved', $input)
        ? ((int)(bool)$input['requires_approved'])
        : 1;

    if ($provider === 'local') {
        if ($localPath === '') {
            api_error('local_path es obligatorio cuando provider=local.', 422, 'validation_error', ['field' => 'local_path']);
        }

        $baseStorage = admin_ebooks_storage_base_path();
        $filePath = realpath($baseStorage . '/' . ltrim($localPath, '/'));

        if ($filePath === false || !str_starts_with($filePath, $baseStorage) || !is_file($filePath)) {
            api_error('El archivo local no existe en storage/ebooks.', 422, 'validation_error', ['field' => 'local_path']);
        }

        $externalUrl = '';
    }

    if ($provider === 'external') {
        if ($externalUrl === '' || !admin_ebooks_validate_https_url($externalUrl)) {
            api_error('external_url debe ser una URL HTTPS válida cuando provider=external.', 422, 'validation_error', ['field' => 'external_url']);
        }

        $localPath = '';
    }

    return [
        'title' => $title,
        'description' => $description,
        'provider' => $provider,
        'status' => $status,
        'local_path' => $localPath,
        'external_url' => $externalUrl,
        'min_attendance' => $minAttendance,
        'requires_approved' => $requiresApproved,
    ];
}
