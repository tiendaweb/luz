<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

api_require_method(['POST']);

$currentUser = api_current_user();
api_audit($currentUser['id'] ?? null, 'logout');

api_clear_session();

api_json(['ok' => true]);
