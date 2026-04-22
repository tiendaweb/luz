<?php

declare(strict_types=1);

/**
 * @param array{role:string,id?:int} $actor
 * @return array{ok:bool,error?:string}
 */
function api_set_registration_status(PDO $pdo, int $registrationId, string $status, ?string $note, array $actor): array
{
    if ($registrationId < 1) {
        return ['ok' => false, 'error' => 'Registro inválido.'];
    }

    if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
        return ['ok' => false, 'error' => 'Estado inválido.'];
    }

    $role = (string)($actor['role'] ?? '');
    $updatedByUserId = isset($actor['id']) ? (int)$actor['id'] : null;
    $updatedByRole = $role !== '' ? $role : null;

    if (!in_array($role, ['admin', 'associate'], true)) {
        return ['ok' => false, 'error' => 'Rol no autorizado.'];
    }

    if ($role === 'associate' && (!$updatedByUserId || $updatedByUserId < 1)) {
        return ['ok' => false, 'error' => 'Asociado inválido.'];
    }

    if ($role === 'associate') {
        $ownershipStmt = $pdo->prepare(
            'SELECT registrations.id
             FROM registrations
             INNER JOIN registration_meta ON registration_meta.registration_id = registrations.id
             WHERE registrations.id = :registration_id
               AND registration_meta.referrer_user_id = :associate_id
             LIMIT 1'
        );
        $ownershipStmt->execute([
            'registration_id' => $registrationId,
            'associate_id' => $updatedByUserId,
        ]);
        if ($ownershipStmt->fetchColumn() === false) {
            return ['ok' => false, 'error' => 'No autorizado para este registro.'];
        }
    }

    $updateSql = 'INSERT INTO registration_admin_state (
            registration_id, status, note, updated_by_user_id, updated_by_role, updated_at
        ) VALUES (
            :registration_id, :status, :note, :updated_by_user_id, :updated_by_role, :updated_at
        )
        ON CONFLICT(registration_id) DO UPDATE SET
          status = excluded.status,
          note = excluded.note,
          updated_by_user_id = excluded.updated_by_user_id,
          updated_by_role = excluded.updated_by_role,
          updated_at = excluded.updated_at';

    if ($role === 'associate') {
        $updateSql .= '\n        WHERE EXISTS (
            SELECT 1
            FROM registration_meta
            WHERE registration_meta.registration_id = registration_admin_state.registration_id
              AND registration_meta.referrer_user_id = :owner_check_id
        )';
    }

    $updateStmt = $pdo->prepare($updateSql);
    $params = [
        'registration_id' => $registrationId,
        'status' => $status,
        'note' => ($note === null || trim($note) === '') ? null : trim($note),
        'updated_by_user_id' => $updatedByUserId,
        'updated_by_role' => $updatedByRole,
        'updated_at' => gmdate('c'),
    ];
    if ($role === 'associate') {
        $params['owner_check_id'] = $updatedByUserId;
    }

    $updateStmt->execute($params);
    if ($role === 'associate' && $updateStmt->rowCount() < 1) {
        return ['ok' => false, 'error' => 'No autorizado para este registro.'];
    }

    return ['ok' => true];
}
