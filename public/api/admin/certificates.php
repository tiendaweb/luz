<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/CertificateRenderer.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $forumId = (int)($_GET['forum'] ?? 0);
    $viewGenerated = isset($_GET['generated']) && $_GET['generated'] === '1';
    $requestedType = isset($_GET['type']) ? strtolower(trim((string)$_GET['type'])) : null;
    if ($requestedType !== null && $requestedType !== '' && !CertificateRenderer::isValidType($requestedType)) {
        api_json(['ok' => false, 'error' => 'Tipo de certificado inválido'], 422);
    }
    $type = CertificateRenderer::normalizeType($requestedType);

    if ($type === 'attendance') {
        // Eligibles para asistencia: cualquier inscripción aprobada por admin/asociado
        $sql = '
            SELECT DISTINCT
                users.id AS user_id,
                users.full_name,
                users.email,
                forums.id AS forum_id,
                forums.code AS forum_code,
                forums.title AS forum_title,
                user_certificates.id,
                user_certificates.type,
                user_certificates.created_at,
                CASE WHEN user_certificates.id IS NOT NULL THEN 1 ELSE 0 END AS has_certificate
            FROM registrations
            INNER JOIN users ON users.id = registrations.user_id
            INNER JOIN forums ON forums.id = registrations.forum_id
            INNER JOIN registration_admin_state ras ON ras.registration_id = registrations.id AND ras.status = "approved"
            LEFT JOIN user_certificates
              ON user_certificates.user_id = users.id
              AND user_certificates.forum_id = forums.id
              AND user_certificates.type = "attendance"
            WHERE 1=1
        ';
    } else {
        // Eligibles para conclusión: paid+validated O registration approved
        $sql = '
            SELECT DISTINCT
                users.id AS user_id,
                users.full_name,
                users.email,
                forums.id AS forum_id,
                forums.code AS forum_code,
                forums.title AS forum_title,
                user_certificates.id,
                user_certificates.type,
                user_certificates.created_at,
                CASE WHEN user_certificates.id IS NOT NULL THEN 1 ELSE 0 END AS has_certificate
            FROM registrations
            INNER JOIN users ON users.id = registrations.user_id
            INNER JOIN forums ON forums.id = registrations.forum_id
            LEFT JOIN user_admin_flags ON user_admin_flags.user_id = users.id
            LEFT JOIN user_certificates
              ON user_certificates.user_id = users.id
              AND user_certificates.forum_id = forums.id
              AND user_certificates.type = "completion"
            WHERE (
                (user_admin_flags.is_validated = 1 AND user_admin_flags.is_paid = 1)
                OR EXISTS (
                    SELECT 1 FROM registration_admin_state
                    WHERE registration_admin_state.registration_id = registrations.id
                    AND registration_admin_state.status = "approved"
                )
            )
        ';
    }

    if ($viewGenerated) {
        $sql .= ' AND user_certificates.id IS NOT NULL';
    }
    if ($forumId > 0) {
        $sql .= ' AND forums.id = :forum_id';
    }
    $sql .= ' ORDER BY forums.id DESC, users.full_name ASC';

    $stmt = $pdo->prepare($sql);
    $params = [];
    if ($forumId > 0) {
        $params['forum_id'] = $forumId;
    }
    $stmt->execute($params);

    $rows = $stmt->fetchAll();
    api_json(['ok' => true, 'items' => $rows, 'type' => $type]);
}

if ($method === 'POST') {
    $input = api_read_json();
    $userId = (int)($input['userId'] ?? 0);
    $forumId = (int)($input['forumId'] ?? 0);
    $requestedType = isset($input['type']) ? strtolower(trim((string)$input['type'])) : 'completion';
    if (!CertificateRenderer::isValidType($requestedType)) {
        api_json(['ok' => false, 'error' => 'Tipo de certificado inválido'], 422);
    }
    $type = $requestedType;

    if ($userId < 1 || $forumId < 1) {
        api_json(['ok' => false, 'error' => 'Usuario o foro inválido'], 422);
    }

    $eligible = false;
    if ($type === 'attendance') {
        // Cualquier inscripción aprobada
        $checkStmt = $pdo->prepare('
            SELECT 1
            FROM registrations
            INNER JOIN registration_admin_state ras ON ras.registration_id = registrations.id
            WHERE registrations.user_id = :user_id
              AND registrations.forum_id = :forum_id
              AND ras.status = "approved"
            LIMIT 1
        ');
        $checkStmt->execute(['user_id' => $userId, 'forum_id' => $forumId]);
        $eligible = (bool)$checkStmt->fetch();
    } else {
        $checkStmt = $pdo->prepare('
            SELECT 1
            FROM users
            INNER JOIN forums ON forums.id = :forum_id
            LEFT JOIN user_admin_flags ON user_admin_flags.user_id = users.id
            LEFT JOIN registrations r ON r.user_id = users.id AND r.forum_id = forums.id
            LEFT JOIN registration_admin_state ras ON ras.registration_id = r.id
            WHERE users.id = :user_id
            AND (
                (user_admin_flags.is_validated = 1 AND user_admin_flags.is_paid = 1)
                OR ras.status = "approved"
            )
            LIMIT 1
        ');
        $checkStmt->execute(['user_id' => $userId, 'forum_id' => $forumId]);
        $eligible = (bool)$checkStmt->fetch();
    }

    if (!$eligible) {
        api_json(['ok' => false, 'error' => 'Usuario no cumple requisitos para certificado de ' . $type], 403);
    }

    $existStmt = $pdo->prepare('
        SELECT id FROM user_certificates
        WHERE user_id = :user_id AND forum_id = :forum_id AND type = :type
        LIMIT 1
    ');
    $existStmt->execute(['user_id' => $userId, 'forum_id' => $forumId, 'type' => $type]);
    $existing = $existStmt->fetch();

    if ($existing) {
        api_json([
            'ok' => true,
            'message' => 'Certificado ya existe',
            'certificateId' => $existing['id'],
            'type' => $type,
        ]);
        exit;
    }

    $insertStmt = $pdo->prepare('
        INSERT INTO user_certificates (user_id, forum_id, type, created_at, created_by_user_id)
        VALUES (:user_id, :forum_id, :type, :created_at, :created_by_user_id)
    ');
    $insertStmt->execute([
        'user_id' => $userId,
        'forum_id' => $forumId,
        'type' => $type,
        'created_at' => gmdate('c'),
        'created_by_user_id' => (int)($user['id'] ?? 0),
    ]);

    api_json([
        'ok' => true,
        'certificateId' => $pdo->lastInsertId(),
        'type' => $type,
        'message' => 'Certificado generado exitosamente',
    ]);
}

api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
