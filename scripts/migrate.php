<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Database/connection.php';
require_once __DIR__ . '/../app/Database/DemoDataInitializer.php';

try {
    $pdo = app_db_connection();
    $result = app_db_run_migrations($pdo);
    app_initialize_demo_data($pdo);
    $status = app_db_schema_status($pdo);
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf("[migrate] ERROR: %s\n", $exception->getMessage()));
    exit(1);
}

fwrite(STDOUT, sprintf("[migrate] Applied: %d\n", count($result['applied'])));
fwrite(STDOUT, sprintf("[migrate] Skipped (already compatible): %d\n", count($result['skipped'])));
fwrite(STDOUT, sprintf("[migrate] Pending: %d\n", count($status['pending_migrations'])));

if (($status['ok'] ?? false) !== true) {
    fwrite(STDERR, "[migrate] Schema is still outdated.\n");
    foreach ($status['pending_migrations'] as $migration) {
        fwrite(STDERR, sprintf(" - %s\n", $migration));
    }
    exit(2);
}

fwrite(STDOUT, "[migrate] Schema ready.\n");
