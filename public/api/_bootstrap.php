<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Database/connection.php';
require_once __DIR__ . '/../../app/Database/DemoDataInitializer.php';

const API_CSRF_HEADER = 'HTTP_X_CSRF_TOKEN';
const API_SESSION_TTL_ENV = 'SESSION_TTL_SECONDS';
const API_DEFAULT_SESSION_TTL = 7200;

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

function api_error(string $message, int $status = 400, string $code = 'api_error', array $details = []): never
{
    api_json([
        'ok' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
            'details' => $details,
        ],
    ], $status);
}

function api_error_message(array $payload): string
{
    $error = $payload['error'] ?? null;
    if (is_array($error)) {
        return (string)($error['message'] ?? 'Error de API.');
    }

    return is_string($error) && $error !== '' ? $error : 'Error de API.';
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

/** @param list<string> $methods */
function api_require_method(array $methods): string
{
    $requestMethod = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $allowed = array_map('strtoupper', $methods);
    if (!in_array($requestMethod, $allowed, true)) {
        api_error('Método no permitido', 405, 'method_not_allowed', ['allowed' => $allowed]);
    }

    return $requestMethod;
}

function api_input_string(array $input, string $key, bool $required = false): string
{
    $value = trim((string)($input[$key] ?? ''));
    if ($required && $value === '') {
        api_error(sprintf('El campo "%s" es obligatorio.', $key), 422, 'validation_error', ['field' => $key]);
    }

    return $value;
}

/** @param list<string> $fields */
function api_require_fields(array $input, array $fields): void
{
    $missing = [];
    foreach ($fields as $field) {
        if (!array_key_exists($field, $input) || trim((string)$input[$field]) === '') {
            $missing[] = $field;
        }
    }

    if ($missing !== []) {
        api_error('Faltan campos obligatorios.', 422, 'validation_error', ['missing' => $missing]);
    }
}

function api_validate_slug(string $slug): bool
{
    return (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
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

function api_require_role(string $role): array
{
    $user = api_current_user();
    if (!is_array($user) || ($user['role'] ?? '') !== $role) {
        api_error('Acceso denegado', 403, 'forbidden');
    }

    return $user;
}

function api_clear_session(): void
{
    $_SESSION = [];
    unset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['auth_user'], $_SESSION['session_expires_at'], $_SESSION['csrf_token']);
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}

function api_session_ttl_seconds(): int
{
    $configured = getenv(API_SESSION_TTL_ENV);
    if (!is_string($configured) || $configured === '') {
        return API_DEFAULT_SESSION_TTL;
    }

    $ttl = (int)$configured;
    return $ttl > 60 ? $ttl : API_DEFAULT_SESSION_TTL;
}

function api_touch_session_expiry(): int
{
    $expiresAt = time() + api_session_ttl_seconds();
    $_SESSION['session_expires_at'] = $expiresAt;
    return $expiresAt;
}

function api_is_session_expired(): bool
{
    $expiresAt = (int)($_SESSION['session_expires_at'] ?? 0);
    return $expiresAt > 0 && $expiresAt < time();
}

function api_csrf_token(): string
{
    $token = $_SESSION['csrf_token'] ?? null;
    if (!is_string($token) || strlen($token) < 32) {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
    }

    return $token;
}

function api_rotate_session_after_login(): void
{
    session_regenerate_id(true);
    api_touch_session_expiry();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function api_ensure_active_session(): void
{
    $user = api_current_user();
    if (!is_array($user)) {
        api_csrf_token();
        return;
    }

    if (api_is_session_expired()) {
        api_audit((int)($user['id'] ?? 0) ?: null, 'session_expired');
        api_clear_session();
        api_error('La sesión expiró. Iniciá sesión nuevamente.', 401, 'session_expired');
    }

    api_touch_session_expiry();
    api_csrf_token();
}

function api_requires_csrf_check(): bool
{
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        return false;
    }

    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $requestPath = trim(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '', '/');
    $normalizedRequestPath = str_starts_with($requestPath, 'public/') ? substr($requestPath, 7) : $requestPath;
    if (str_ends_with($normalizedRequestPath, '.php')) {
        $normalizedRequestPath = substr($normalizedRequestPath, 0, -4);
    }

    if ($normalizedRequestPath === 'api/auth/login') {
        return false;
    }

    return !str_ends_with($script, '/auth/login.php');
}

function api_enforce_csrf(): void
{
    if (!api_requires_csrf_check()) {
        return;
    }

    $provided = trim((string)($_SERVER[API_CSRF_HEADER] ?? ''));
    $expected = api_csrf_token();
    if ($provided === '' || !hash_equals($expected, $provided)) {
        api_error('Token CSRF inválido o ausente.', 419, 'csrf_invalid');
    }
}

function api_set_current_user(array $user): void
{
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = (string)$user['role'];
    $_SESSION['auth_user'] = $user;
    api_touch_session_expiry();
}

function api_require_db(): PDO
{
    $pdo = app_db_connection();
    static $migrated = false;

    if (!$migrated) {
        $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (filename TEXT PRIMARY KEY, executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');

        $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.sql') ?: [];
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
                    continue;
                }
            }

            if ($filename === '003_registration_state_audit.sql') {
                $stateColumns = $pdo->query('PRAGMA table_info(registration_admin_state)')->fetchAll(PDO::FETCH_ASSOC);
                $stateColumnNames = array_map(static fn(array $column): string => (string)$column['name'], $stateColumns);
                if (in_array('updated_by_user_id', $stateColumnNames, true) && in_array('updated_by_role', $stateColumnNames, true)) {
                    $insertStmt->execute(['filename' => $filename]);
                    continue;
                }
            }

            $migrationSql = file_get_contents($file);
            if ($migrationSql === false) {
                throw new RuntimeException(sprintf('No se pudo leer la migración: %s', $filename));
            }

            $pdo->exec($migrationSql);
            $insertStmt->execute(['filename' => $filename]);
        }

        app_initialize_demo_data($pdo);

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

api_ensure_active_session();
api_enforce_csrf();
