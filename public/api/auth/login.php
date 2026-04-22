<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$role = (string)($input['role'] ?? 'guest');
$allowedRoles = ['guest', 'user', 'associate', 'admin'];

if (!in_array($role, $allowedRoles, true)) {
    api_json(['ok' => false, 'error' => 'Rol inválido'], 422);
}

$user = [
    'id' => null,
    'name' => 'Invitado',
    'role' => $role,
];

if ($role === 'admin') {
    $user['name'] = 'Luz Genovese';
} elseif ($role === 'associate') {
    $user['name'] = 'Coordinador Red';
} elseif ($role === 'user') {
    $user['name'] = 'Inscripto Foro';
}

$_SESSION['auth_user'] = $user;
api_audit(null, 'login:' . $role);

api_json(['ok' => true, 'user' => $user]);
