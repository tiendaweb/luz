<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/AuthService.php';

api_require_method(['POST']);

$input = api_read_json();
$usernameOrEmail = trim((string)($input['email'] ?? $input['username'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($usernameOrEmail === '' || $password === '') {
    api_error('Email/usuario y contraseña son obligatorios', 422, 'validation_error');
}

$pdo = api_require_db();
$stmt = $pdo->prepare(
    'SELECT users.id, users.full_name, users.email, users.password_hash, roles.slug AS role
     FROM users
     INNER JOIN roles ON roles.id = users.role_id
     WHERE users.email = :email
     LIMIT 1'
);
$stmt->execute(['email' => $usernameOrEmail]);
$row = $stmt->fetch();

if (!is_array($row) || !password_verify($password, (string)$row['password_hash'])) {
    api_error('Credenciales inválidas', 401, 'invalid_credentials');
}

$user = [
    'id' => (int)$row['id'],
    'name' => (string)$row['full_name'],
    'email' => (string)$row['email'],
    'role' => (string)$row['role'],
];

api_rotate_session_after_login();
api_set_current_user($user);
api_audit($user['id'], 'login:' . $user['role']);

api_json(['ok' => true, 'user' => $user, 'csrfToken' => api_csrf_token(), 'sessionExpiresAt' => (int)($_SESSION['session_expires_at'] ?? 0)]);
