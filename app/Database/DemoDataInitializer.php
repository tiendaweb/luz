<?php

declare(strict_types=1);

require_once __DIR__ . '/connection.php';

/**
 * @return list<string>
 */
function app_seed_files(): array
{
    $seedFiles = glob(__DIR__ . '/../../database/seeds/*.php') ?: [];
    sort($seedFiles, SORT_NATURAL);

    return array_values($seedFiles);
}

function app_run_demo_seed_files(PDO $pdo): void
{
    foreach (app_seed_files() as $file) {
        $seedRunner = require $file;
        if (!is_callable($seedRunner)) {
            throw new RuntimeException(sprintf('El seed no retorna una función válida: %s', basename($file)));
        }

        $seedRunner($pdo);
    }
}

/**
 * @return array{missing_roles:list<string>, published_forums:int, registrations:int, messages:int}
 */
function app_demo_data_health(PDO $pdo): array
{
    $requiredRoles = ['admin', 'associate', 'user'];
    $missingRoles = [];

    $roleCountStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE roles.slug = :role_slug"
    );

    foreach ($requiredRoles as $roleSlug) {
        $roleCountStmt->execute(['role_slug' => $roleSlug]);
        $count = (int)$roleCountStmt->fetchColumn();
        if ($count < 1) {
            $missingRoles[] = $roleSlug;
        }
    }

    $publishedForums = (int)$pdo->query("SELECT COUNT(*) FROM forums WHERE status = 'published'")->fetchColumn();
    $registrations = (int)$pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
    $messages = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

    return [
        'missing_roles' => $missingRoles,
        'published_forums' => $publishedForums,
        'registrations' => $registrations,
        'messages' => $messages,
    ];
}

function app_demo_data_is_valid(array $health): bool
{
    return $health['missing_roles'] === []
        && $health['published_forums'] >= 1
        && $health['registrations'] >= 1
        && $health['messages'] >= 1;
}

function app_initialize_demo_data(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS seed_runs (seed_key TEXT PRIMARY KEY, executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');

    $isBaseEmpty = ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()) === 0
        && ((int)$pdo->query('SELECT COUNT(*) FROM forums')->fetchColumn()) === 0;

    $shouldRunSeed = $isBaseEmpty;
    $health = app_demo_data_health($pdo);

    if (!$shouldRunSeed && !app_demo_data_is_valid($health)) {
        $shouldRunSeed = true;
    }

    if (!$shouldRunSeed) {
        return;
    }

    $startedTransaction = false;
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
        $startedTransaction = true;
    }

    try {
        app_run_demo_seed_files($pdo);

        $healthAfterSeed = app_demo_data_health($pdo);
        if (!app_demo_data_is_valid($healthAfterSeed)) {
            throw new RuntimeException('Inicialización incompleta: faltan datos críticos demo (roles, foros publicados o datos de dashboard).');
        }

        $seedKey = $isBaseEmpty ? 'first_run_auto_init_v1' : 'critical_data_repair_v1';
        $seedInsert = $pdo->prepare('INSERT OR REPLACE INTO seed_runs (seed_key, executed_at) VALUES (:seed_key, CURRENT_TIMESTAMP)');
        $seedInsert->execute(['seed_key' => $seedKey]);

        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}
