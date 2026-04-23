<?php

declare(strict_types=1);

require_once __DIR__ . '/layouts/redirect.php';

$target = $target ?? '/index.php';
$message = $message ?? 'Redireccionando a la vista interna…';
$title = $title ?? 'Redireccionando…';

render_redirect_layout([
    'title' => $title,
    'target' => $target,
    'message' => $message,
]);
