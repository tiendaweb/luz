<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $forumStmt = $pdo->prepare('SELECT id FROM forums WHERE code = :code LIMIT 1');

    $findUserStmt = $pdo->prepare(
        "SELECT users.id
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE roles.slug = :role_slug
         ORDER BY users.id ASC
         LIMIT 1"
    );

    $findUserStmt->execute(['role_slug' => 'user']);
    $userId = $findUserStmt->fetchColumn();

    $findUserStmt->execute(['role_slug' => 'associate']);
    $associateId = $findUserStmt->fetchColumn();

    if ($userId === false || $associateId === false) {
        throw new RuntimeException('No se encontraron usuarios demo para crear datos de dashboard.');
    }

    $forumStmt->execute(['code' => 'morning']);
    $forumId = $forumStmt->fetchColumn();
    if ($forumId === false) {
        throw new RuntimeException('No se encontró el foro demo "morning".');
    }

    $registrationStmt = $pdo->prepare(
        'INSERT INTO registrations (
            user_id, forum_id, forum_slot, full_name, document_id, needs_cert,
            payment_proof_name, payment_proof_mime, payment_proof_size, payment_proof_base64,
            acceptance_checked, signature_data_url
        )
        SELECT :user_id, :forum_id, :forum_slot, :full_name, :document_id, :needs_cert,
               :payment_proof_name, :payment_proof_mime, :payment_proof_size, :payment_proof_base64,
               :acceptance_checked, :signature_data_url
        WHERE NOT EXISTS (
            SELECT 1 FROM registrations WHERE user_id = :user_id AND forum_id = :forum_id
        )'
    );

    $registrationStmt->execute([
        'user_id' => (int)$userId,
        'forum_id' => (int)$forumId,
        'forum_slot' => 'morning',
        'full_name' => 'Usuario Demo',
        'document_id' => 'DEMO-USER-001',
        'needs_cert' => 1,
        'payment_proof_name' => 'demo-transferencia.pdf',
        'payment_proof_mime' => 'application/pdf',
        'payment_proof_size' => 1024,
        'payment_proof_base64' => base64_encode('demo-payment-proof'),
        'acceptance_checked' => 1,
        'signature_data_url' => 'data:image/png;base64,' . base64_encode('demo-signature'),
    ]);

    $registrationIdStmt = $pdo->prepare('SELECT id FROM registrations WHERE user_id = :user_id AND forum_id = :forum_id LIMIT 1');
    $registrationIdStmt->execute([
        'user_id' => (int)$userId,
        'forum_id' => (int)$forumId,
    ]);
    $registrationId = $registrationIdStmt->fetchColumn();

    if ($registrationId !== false) {
        $stateStmt = $pdo->prepare(
            'INSERT INTO registration_admin_state (registration_id, status, note, updated_by_user_id, updated_by_role)
             SELECT :registration_id, :status, :note, :updated_by_user_id, :updated_by_role
             WHERE NOT EXISTS (SELECT 1 FROM registration_admin_state WHERE registration_id = :registration_id)'
        );

        $stateStmt->execute([
            'registration_id' => (int)$registrationId,
            'status' => 'approved',
            'note' => 'Aprobación demo automática para estado inicial de dashboard.',
            'updated_by_user_id' => (int)$associateId,
            'updated_by_role' => 'associate',
        ]);
    }

    $messageStmt = $pdo->prepare(
        'INSERT INTO messages (sender_user_id, subject, body)
         SELECT :sender_user_id, :subject, :body
         WHERE NOT EXISTS (SELECT 1 FROM messages WHERE subject = :subject LIMIT 1)'
    );

    $messageStmt->execute([
        'sender_user_id' => (int)$userId,
        'subject' => 'Mensaje demo de bienvenida',
        'body' => 'Este mensaje demo verifica que el dashboard inicial tenga métricas y actividad mínima coherente.',
    ]);
};
