<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$method = api_require_method(['POST']);
api_require_role('admin');
$pdo = api_require_db();

if (!isset($_FILES['logo']) || !is_array($_FILES['logo'])) {
    api_error('Debes adjuntar un archivo en el campo "logo".', 422, 'validation_error', ['field' => 'logo']);
}

$file = $_FILES['logo'];
$error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
if ($error !== UPLOAD_ERR_OK) {
    api_error('Error al subir el archivo del logo.', 422, 'validation_error', ['field' => 'logo', 'upload_error' => $error]);
}

$tmpPath = (string)($file['tmp_name'] ?? '');
$mime = (string)(mime_content_type($tmpPath) ?: '');
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($allowed[$mime])) {
    api_error('Formato no soportado. Usa JPG, PNG o WEBP.', 422, 'validation_error', ['field' => 'logo']);
}

$maxSizeBytes = 2 * 1024 * 1024;
$size = (int)($file['size'] ?? 0);
if ($size <= 0 || $size > $maxSizeBytes) {
    api_error('El logo debe pesar menos de 2MB.', 422, 'validation_error', ['field' => 'logo']);
}

$uploadsDir = dirname(__DIR__, 2) . '/uploads';
if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
    api_error('No se pudo preparar el directorio de uploads.', 500, 'system_error');
}

$targetName = 'logo.' . $allowed[$mime];
$targetPath = $uploadsDir . '/' . $targetName;
if (!move_uploaded_file($tmpPath, $targetPath)) {
    api_error('No se pudo guardar el logo.', 500, 'system_error');
}

$logoPublicPath = '/uploads/' . $targetName;
$stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, value_type, value_text, updated_at)
VALUES (:setting_key, :value_type, :value_text, CURRENT_TIMESTAMP)
ON CONFLICT(setting_key) DO UPDATE SET value_type = excluded.value_type, value_text = excluded.value_text, updated_at = CURRENT_TIMESTAMP');
$stmt->execute([
    'setting_key' => 'brand_logo_path',
    'value_type' => 'string',
    'value_text' => $logoPublicPath,
]);

api_json([
    'ok' => true,
    'logo_path' => $logoPublicPath,
]);
