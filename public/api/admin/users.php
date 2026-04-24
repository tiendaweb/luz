<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

$pdo = api_require_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT users.id,
                users.full_name,
                users.email,
                roles.slug AS role,
                user_admin_flags.is_validated,
                user_admin_flags.is_paid,
                user_admin_flags.updated_at,
                user_admin_flags.updated_by_user_id,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM registrations
                        LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
                        WHERE registrations.user_id = users.id
                          AND COALESCE(registration_admin_state.status, "pending") = "approved"
                    ) THEN 1 ELSE 0
                END AS legacy_is_validated,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM registrations
                        LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
                        WHERE registrations.user_id = users.id
                          AND COALESCE(registration_admin_state.status, "pending") IN ("payment_submitted", "approved")
                    ) THEN 1 ELSE 0
                END AS legacy_is_paid
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         LEFT JOIN user_admin_flags ON user_admin_flags.user_id = users.id
         ORDER BY users.id DESC'
    )->fetchAll();

    foreach ($rows as &$row) {
        $hasExplicitFlags = $row['is_validated'] !== null || $row['is_paid'] !== null;
        $explicitValidated = (int)($row['is_validated'] ?? 0) === 1;
        $explicitPaid = (int)($row['is_paid'] ?? 0) === 1;
        $legacyValidated = (int)($row['legacy_is_validated'] ?? 0) === 1;
        $legacyPaid = (int)($row['legacy_is_paid'] ?? 0) === 1;

        $row['has_explicit_flags'] = $hasExplicitFlags;
        $row['is_validated'] = $hasExplicitFlags ? $explicitValidated : $legacyValidated;
        $row['is_paid'] = $hasExplicitFlags ? $explicitPaid : $legacyPaid;
        $row['legacy_is_validated'] = $legacyValidated;
        $row['legacy_is_paid'] = $legacyPaid;
    }
    unset($row);

    api_json(['ok' => true, 'items' => $rows]);
}

if ($method === 'PATCH') {
    $input = api_read_json();
    $targetUserId = (int)($input['userId'] ?? 0);

    if ($targetUserId < 1) {
        api_json(['ok' => false, 'error' => 'Usuario inválido.'], 422);
    }

    if (!array_key_exists('isValidated', $input) || !array_key_exists('isPaid', $input)) {
        api_json(['ok' => false, 'error' => 'Faltan flags obligatorios.'], 422);
    }

    $isValidated = filter_var($input['isValidated'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $isPaid = filter_var($input['isPaid'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    if ($isValidated === null || $isPaid === null) {
        api_json(['ok' => false, 'error' => 'Formato de flags inválido.'], 422);
    }

    $existsStmt = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
    $existsStmt->execute(['id' => $targetUserId]);
    if ($existsStmt->fetchColumn() === false) {
        api_json(['ok' => false, 'error' => 'Usuario inexistente.'], 404);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO user_admin_flags (user_id, is_validated, is_paid, updated_at, updated_by_user_id)
         VALUES (:user_id, :is_validated, :is_paid, :updated_at, :updated_by_user_id)
         ON CONFLICT(user_id) DO UPDATE SET
           is_validated = excluded.is_validated,
           is_paid = excluded.is_paid,
           updated_at = excluded.updated_at,
           updated_by_user_id = excluded.updated_by_user_id'
    );
    $stmt->execute([
        'user_id' => $targetUserId,
        'is_validated' => $isValidated ? 1 : 0,
        'is_paid' => $isPaid ? 1 : 0,
        'updated_at' => gmdate('c'),
        'updated_by_user_id' => (int)($user['id'] ?? 0),
    ]);

    api_json(['ok' => true]);
}

api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
