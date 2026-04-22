<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Database/connection.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function api_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_read_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function api_current_user(): ?array
{
    if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
        return $_SESSION['auth_user'];
    }

    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? null;
    if (!is_int($userId) || !is_string($role) || $role === '') {
        return null;
    }

    return [
        'id' => $userId,
        'role' => $role,
    ];
}

function api_set_current_user(array $user): void
{
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = (string)$user['role'];
    $_SESSION['auth_user'] = $user;
}

function api_require_db(): PDO
{
    $pdo = app_db_connection();
    static $migrated = false;

    if (!$migrated) {
        $migrationSql = file_get_contents(__DIR__ . '/../../database/migrations/001_init.sql');
        if ($migrationSql !== false) {
            $pdo->exec($migrationSql);
        }
        $migrated = true;
    }

    return $pdo;
}

function api_audit(?int $userId, string $event): void
{
    $pdo = api_require_db();
    $stmt = $pdo->prepare('INSERT INTO sessions_audit (user_id, event, ip_address, user_agent) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $userId,
        $event,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}
