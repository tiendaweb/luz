<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../app/Services/AuthService.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$input = api_read_json();
$role = (string)($input['role'] ?? 'guest');

try {
    $authService = new AuthService();
    $user = $authService->loginAsRole($role);
} catch (InvalidArgumentException $error) {
    api_json(['ok' => false, 'error' => $error->getMessage()], 422);
}

$_SESSION['auth_user'] = $user;
api_audit(null, 'login:' . $role);

api_json(['ok' => true, 'user' => $user]);
