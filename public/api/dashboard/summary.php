<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();

$registrationsTotal = (int)$pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$certRequestsTotal = (int)$pdo->query('SELECT COUNT(*) FROM registrations WHERE needs_cert = 1')->fetchColumn();
$usersTotal = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$messagesTotal = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

api_json([
    'ok' => true,
    'summary' => [
        'registrations_total' => $registrationsTotal,
        'cert_requests_total' => $certRequestsTotal,
        'users_total' => $usersTotal,
        'messages_total' => $messagesTotal,
    ],
]);
