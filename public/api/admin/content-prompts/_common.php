<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

function admin_content_prompts_require_admin(): array
{
    $user = api_current_user();
    if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
        api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
    }

    return $user;
}
