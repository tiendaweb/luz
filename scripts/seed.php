<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Database/connection.php';
require_once __DIR__ . '/../app/Database/DemoDataInitializer.php';

$pdo = app_db_connection();

$runSqlMigrations = static function (PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (filename TEXT PRIMARY KEY, executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');

    $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql') ?: [];
    sort($migrationFiles, SORT_NATURAL);

    $checkStmt = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE filename = :filename LIMIT 1');
    $insertStmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');

    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        $checkStmt->execute(['filename' => $filename]);
        $alreadyExecuted = $checkStmt->fetchColumn() !== false;
        if ($alreadyExecuted) {
            continue;
        }

        if ($filename === '002_forum_detail.sql') {
            $forumColumns = $pdo->query('PRAGMA table_info(forums)')->fetchAll(PDO::FETCH_ASSOC);
            $forumColumnNames = array_map(static fn(array $column): string => (string)$column['name'], $forumColumns);
            if (in_array('objective', $forumColumnNames, true)) {
                $insertStmt->execute(['filename' => $filename]);
                fwrite(STDOUT, sprintf("[migrate] %s (existing)\n", $filename));
                continue;
            }
        }

        if ($filename === '003_registration_state_audit.sql') {
            $stateColumns = $pdo->query('PRAGMA table_info(registration_admin_state)')->fetchAll(PDO::FETCH_ASSOC);
            $stateColumnNames = array_map(static fn(array $column): string => (string)$column['name'], $stateColumns);
            if (in_array('updated_by_user_id', $stateColumnNames, true) && in_array('updated_by_role', $stateColumnNames, true)) {
                $insertStmt->execute(['filename' => $filename]);
                fwrite(STDOUT, sprintf("[migrate] %s (existing)\n", $filename));
                continue;
            }
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException(sprintf('No se pudo leer la migración: %s', $file));
        }

        $pdo->exec($sql);
        $insertStmt->execute(['filename' => $filename]);
        fwrite(STDOUT, sprintf("[migrate] %s\n", $filename));
    }
};

try {
    $pdo->beginTransaction();
    $runSqlMigrations($pdo);
    app_initialize_demo_data($pdo);
    $pdo->commit();

    fwrite(STDOUT, "Proceso completo: migraciones + auto-init demo ejecutados.\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, sprintf("Error ejecutando inicialización: %s\n", $e->getMessage()));
    exit(1);
}
