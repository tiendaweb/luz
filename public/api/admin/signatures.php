<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../app/Services/SignatureGenerator.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$forumId = (int)($_GET['forum'] ?? 0);

$sql = '
    SELECT
        r.id AS registration_id,
        r.user_id,
        r.forum_id,
        r.full_name,
        r.signature_data_url,
        r.signature_data,
        u.email,
        f.code AS forum_code,
        f.title AS forum_title
    FROM registrations r
    INNER JOIN users u ON u.id = r.user_id
    INNER JOIN forums f ON f.id = r.forum_id
';

if ($forumId > 0) {
    $sql .= ' WHERE r.forum_id = :forum_id';
}

$sql .= ' ORDER BY f.id DESC, r.full_name ASC';

$stmt = $pdo->prepare($sql);
if ($forumId > 0) {
    $stmt->execute(['forum_id' => $forumId]);
} else {
    $stmt->execute();
}

$items = $stmt->fetchAll();

foreach ($items as &$item) {
    $item['has_real_signature'] = !empty($item['signature_data_url']) || !empty($item['signature_data']);
    if (empty($item['signature_data_url']) && empty($item['signature_data'])) {
        $item['signature_data_url'] = SignatureGenerator::generateFakeSignature($item['full_name']);
    }
    $item['has_signature'] = true;
}
unset($item);

api_json(['ok' => true, 'items' => $items]);
