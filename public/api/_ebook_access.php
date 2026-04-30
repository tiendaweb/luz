<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

function api_ebook_token_secret(): string
{
    $env = getenv('APP_EBOOK_TOKEN_SECRET');
    if (is_string($env) && trim($env) !== '') {
        return trim($env);
    }

    return 'luz-ebooks-secret-change-me';
}

function api_ebook_sign_token(int $userId, int $ebookId, int $forumId, int $expiresAt): string
{
    $payload = $userId . ':' . $ebookId . ':' . $forumId . ':' . $expiresAt;
    return hash_hmac('sha256', $payload, api_ebook_token_secret());
}

function api_ebook_log_access(PDO $pdo, ?int $userId, ?int $forumId, ?int $ebookId, bool $granted, string $reason, string $event = 'access_check'): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO ebook_download_audit (user_id, forum_id, ebook_id, event, access_granted, reason, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $forumId,
        $ebookId,
        $event,
        $granted ? 1 : 0,
        $reason,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}

function api_user_has_premium(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare(
        'SELECT roles.slug
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE users.id = :user_id
         LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $slug = strtolower(trim((string)$stmt->fetchColumn()));

    return in_array($slug, ['premium', 'vip', 'pro'], true);
}

function api_user_ebook_permission(PDO $pdo, int $userId, array $ebook, int $forumId): array
{
    $ebookId = (int)($ebook['id'] ?? 0);
    $minAttendance = (float)($ebook['min_attendance'] ?? 75);

    if (api_user_has_premium($pdo, $userId)) {
        return [
            'has_access' => true,
            'reason' => 'Acceso habilitado por estado premium.',
            'via' => 'premium',
            'attendance_percent' => 0.0,
            'attendance_threshold' => $minAttendance,
        ];
    }

    $manualStmt = $pdo->prepare(
        'SELECT access_granted, reason, expires_at
         FROM user_ebook_access
         WHERE user_id = :user_id AND ebook_id = :ebook_id
         LIMIT 1'
    );
    $manualStmt->execute([
        'user_id' => $userId,
        'ebook_id' => $ebookId,
    ]);
    $manual = $manualStmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($manual)) {
        $expiresAtRaw = trim((string)($manual['expires_at'] ?? ''));
        $expiresAt = $expiresAtRaw !== '' ? strtotime($expiresAtRaw) : false;
        $isExpired = $expiresAt !== false && $expiresAt < time();
        if (!$isExpired) {
            $granted = (int)$manual['access_granted'] === 1;
            return [
                'has_access' => $granted,
                'reason' => $granted ? 'Acceso otorgado manualmente.' : 'Acceso restringido manualmente.',
                'via' => 'manual',
                'attendance_percent' => 0.0,
                'attendance_threshold' => $minAttendance,
            ];
        }
    }

    $registrationStmt = $pdo->prepare(
        'SELECT registrations.id
         FROM registrations
         WHERE registrations.user_id = :user_id
           AND registrations.forum_id = :forum_id'
    );
    $registrationStmt->execute([
        'user_id' => $userId,
        'forum_id' => $forumId,
    ]);
    $registrationIds = $registrationStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    if ($registrationIds === []) {
        return [
            'has_access' => false,
            'reason' => 'Sin acceso: no estás inscrito/a en el foro de este material.',
            'via' => 'none',
            'attendance_percent' => 0.0,
            'attendance_threshold' => $minAttendance,
        ];
    }

    $placeholders = implode(',', array_fill(0, count($registrationIds), '?'));
    $attendanceStmt = $pdo->prepare(
        "SELECT MAX(COALESCE(attendance_percent, 0))
         FROM registration_attendance
         WHERE registration_id IN ($placeholders)"
    );
    $attendanceStmt->execute($registrationIds);
    $attendanceMax = (float)$attendanceStmt->fetchColumn();

    $attendanceEligible = $attendanceMax >= $minAttendance;
    if ($attendanceEligible) {
        return [
            'has_access' => true,
            'reason' => sprintf('Acceso habilitado por asistencia en el foro vinculado (%.2f%% ≥ %.2f%%).', $attendanceMax, $minAttendance),
            'via' => 'attendance',
            'attendance_percent' => $attendanceMax,
            'attendance_threshold' => $minAttendance,
        ];
    }

    return [
        'has_access' => false,
        'reason' => sprintf('Sin acceso: requiere estado premium, autorización manual o asistencia mínima de %.2f%% en este foro.', $minAttendance),
        'via' => 'none',
        'attendance_percent' => $attendanceMax,
        'attendance_threshold' => $minAttendance,
    ];
}
