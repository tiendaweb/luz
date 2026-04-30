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

function api_ebook_log_download(PDO $pdo, ?int $userId, ?int $ebookId, bool $granted, string $reason): void
{
    $stmt = $pdo->prepare('INSERT INTO ebook_download_audit (user_id, ebook_id, event, access_granted, reason, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $userId,
        $ebookId,
        'download',
        $granted ? 1 : 0,
        $reason,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}

function api_user_ebook_permission(PDO $pdo, int $userId, array $ebook, int $forumId): array
{
    $manualStmt = $pdo->prepare(
        'SELECT access_granted, reason, expires_at
         FROM user_ebook_access
         WHERE user_id = :user_id AND ebook_id = :ebook_id
         LIMIT 1'
    );
    $manualStmt->execute([
        'user_id' => $userId,
        'ebook_id' => (int)$ebook['id'],
    ]);
    $manual = $manualStmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($manual)) {
        $expiresAtRaw = trim((string)($manual['expires_at'] ?? ''));
        $isExpired = $expiresAtRaw !== '' && strtotime($expiresAtRaw) !== false && strtotime($expiresAtRaw) < time();
        if (!$isExpired) {
            $granted = (int)$manual['access_granted'] === 1;
            return [
                'has_access' => $granted,
                'reason' => $granted ? 'Acceso otorgado manualmente.' : 'Acceso restringido manualmente.',
                'via' => 'manual',
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
            'attendance_threshold' => (float)($ebook['min_attendance'] ?? 75),
        ];
    }

    $placeholders = implode(',', array_fill(0, count($registrationIds), '?'));

    $approvedStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM registration_admin_state
         WHERE registration_id IN ($placeholders)
           AND status = 'approved'"
    );
    $approvedStmt->execute($registrationIds);
    $approvedCount = (int)$approvedStmt->fetchColumn();
    $hasApproved = $approvedCount > 0;

    $attendanceStmt = $pdo->prepare(
        "SELECT MAX(COALESCE(attendance_percent, 0))
         FROM registration_attendance
         WHERE registration_id IN ($placeholders)"
    );
    $attendanceStmt->execute($registrationIds);
    $attendanceMax = (float)$attendanceStmt->fetchColumn();

    $minAttendance = (float)($ebook['min_attendance'] ?? 75);
    $attendanceEligible = $attendanceMax >= $minAttendance;

    $requiresApproved = (int)($ebook['requires_approved'] ?? 1) === 1;
    $hasAccess = $requiresApproved ? ($hasApproved || $attendanceEligible) : $attendanceEligible;

    $reason = $hasAccess
        ? ($hasApproved
            ? 'Acceso habilitado por inscripción aprobada en el foro vinculado.'
            : sprintf('Acceso habilitado por asistencia en el foro vinculado (%.2f%% ≥ %.2f%%).', $attendanceMax, $minAttendance))
        : ($requiresApproved
            ? sprintf('Sin acceso: requiere estado aprobado o asistencia mínima de %.2f%% en este foro.', $minAttendance)
            : sprintf('Sin acceso: requiere asistencia mínima de %.2f%% en este foro.', $minAttendance));

    return [
        'has_access' => $hasAccess,
        'reason' => $reason,
        'via' => $hasApproved ? 'approved' : ($attendanceEligible ? 'attendance' : 'none'),
        'attendance_percent' => $attendanceMax,
        'attendance_threshold' => $minAttendance,
    ];
}
