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

    if (!in_array($status, ['pending', 'payment_submitted', 'approved', 'rejected'], true)) {
        return ['ok' => false, 'error' => 'Estado inválido.'];
    }
    if ($status === 'rejected' && ($note === null || trim($note) === '')) {
        return ['ok' => false, 'error' => 'La nota es obligatoria para rechazar.'];
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

    $registrationStmt = $pdo->prepare(
        'SELECT registrations.id,
                registrations.needs_cert,
                registrations.payment_proof_base64,
                registration_admin_state.status AS current_status,
                registration_meta.payment_method,
                registration_meta.payment_link,
                registration_meta.price_amount
         FROM registrations
         LEFT JOIN registration_admin_state ON registration_admin_state.registration_id = registrations.id
         LEFT JOIN registration_meta ON registration_meta.registration_id = registrations.id
         WHERE registrations.id = :registration_id
         LIMIT 1'
    );
    $registrationStmt->execute(['registration_id' => $registrationId]);
    $registration = $registrationStmt->fetch();
    if (!is_array($registration)) {
        return ['ok' => false, 'error' => 'Registro inexistente.'];
    }

    if ($status === 'approved') {
        $hasProof = trim((string)($registration['payment_proof_base64'] ?? '')) !== '';
        $needsCert = (int)($registration['needs_cert'] ?? 0) === 1;
        $hasPaidFlow = trim((string)($registration['payment_method'] ?? '')) !== ''
            || trim((string)($registration['payment_link'] ?? '')) !== ''
            || (float)($registration['price_amount'] ?? 0) > 0;
        if (($needsCert || $hasPaidFlow) && !$hasProof) {
            return ['ok' => false, 'error' => 'Debe existir comprobante válido antes de aprobar.'];
        }
    }

    $previousStatus = trim((string)($registration['current_status'] ?? ''));
    if ($previousStatus === '') {
        $previousStatus = 'pending';
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

    if ($previousStatus !== $status) {
        $historyStmt = $pdo->prepare(
            'INSERT INTO registration_status_history (
                registration_id, from_status, to_status, note, reviewed_by_user_id, reviewed_by_role, created_at
            ) VALUES (
                :registration_id, :from_status, :to_status, :note, :reviewed_by_user_id, :reviewed_by_role, :created_at
            )'
        );
        $historyStmt->execute([
            'registration_id' => $registrationId,
            'from_status' => $previousStatus,
            'to_status' => $status,
            'note' => ($note === null || trim($note) === '') ? null : trim($note),
            'reviewed_by_user_id' => $updatedByUserId,
            'reviewed_by_role' => $updatedByRole,
            'created_at' => gmdate('c'),
        ]);
    }

    return ['ok' => true];
}

/**
 * @param array<int, array<string, mixed>> $items
 * @return array<int, array<string, mixed>>
 */
function api_attach_registration_history(PDO $pdo, array $items): array
{
    if (count($items) === 0) {
        return $items;
    }

    $ids = [];
    foreach ($items as $item) {
        $id = (int)($item['id'] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    $ids = array_values(array_unique($ids));
    if (count($ids) === 0) {
        return $items;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $historyStmt = $pdo->prepare(
        "SELECT registration_id, from_status, to_status, note, reviewed_by_user_id, reviewed_by_role, created_at
         FROM registration_status_history
         WHERE registration_id IN ($placeholders)
         ORDER BY created_at DESC, id DESC"
    );
    $historyStmt->execute($ids);
    $historyRows = $historyStmt->fetchAll();

    $historyByRegistration = [];
    foreach ($historyRows as $row) {
        $registrationId = (int)($row['registration_id'] ?? 0);
        if ($registrationId < 1) {
            continue;
        }
        if (!isset($historyByRegistration[$registrationId])) {
            $historyByRegistration[$registrationId] = [];
        }
        $historyByRegistration[$registrationId][] = [
            'from_status' => $row['from_status'],
            'to_status' => $row['to_status'],
            'note' => $row['note'],
            'reviewed_by_user_id' => $row['reviewed_by_user_id'],
            'reviewed_by_role' => $row['reviewed_by_role'],
            'created_at' => $row['created_at'],
        ];
    }

    foreach ($items as &$item) {
        $id = (int)($item['id'] ?? 0);
        $item['status_history'] = $historyByRegistration[$id] ?? [];
    }
    unset($item);

    return $items;
}
