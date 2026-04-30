<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['POST']);

$input = api_read_json();
$email = trim((string)($input['email'] ?? ''));
$password = (string)($input['password'] ?? '');
$full_name = trim((string)($input['full_name'] ?? ''));
$document_id = trim((string)($input['document_id'] ?? ''));

if ($email === '' || $password === '' || $full_name === '') {
    api_error('Email, contraseña y nombre son obligatorios', 422, 'validation_error');
}

if (strlen($password) < 6) {
    api_error('La contraseña debe tener al menos 6 caracteres', 422, 'validation_error');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_error('Email inválido', 422, 'validation_error');
}

$pdo = api_require_db();

// Verificar si el email ya existe
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    api_error('El email ya está registrado', 409, 'email_exists');
}

// Obtener role ID para usuario normal
$stmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
$stmt->execute(['slug' => 'user']);
$roleRow = $stmt->fetch();
if (!is_array($roleRow)) {
    api_error('Rol de usuario no configurado en el sistema', 500, 'system_error');
}
$roleId = (int)$roleRow['id'];

// Hash de contraseña
$hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$passwordHash = password_hash($password, $hashAlgo);

try {
    $pdo->beginTransaction();

    // Insertar nuevo usuario
    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, document_id, role_id, password_hash, created_at, updated_at)
         VALUES (:full_name, :email, :document_id, :role_id, :password_hash, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
    );
    $stmt->execute([
        'full_name' => $full_name,
        'email' => $email,
        'document_id' => $document_id ?: null,
        'role_id' => $roleId,
        'password_hash' => $passwordHash,
    ]);

    $userId = (int)$pdo->lastInsertId();

    $pdo->commit();

    // Iniciar sesión automáticamente
    api_rotate_session_after_login();
    api_set_current_user([
        'id' => $userId,
        'name' => $full_name,
        'email' => $email,
        'role' => 'user',
    ]);
    api_audit($userId, 'registration:user');

    api_json([
        'ok' => true,
        'user' => [
            'id' => $userId,
            'name' => $full_name,
            'email' => $email,
            'role' => 'user',
        ],
        'csrfToken' => api_csrf_token(),
        'sessionExpiresAt' => (int)($_SESSION['session_expires_at'] ?? 0),
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log(sprintf('[api/auth/register] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    api_error('Error interno del servidor', 500, 'database_error');
}
