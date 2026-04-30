<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/SignatureGenerator.php';
require_once __DIR__ . '/../../../app/Services/CertificateRenderer.php';

$user = api_current_user();
if (!is_array($user)) {
    header('HTTP/1.0 401 Unauthorized');
    echo '<h1>Acceso restringido</h1><p>Inicia sesión para visualizar el certificado.</p>';
    exit;
}

$pdo = api_require_db();
$certificateId = (int)($_GET['id'] ?? 0);
$type = CertificateRenderer::normalizeType($_GET['type'] ?? null);

if ($certificateId < 1) {
    header('HTTP/1.0 422 Unprocessable Entity');
    echo '<h1>Certificado inválido</h1>';
    exit;
}

$stmt = $pdo->prepare('
    SELECT
        uc.id,
        uc.created_at,
        uc.user_id,
        uc.type,
        u.full_name,
        u.email,
        f.code,
        f.title,
        r.signature_data_url,
        r.signature_data,
        r.full_name AS reg_full_name
    FROM user_certificates uc
    INNER JOIN users u ON u.id = uc.user_id
    INNER JOIN forums f ON f.id = uc.forum_id
    LEFT JOIN registrations r ON r.user_id = uc.user_id AND r.forum_id = uc.forum_id
    WHERE uc.id = :id
    LIMIT 1
');

$stmt->execute(['id' => $certificateId]);
$cert = $stmt->fetch();

if (!$cert) {
    header('HTTP/1.0 404 Not Found');
    echo '<h1>Certificado no encontrado</h1>';
    exit;
}

$userRole = $user['role'] ?? 'guest';
if ($userRole !== 'admin' && (int)($cert['user_id'] ?? 0) !== (int)($user['id'] ?? 0)) {
    header('HTTP/1.0 403 Forbidden');
    echo '<h1>Acceso denegado</h1>';
    exit;
}

$resolvedType = !empty($cert['type']) ? (string)$cert['type'] : 'completion';
if (!CertificateRenderer::isValidType($resolvedType)) {
    $resolvedType = 'completion';
}
if (!empty($_GET['type']) && CertificateRenderer::isValidType((string)$_GET['type'])) {
    $resolvedType = (string)$_GET['type'];
}

$signatureDataUrl = SignatureGenerator::getSignatureDataUrl([
    'full_name' => $cert['reg_full_name'] ?? $cert['full_name'],
    'signature_data_url' => $cert['signature_data_url'],
    'signature_data' => $cert['signature_data'],
]);

$participantName = (string)($cert['reg_full_name'] ?? $cert['full_name'] ?? 'Participante');

echo CertificateRenderer::render($resolvedType, [
    'participantName' => $participantName,
    'forumCode' => (string)$cert['code'],
    'forumTitle' => (string)$cert['title'],
    'dateIssued' => CertificateRenderer::formatDate((string)$cert['created_at']),
    'signatureDataUrl' => $signatureDataUrl,
]);
