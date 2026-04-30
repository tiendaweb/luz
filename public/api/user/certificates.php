<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/CertificateRenderer.php';

api_require_method(['GET']);

$user = api_current_user();
if (!is_array($user)) {
    api_error('Debes iniciar sesión', 401, 'unauthorized');
}

$pdo = api_require_db();
$userId = (int)($user['id'] ?? 0);
if ($userId < 1) {
    api_error('Usuario inválido', 401, 'unauthorized');
}

$types = CertificateRenderer::SUPPORTED_TYPES;

$registrationsStmt = $pdo->prepare(
    'SELECT registrations.forum_id,
            forums.code AS forum_code,
            forums.title AS forum_title,
            MAX(CASE WHEN ras.status = "approved" THEN 1 ELSE 0 END) AS approved_registration,
            MAX(COALESCE(user_admin_flags.is_validated, 0)) AS is_validated,
            MAX(COALESCE(user_admin_flags.is_paid, 0)) AS is_paid
     FROM registrations
     INNER JOIN forums ON forums.id = registrations.forum_id
     LEFT JOIN registration_admin_state ras ON ras.registration_id = registrations.id
     LEFT JOIN user_admin_flags ON user_admin_flags.user_id = registrations.user_id
     WHERE registrations.user_id = :user_id
     GROUP BY registrations.forum_id, forums.code, forums.title'
);
$registrationsStmt->execute(['user_id' => $userId]);
$registrations = $registrationsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$issuedStmt = $pdo->prepare(
    'SELECT id, forum_id, type, created_at
     FROM user_certificates
     WHERE user_id = :user_id'
);
$issuedStmt->execute(['user_id' => $userId]);
$issuedRows = $issuedStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$issuedMap = [];
foreach ($issuedRows as $row) {
    $type = (string)($row['type'] ?? 'completion');
    if (!CertificateRenderer::isValidType($type)) {
        continue;
    }
    $issuedMap[(int)$row['forum_id'] . ':' . $type] = $row;
}

$items = [];
foreach ($registrations as $registration) {
    $forumId = (int)$registration['forum_id'];
    $approved = (int)($registration['approved_registration'] ?? 0) === 1;
    $paidAndValidated = (int)($registration['is_paid'] ?? 0) === 1 && (int)($registration['is_validated'] ?? 0) === 1;

    foreach ($types as $type) {
        $key = $forumId . ':' . $type;
        $issued = $issuedMap[$key] ?? null;

        $eligible = $type === 'attendance'
            ? $approved
            : ($paidAndValidated || $approved);

        $status = $issued ? 'issued' : ($eligible ? 'eligible' : 'blocked');

        $items[] = [
            'id' => $issued ? (int)$issued['id'] : null,
            'type' => $type,
            'type_label' => $type === 'attendance' ? 'Asistencia' : 'Finalización',
            'status' => $status,
            'forum_id' => $forumId,
            'forum_code' => (string)$registration['forum_code'],
            'forum_title' => (string)$registration['forum_title'],
            'created_at' => $issued ? (string)$issued['created_at'] : null,
            'date' => $issued ? (string)$issued['created_at'] : null,
            'view_url' => $issued ? sprintf('/api/admin/certificate-view.php?id=%d&type=%s', (int)$issued['id'], $type) : null,
        ];
    }
}

usort($items, static function (array $a, array $b): int {
    return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
});

api_json(['ok' => true, 'items' => $items]);
