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
