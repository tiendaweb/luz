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

function api_ebook_sign_token(int $userId, int $ebookId, int $expiresAt): string
{
    $payload = $userId . ':' . $ebookId . ':' . $expiresAt;
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

function api_user_ebook_permission(PDO $pdo, int $userId, array $ebook): array
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

    $approvedStmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM registrations
         LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
         WHERE registrations.user_id = :user_id
           AND COALESCE(registration_admin_state.status, "pending") = "approved"'
    );
    $approvedStmt->execute(['user_id' => $userId]);
    $approvedCount = (int)$approvedStmt->fetchColumn();
    $hasApproved = $approvedCount > 0;

    $attendanceStmt = $pdo->prepare(
        'SELECT MAX(COALESCE(registration_attendance.attendance_percent, 0))
         FROM registrations
         LEFT JOIN registration_attendance ON registration_attendance.registration_id = registrations.id
         WHERE registrations.user_id = :user_id'
    );
    $attendanceStmt->execute(['user_id' => $userId]);
    $attendanceMax = (float)$attendanceStmt->fetchColumn();

    $minAttendance = (float)($ebook['min_attendance'] ?? 75);
    $attendanceEligible = $attendanceMax >= $minAttendance;

    $hasAccess = $hasApproved || $attendanceEligible;

    $reason = $hasAccess
        ? ($hasApproved
            ? 'Acceso habilitado por inscripción aprobada.'
            : sprintf('Acceso habilitado por asistencia (%.2f%% ≥ %.2f%%).', $attendanceMax, $minAttendance))
        : sprintf('Sin acceso: requiere estado aprobado o asistencia mínima de %.2f%%.', $minAttendance);

    return [
        'has_access' => $hasAccess,
        'reason' => $reason,
        'via' => $hasApproved ? 'approved' : ($attendanceEligible ? 'attendance' : 'none'),
        'attendance_percent' => $attendanceMax,
        'attendance_threshold' => $minAttendance,
    ];
}
