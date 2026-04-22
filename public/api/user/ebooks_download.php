<?php

declare(strict_types=1);

require_once __DIR__ . '/../_ebook_access.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$user = api_current_user();
if (!$user) {
    api_json(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
}

$ebookId = (int)($_GET['ebook_id'] ?? 0);
$expiresAt = (int)($_GET['expires'] ?? 0);
$token = trim((string)($_GET['token'] ?? ''));

if ($ebookId <= 0 || $expiresAt <= 0 || $token === '') {
    api_json(['ok' => false, 'error' => 'Parámetros incompletos.'], 422);
}

$pdo = api_require_db();
$userId = (int)$user['id'];

if ($expiresAt < time()) {
    api_ebook_log_download($pdo, $userId, $ebookId, false, 'Token expirado');
    api_json(['ok' => false, 'error' => 'El enlace expiró. Solicita uno nuevo.'], 403);
}

$expectedToken = api_ebook_sign_token($userId, $ebookId, $expiresAt);
if (!hash_equals($expectedToken, $token)) {
    api_ebook_log_download($pdo, $userId, $ebookId, false, 'Token inválido');
    api_json(['ok' => false, 'error' => 'Token inválido.'], 403);
}

$stmt = $pdo->prepare(
    "SELECT id, title, provider, local_path, external_url, min_attendance, requires_approved
     FROM ebooks
     WHERE id = :ebook_id
       AND status = 'published'
     LIMIT 1"
);
$stmt->execute(['ebook_id' => $ebookId]);
$ebook = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($ebook)) {
    api_ebook_log_download($pdo, $userId, $ebookId, false, 'Ebook inexistente');
    api_json(['ok' => false, 'error' => 'Ebook no disponible.'], 404);
}

$permission = api_user_ebook_permission($pdo, $userId, $ebook);
if (($permission['has_access'] ?? false) !== true) {
    api_ebook_log_download($pdo, $userId, $ebookId, false, 'Sin autorización vigente');
    api_json(['ok' => false, 'error' => 'No tienes acceso a este ebook.'], 403);
}

$provider = (string)$ebook['provider'];
if ($provider === 'external') {
    $externalUrl = trim((string)($ebook['external_url'] ?? ''));
    if ($externalUrl === '') {
        api_ebook_log_download($pdo, $userId, $ebookId, false, 'URL externa vacía');
        api_json(['ok' => false, 'error' => 'URL externa no configurada.'], 500);
    }

    api_ebook_log_download($pdo, $userId, $ebookId, true, 'Redirect external');
    header('Location: ' . $externalUrl, true, 302);
    exit;
}

$relativePath = trim((string)($ebook['local_path'] ?? ''));
$baseStorage = realpath(__DIR__ . '/../../../storage/ebooks') ?: (__DIR__ . '/../../../storage/ebooks');
$filePath = realpath($baseStorage . '/' . ltrim($relativePath, '/'));

if ($relativePath === '' || $filePath === false || !str_starts_with($filePath, (string)$baseStorage) || !is_file($filePath)) {
    api_ebook_log_download($pdo, $userId, $ebookId, false, 'Archivo local no disponible');
    api_json(['ok' => false, 'error' => 'Archivo no disponible en el servidor.'], 404);
}

$filename = basename($filePath);
$filesize = filesize($filePath) ?: 0;

api_ebook_log_download($pdo, $userId, $ebookId, true, 'Download local file');
header('Content-Type: application/octet-stream');
header('Content-Length: ' . $filesize);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=60');
readfile($filePath);
exit;
