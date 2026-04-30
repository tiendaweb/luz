<?php

declare(strict_types=1);

function app_db_path(): string
{
    return dirname(__DIR__, 2) . '/data/app.sqlite';
}

/** @return array{ok: bool, error?: string, data_dir?: string, db_path?: string} */
function app_db_prepare_storage(): array
{
    $dbPath = app_db_path();
    $dataDir = dirname($dbPath);

    if (!is_dir($dataDir) && !@mkdir($dataDir, 0755, true) && !is_dir($dataDir)) {
        return [
            'ok' => false,
            'error' => sprintf('No se pudo crear el directorio de datos: %s', $dataDir),
            'data_dir' => $dataDir,
            'db_path' => $dbPath,
        ];
    }

    if (!is_writable($dataDir)) {
        return [
            'ok' => false,
            'error' => sprintf('El directorio de datos no es escribible: %s', $dataDir),
            'data_dir' => $dataDir,
            'db_path' => $dbPath,
        ];
    }

    if (file_exists($dbPath) && !is_writable($dbPath)) {
        return [
            'ok' => false,
            'error' => sprintf('El archivo de base de datos no es escribible: %s', $dbPath),
            'data_dir' => $dataDir,
            'db_path' => $dbPath,
        ];
    }

    if (!file_exists($dbPath) && @touch($dbPath) === false) {
        return [
            'ok' => false,
            'error' => sprintf('No se pudo crear el archivo de base de datos: %s', $dbPath),
            'data_dir' => $dataDir,
            'db_path' => $dbPath,
        ];
    }

    return [
        'ok' => true,
        'data_dir' => $dataDir,
        'db_path' => $dbPath,
    ];
}

function app_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $storage = app_db_prepare_storage();
    if (($storage['ok'] ?? false) !== true) {
        throw new RuntimeException((string)($storage['error'] ?? 'No se pudo preparar el almacenamiento de la base de datos.'));
    }

    $dsn = 'sqlite:' . $storage['db_path'];
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pdo->exec('PRAGMA foreign_keys = ON;');

    return $pdo;
}

/** @return list<string> */
function app_db_migration_files(): array
{
    $migrationFiles = glob(dirname(__DIR__, 2) . '/database/migrations/*.sql') ?: [];
    sort($migrationFiles, SORT_NATURAL);
    return $migrationFiles;
}

function app_db_ensure_schema_table(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (filename TEXT PRIMARY KEY, executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
}

/** @return array{ok: bool, applied_migrations: list<string>, pending_migrations: list<string>} */
function app_db_schema_status(PDO $pdo): array
{
    app_db_ensure_schema_table($pdo);

    $rows = $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $appliedMigrations = array_values(array_map(static fn(mixed $row): string => (string)$row, is_array($rows) ? $rows : []));

    $pendingMigrations = [];
    foreach (app_db_migration_files() as $file) {
        $filename = basename($file);
        if (!in_array($filename, $appliedMigrations, true)) {
            $pendingMigrations[] = $filename;
        }
    }

    return [
        'ok' => $pendingMigrations === [],
        'applied_migrations' => $appliedMigrations,
        'pending_migrations' => $pendingMigrations,
    ];
}

/** @return array{applied: list<string>, skipped: list<string>} */
function app_db_run_migrations(PDO $pdo): array
{
    app_db_ensure_schema_table($pdo);

    $checkStmt = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE filename = :filename LIMIT 1');
    $insertStmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');

    $applied = [];
    $skipped = [];

    foreach (app_db_migration_files() as $file) {
        $filename = basename($file);
        $checkStmt->execute(['filename' => $filename]);
        if ($checkStmt->fetchColumn() !== false) {
            continue;
        }

        if ($filename === '002_forum_detail.sql') {
            $forumColumns = $pdo->query('PRAGMA table_info(forums)')->fetchAll(PDO::FETCH_ASSOC);
            $forumColumnNames = array_map(static fn(array $column): string => (string)$column['name'], $forumColumns);
            if (in_array('objective', $forumColumnNames, true)) {
                $insertStmt->execute(['filename' => $filename]);
                $skipped[] = $filename;
                continue;
            }
        }

        if ($filename === '003_registration_state_audit.sql') {
            $stateColumns = $pdo->query('PRAGMA table_info(registration_admin_state)')->fetchAll(PDO::FETCH_ASSOC);
            $stateColumnNames = array_map(static fn(array $column): string => (string)$column['name'], $stateColumns);
            if (in_array('updated_by_user_id', $stateColumnNames, true) && in_array('updated_by_role', $stateColumnNames, true)) {
                $insertStmt->execute(['filename' => $filename]);
                $skipped[] = $filename;
                continue;
            }
        }

        $migrationSql = file_get_contents($file);
        if ($migrationSql === false) {
            throw new RuntimeException(sprintf('No se pudo leer la migración: %s', $filename));
        }

        $pdo->exec($migrationSql);
        $insertStmt->execute(['filename' => $filename]);
        $applied[] = $filename;
    }

    return ['applied' => $applied, 'skipped' => $skipped];
}
