<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['GET']);

$user = api_current_user();
if (!$user) {
    api_json([
        'ok' => true,
        'authenticated' => false,
        'user' => ['role' => 'guest'],
        'csrfToken' => api_csrf_token(),
    ]);
}

api_json([
    'ok' => true,
    'authenticated' => true,
    'user' => $user,
    'csrfToken' => api_csrf_token(),
    'sessionExpiresAt' => (int)($_SESSION['session_expires_at'] ?? 0),
]);
