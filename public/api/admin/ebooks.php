<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_role('admin');
$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

const ADMIN_EBOOK_ALLOWED_MIME = [
    'application/pdf' => 'pdf',
    'application/epub+zip' => 'epub',
    'application/zip' => 'zip',
];
const ADMIN_EBOOK_MAX_BYTES = 30 * 1024 * 1024; // 30 MB

function admin_ebooks_storage_dir(): string
{
    $dir = dirname(__DIR__, 3) . '/storage/ebooks';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        api_error('No se pudo crear directorio de ebooks', 500, 'storage_error');
    }
    return $dir;
}

function admin_ebooks_load_forum_links(PDO $pdo, array $ebookIds): array
{
    if (empty($ebookIds)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ebookIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT forum_ebooks.ebook_id, forums.id AS forum_id, forums.code, forums.title
         FROM forum_ebooks
         INNER JOIN forums ON forums.id = forum_ebooks.forum_id
         WHERE forum_ebooks.ebook_id IN ($placeholders) AND forum_ebooks.is_active = 1
         ORDER BY forums.starts_at DESC, forums.id DESC"
    );
    $stmt->execute(array_values($ebookIds));
    $byEbook = [];
    foreach ($stmt->fetchAll() as $row) {
        $eid = (int)$row['ebook_id'];
        $byEbook[$eid][] = [
            'forum_id' => (int)$row['forum_id'],
            'code' => (string)$row['code'],
            'title' => (string)$row['title'],
        ];
    }
    return $byEbook;
}

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT id, title, description, status, provider, local_path, external_url,
                min_attendance, requires_approved, created_at, updated_at
         FROM ebooks
         ORDER BY created_at DESC, id DESC'
    )->fetchAll();

    $forumLinks = admin_ebooks_load_forum_links($pdo, array_map(static fn($r) => (int)$r['id'], $rows));

    foreach ($rows as &$row) {
        $row['id'] = (int)$row['id'];
        $row['min_attendance'] = (float)$row['min_attendance'];
        $row['requires_approved'] = (int)$row['requires_approved'] === 1;
        $row['forums'] = $forumLinks[(int)$row['id']] ?? [];
    }
    unset($row);

    api_json(['ok' => true, 'items' => $rows]);
}

if ($method === 'POST') {
    $input = api_read_json();
    $title = api_input_string($input, 'title', true);
    $description = api_input_string($input, 'description');
    $status = api_input_string($input, 'status') ?: 'published';
    $provider = api_input_string($input, 'provider') ?: 'local';
    $minAttendance = (float)($input['minAttendance'] ?? 75);
    $requiresApproved = !empty($input['requiresApproved']) ? 1 : 0;
    $forumIds = array_values(array_filter(array_map('intval', (array)($input['forumIds'] ?? []))));

    if (!in_array($provider, ['local', 'external'], true)) {
        api_error('Proveedor inválido (local | external)', 422, 'validation_error');
    }
    if (!in_array($status, ['draft', 'published', 'archived'], true)) {
        api_error('Estado inválido', 422, 'validation_error');
    }
    if ($minAttendance < 0 || $minAttendance > 100) {
        api_error('min_attendance debe estar entre 0 y 100', 422, 'validation_error');
    }

    $localPath = null;
    $externalUrl = null;

    if ($provider === 'local') {
        $fileBase64 = (string)($input['fileBase64'] ?? '');
        $fileMime = trim((string)($input['fileMime'] ?? ''));
        $fileName = trim((string)($input['fileName'] ?? ''));
        if ($fileBase64 === '' || $fileMime === '' || $fileName === '') {
            api_error('Para provider=local se requiere fileBase64, fileMime y fileName', 422, 'validation_error');
        }
        if (!isset(ADMIN_EBOOK_ALLOWED_MIME[$fileMime])) {
            api_error('Tipo de archivo no permitido (PDF/EPUB/ZIP)', 422, 'validation_error');
        }
        $binary = base64_decode($fileBase64, true);
        if ($binary === false) {
            api_error('Archivo base64 inválido', 422, 'validation_error');
        }
        if (strlen($binary) > ADMIN_EBOOK_MAX_BYTES) {
            api_error('Archivo excede 30 MB', 422, 'validation_error');
        }

        $extension = ADMIN_EBOOK_ALLOWED_MIME[$fileMime];
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', pathinfo($fileName, PATHINFO_FILENAME)) ?: 'ebook';
        $finalFilename = sprintf('%s_%s.%s', $safeName, bin2hex(random_bytes(4)), $extension);
        $fullPath = admin_ebooks_storage_dir() . '/' . $finalFilename;
        if (file_put_contents($fullPath, $binary) === false) {
            api_error('No se pudo guardar el archivo en storage', 500, 'storage_error');
        }
        $localPath = $finalFilename;
    } else {
        $externalUrl = api_input_string($input, 'externalUrl', true);
        if (!filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            api_error('URL externa inválida', 422, 'validation_error');
        }
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved, created_at, updated_at)
             VALUES (:title, :description, :status, :provider, :local_path, :external_url, :min_attendance, :requires_approved, :created_at, :updated_at)'
        );
        $now = gmdate('c');
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'provider' => $provider,
            'local_path' => $localPath,
            'external_url' => $externalUrl,
            'min_attendance' => $minAttendance,
            'requires_approved' => $requiresApproved,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $ebookId = (int)$pdo->lastInsertId();

        if (!empty($forumIds)) {
            $linkStmt = $pdo->prepare(
                'INSERT OR IGNORE INTO forum_ebooks (forum_id, ebook_id, is_active, created_at)
                 VALUES (:forum_id, :ebook_id, 1, :created_at)'
            );
            foreach ($forumIds as $fid) {
                $linkStmt->execute(['forum_id' => $fid, 'ebook_id' => $ebookId, 'created_at' => $now]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_error('Error al crear ebook: ' . $e->getMessage(), 500, 'database_error');
    }

    api_json(['ok' => true, 'id' => $ebookId, 'message' => 'Ebook creado']);
}

if ($method === 'PATCH') {
    $input = api_read_json();
    $id = (int)($input['id'] ?? 0);
    if ($id < 1) {
        api_error('id requerido', 422, 'validation_error');
    }

    $fields = [];
    $params = ['id' => $id];

    if (array_key_exists('title', $input)) {
        $fields[] = 'title = :title';
        $params['title'] = trim((string)$input['title']);
    }
    if (array_key_exists('description', $input)) {
        $fields[] = 'description = :description';
        $params['description'] = trim((string)$input['description']);
    }
    if (array_key_exists('status', $input)) {
        $status = (string)$input['status'];
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            api_error('Estado inválido', 422, 'validation_error');
        }
        $fields[] = 'status = :status';
        $params['status'] = $status;
    }
    if (array_key_exists('minAttendance', $input)) {
        $fields[] = 'min_attendance = :min_attendance';
        $params['min_attendance'] = (float)$input['minAttendance'];
    }
    if (array_key_exists('requiresApproved', $input)) {
        $fields[] = 'requires_approved = :requires_approved';
        $params['requires_approved'] = !empty($input['requiresApproved']) ? 1 : 0;
    }
    if (array_key_exists('externalUrl', $input)) {
        $fields[] = 'external_url = :external_url';
        $params['external_url'] = (string)$input['externalUrl'] ?: null;
    }

    if (empty($fields)) {
        api_json(['ok' => true, 'message' => 'Sin cambios']);
    }

    $fields[] = 'updated_at = :updated_at';
    $params['updated_at'] = gmdate('c');

    $sql = 'UPDATE ebooks SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    api_json(['ok' => true, 'message' => 'Ebook actualizado']);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id < 1) {
        api_error('id requerido', 422, 'validation_error');
    }

    $row = $pdo->prepare('SELECT local_path FROM ebooks WHERE id = :id LIMIT 1');
    $row->execute(['id' => $id]);
    $ebook = $row->fetch();

    $deleteStmt = $pdo->prepare('DELETE FROM ebooks WHERE id = :id');
    $deleteStmt->execute(['id' => $id]);

    if ($ebook && !empty($ebook['local_path'])) {
        $path = admin_ebooks_storage_dir() . '/' . basename((string)$ebook['local_path']);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    api_json(['ok' => true, 'message' => 'Ebook eliminado']);
}

api_error('Método no permitido', 405, 'method_not_allowed');
