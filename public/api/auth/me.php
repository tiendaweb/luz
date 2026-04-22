<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$user = api_current_user();
if (!$user) {
    api_json(['ok' => true, 'authenticated' => false, 'user' => ['role' => 'guest']]);
}

api_json(['ok' => true, 'authenticated' => true, 'user' => $user]);
