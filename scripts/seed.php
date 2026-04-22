<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Database/connection.php';

$pdo = app_db_connection();

$runSqlMigrations = static function (PDO $pdo): void {
    $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql') ?: [];
    sort($migrationFiles, SORT_NATURAL);

    foreach ($migrationFiles as $file) {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException(sprintf('No se pudo leer la migración: %s', $file));
        }

        $pdo->exec($sql);
        fwrite(STDOUT, sprintf("[migrate] %s\n", basename($file)));
    }
};

$runPhpSeeds = static function (PDO $pdo): void {
    $seedFiles = glob(__DIR__ . '/../database/seeds/*.php') ?: [];
    sort($seedFiles, SORT_NATURAL);

    foreach ($seedFiles as $file) {
        $seedRunner = require $file;
        if (!is_callable($seedRunner)) {
            throw new RuntimeException(sprintf('El seed no retorna una función válida: %s', $file));
        }

        $seedRunner($pdo);
        fwrite(STDOUT, sprintf("[seed] %s\n", basename($file)));
    }
};

try {
    $pdo->beginTransaction();
    $runSqlMigrations($pdo);
    $runPhpSeeds($pdo);
    $pdo->commit();

    fwrite(STDOUT, "Proceso completo: migraciones + seeds ejecutados.\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, sprintf("Error ejecutando seeds: %s\n", $e->getMessage()));
    exit(1);
}
