<?php

declare(strict_types=1);

require_once __DIR__ . '/../ebooks/_common.php';

function admin_forum_ebooks_require_admin(): array
{
    return admin_ebooks_require_admin();
}
