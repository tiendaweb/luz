<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_db();
$user = api_require_role('admin');
$method = api_require_method(['GET', 'PATCH', 'POST']);
$pdo = api_require_db();

if ($method === 'GET') {
    $context = trim((string)($_GET['context'] ?? ''));
    $locale = trim((string)($_GET['locale'] ?? 'es'));
    $where = 'locale = :locale';
    $params = ['locale' => $locale];
    if ($context !== '') {
        $where .= ' AND context = :context';
        $params['context'] = $context;
    }
    $stmt = $pdo->prepare("SELECT id, block_key, context, locale, content_type, value, version, updated_at FROM content_blocks WHERE {$where} ORDER BY context, block_key");
    $stmt->execute($params);
    api_json(['ok' => true, 'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

$input = api_read_json();
$action = trim((string)($input['action'] ?? 'update'));
if ($action === 'revert') {
    $blockId = (int)($input['blockId'] ?? 0);
    $targetVersion = (int)($input['targetVersion'] ?? 0);
    if ($blockId <= 0 || $targetVersion <= 0) {
        api_error('Parámetros inválidos para revertir.', 422, 'validation_error');
    }
    $stmt = $pdo->prepare('SELECT * FROM content_block_versions WHERE content_block_id = :id AND version = :version LIMIT 1');
    $stmt->execute(['id' => $blockId, 'version' => $targetVersion]);
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($version)) {
        api_error('Versión no encontrada.', 404, 'not_found');
    }

    $pdo->prepare('INSERT INTO content_block_versions (content_block_id, block_key, context, locale, content_type, value, version, changed_by_user_id) SELECT id, block_key, context, locale, content_type, value, version, :uid FROM content_blocks WHERE id = :id')
        ->execute(['uid' => (int)$user['id'], 'id' => $blockId]);

    $pdo->prepare('UPDATE content_blocks SET value = :value, content_type = :content_type, version = version + 1, updated_by_user_id = :uid, updated_at = CURRENT_TIMESTAMP WHERE id = :id')
        ->execute(['value' => (string)$version['value'], 'content_type' => (string)$version['content_type'], 'uid' => (int)$user['id'], 'id' => $blockId]);

    api_json(['ok' => true]);
}

$blockKey = api_input_string($input, 'blockKey', true);
$context = api_input_string($input, 'context', true);
$locale = api_input_string($input, 'locale') ?: 'es';
$value = trim((string)($input['value'] ?? ''));
$contentType = api_input_string($input, 'contentType') ?: 'text';

$stmt = $pdo->prepare('SELECT id, version, value FROM content_blocks WHERE block_key = :block_key AND context = :context AND locale = :locale LIMIT 1');
$stmt->execute(['block_key' => $blockKey, 'context' => $context, 'locale' => $locale]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (is_array($existing)) {
    $pdo->prepare('INSERT INTO content_block_versions (content_block_id, block_key, context, locale, content_type, value, version, changed_by_user_id) SELECT id, block_key, context, locale, content_type, value, version, :uid FROM content_blocks WHERE id = :id')
        ->execute(['uid' => (int)$user['id'], 'id' => (int)$existing['id']]);

    $pdo->prepare('UPDATE content_blocks SET value = :value, content_type = :content_type, version = version + 1, updated_by_user_id = :uid, updated_at = CURRENT_TIMESTAMP WHERE id = :id')
        ->execute(['value' => $value, 'content_type' => $contentType, 'uid' => (int)$user['id'], 'id' => (int)$existing['id']]);
} else {
    $pdo->prepare('INSERT INTO content_blocks (block_key, context, locale, content_type, value, version, updated_by_user_id) VALUES (:block_key, :context, :locale, :content_type, :value, 1, :uid)')
        ->execute(['block_key' => $blockKey, 'context' => $context, 'locale' => $locale, 'content_type' => $contentType, 'value' => $value, 'uid' => (int)$user['id']]);
}

api_json(['ok' => true]);
