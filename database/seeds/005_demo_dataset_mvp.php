<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;

    $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
    $roleStmt->execute(['slug' => 'user']);
    $userRoleId = $roleStmt->fetchColumn();

    $roleStmt->execute(['slug' => 'associate']);
    $associateRoleId = $roleStmt->fetchColumn();

    if ($userRoleId === false || $associateRoleId === false) {
        throw new RuntimeException('No se pudieron resolver roles para seed demo MVP.');
    }

    $upsertUserStmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, document_id, role_id, password_hash, updated_at)
         VALUES (:full_name, :email, :document_id, :role_id, :password_hash, CURRENT_TIMESTAMP)
         ON CONFLICT(email) DO UPDATE SET
            full_name = excluded.full_name,
            document_id = excluded.document_id,
            role_id = excluded.role_id,
            password_hash = excluded.password_hash,
            updated_at = CURRENT_TIMESTAMP'
    );

    $findUserByEmailStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $findForumByCodeStmt = $pdo->prepare('SELECT id FROM forums WHERE code = :code LIMIT 1');

    $demoUsers = [
        ['full_name' => 'Asociada Red Demo', 'email' => 'asociada.red@psme.local', 'document_id' => 'ASOC-DEMO-100', 'role_id' => (int)$associateRoleId],
        ['full_name' => 'Usuario Referido Aprobado', 'email' => 'referido.aprobado@psme.local', 'document_id' => 'USR-DEMO-201', 'role_id' => (int)$userRoleId],
        ['full_name' => 'Usuario Referido Pendiente', 'email' => 'referido.pendiente@psme.local', 'document_id' => 'USR-DEMO-202', 'role_id' => (int)$userRoleId],
        ['full_name' => 'Usuario Referido Rechazado', 'email' => 'referido.rechazado@psme.local', 'document_id' => 'USR-DEMO-203', 'role_id' => (int)$userRoleId],
        ['full_name' => 'Usuario Directo Sin Referido', 'email' => 'usuario.directo@psme.local', 'document_id' => 'USR-DEMO-204', 'role_id' => (int)$userRoleId],
    ];

    $userIdsByEmail = [];
    foreach ($demoUsers as $demoUser) {
        $upsertUserStmt->execute([
            'full_name' => $demoUser['full_name'],
            'email' => $demoUser['email'],
            'document_id' => $demoUser['document_id'],
            'role_id' => $demoUser['role_id'],
            'password_hash' => password_hash('Demo1234*', $hashAlgo),
        ]);

        $findUserByEmailStmt->execute(['email' => $demoUser['email']]);
        $userId = $findUserByEmailStmt->fetchColumn();
        if ($userId === false) {
            throw new RuntimeException(sprintf('No se pudo recuperar user_id para %s', $demoUser['email']));
        }
        $userIdsByEmail[$demoUser['email']] = (int)$userId;
    }

    $associateId = $userIdsByEmail['asociada.red@psme.local'];

    $referrals = [
        'referido.aprobado@psme.local' => 'Referido con aprobación y certificado de asistencia.',
        'referido.pendiente@psme.local' => 'Referido en revisión administrativa.',
        'referido.rechazado@psme.local' => 'Referido rechazado por validación comercial.',
    ];

    $upsertReferralStmt = $pdo->prepare(
        'INSERT INTO referrals (referrer_user_id, referred_user_id, note)
         SELECT :referrer_user_id, :referred_user_id, :note
         WHERE NOT EXISTS (
             SELECT 1 FROM referrals
             WHERE referrer_user_id = :referrer_user_id
               AND referred_user_id = :referred_user_id
         )'
    );

    foreach ($referrals as $referredEmail => $note) {
        $upsertReferralStmt->execute([
            'referrer_user_id' => $associateId,
            'referred_user_id' => $userIdsByEmail[$referredEmail],
            'note' => $note,
        ]);
    }

    $forumCodes = ['morning', 'afternoon', 'evening_premium'];
    $forumIds = [];
    foreach ($forumCodes as $code) {
        $findForumByCodeStmt->execute(['code' => $code]);
        $forumId = $findForumByCodeStmt->fetchColumn();
        if ($forumId === false) {
            throw new RuntimeException(sprintf('No existe el foro requerido para seed demo: %s', $code));
        }

        $forumIds[$code] = (int)$forumId;
    }

    $registerStmt = $pdo->prepare(
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

    $registrationIdStmt = $pdo->prepare('SELECT id FROM registrations WHERE user_id = :user_id AND forum_id = :forum_id LIMIT 1');
    $stateStmt = $pdo->prepare(
        'INSERT INTO registration_admin_state (registration_id, status, note, updated_by_user_id, updated_by_role)
         VALUES (:registration_id, :status, :note, :updated_by_user_id, :updated_by_role)
         ON CONFLICT(registration_id) DO UPDATE SET
            status = excluded.status,
            note = excluded.note,
            updated_by_user_id = excluded.updated_by_user_id,
            updated_by_role = excluded.updated_by_role,
            updated_at = CURRENT_TIMESTAMP'
    );

    $historyStmt = $pdo->prepare(
        'INSERT INTO registration_status_history (registration_id, from_status, to_status, note, reviewed_by_user_id, reviewed_by_role)
         VALUES (:registration_id, :from_status, :to_status, :note, :reviewed_by_user_id, :reviewed_by_role)'
    );

    $metaStmt = $pdo->prepare(
        'INSERT INTO registration_meta (registration_id, referral_code, referrer_user_id, network_id, country_code)
         VALUES (:registration_id, :referral_code, :referrer_user_id, :network_id, :country_code)
         ON CONFLICT(registration_id) DO UPDATE SET
            referral_code = excluded.referral_code,
            referrer_user_id = excluded.referrer_user_id,
            network_id = excluded.network_id,
            country_code = excluded.country_code'
    );

    $attendanceStmt = $pdo->prepare(
        'INSERT INTO registration_attendance (registration_id, attendance_percent)
         VALUES (:registration_id, :attendance_percent)
         ON CONFLICT(registration_id) DO UPDATE SET
            attendance_percent = excluded.attendance_percent,
            recorded_at = CURRENT_TIMESTAMP'
    );

    $certificateStmt = $pdo->prepare(
        'INSERT INTO user_certificates (user_id, forum_id, type, created_at, created_by_user_id)
         VALUES (:user_id, :forum_id, :type, CURRENT_TIMESTAMP, :created_by_user_id)
         ON CONFLICT(user_id, forum_id, type) DO NOTHING'
    );

    $cases = [
        [
            'email' => 'referido.aprobado@psme.local',
            'forum_code' => 'morning',
            'admin_status' => 'approved',
            'has_payment_proof' => true,
            'attendance_percent' => 98,
            'country_code' => 'AR',
        ],
        [
            'email' => 'referido.pendiente@psme.local',
            'forum_code' => 'afternoon',
            'admin_status' => 'pending',
            'has_payment_proof' => false,
            'attendance_percent' => 42,
            'country_code' => 'CL',
        ],
        [
            'email' => 'referido.rechazado@psme.local',
            'forum_code' => 'evening_premium',
            'admin_status' => 'rejected',
            'has_payment_proof' => true,
            'attendance_percent' => 35,
            'country_code' => 'PE',
        ],
        [
            'email' => 'usuario.directo@psme.local',
            'forum_code' => 'afternoon',
            'admin_status' => 'approved',
            'has_payment_proof' => false,
            'attendance_percent' => 88,
            'country_code' => 'UY',
        ],
    ];

    foreach ($cases as $index => $case) {
        $userId = $userIdsByEmail[$case['email']];
        $forumId = $forumIds[$case['forum_code']];
        $proofName = $case['has_payment_proof'] ? sprintf('comprobante-demo-%d.pdf', $index + 1) : null;

        $registerStmt->execute([
            'user_id' => $userId,
            'forum_id' => $forumId,
            'forum_slot' => $case['forum_code'],
            'full_name' => $demoUsers[array_search($case['email'], array_column($demoUsers, 'email'), true)]['full_name'],
            'document_id' => $demoUsers[array_search($case['email'], array_column($demoUsers, 'email'), true)]['document_id'],
            'needs_cert' => 1,
            'payment_proof_name' => $proofName,
            'payment_proof_mime' => $case['has_payment_proof'] ? 'application/pdf' : null,
            'payment_proof_size' => $case['has_payment_proof'] ? 2048 : null,
            'payment_proof_base64' => $case['has_payment_proof'] ? base64_encode('proof-demo-' . $case['email']) : null,
            'acceptance_checked' => 1,
            'signature_data_url' => 'data:image/png;base64,' . base64_encode('signature-demo-' . $case['email']),
        ]);

        $registrationIdStmt->execute(['user_id' => $userId, 'forum_id' => $forumId]);
        $registrationId = (int)$registrationIdStmt->fetchColumn();

        $stateNote = match ($case['admin_status']) {
            'approved' => 'Aprobado automáticamente para pruebas de beneficios.',
            'rejected' => 'Rechazado en QA demo para validar trazabilidad.',
            default => 'Pendiente de revisión administrativa en demo.',
        };

        $stateStmt->execute([
            'registration_id' => $registrationId,
            'status' => $case['admin_status'],
            'note' => $stateNote,
            'updated_by_user_id' => $associateId,
            'updated_by_role' => 'associate',
        ]);

        $historyStmt->execute([
            'registration_id' => $registrationId,
            'from_status' => 'pending',
            'to_status' => $case['admin_status'],
            'note' => $stateNote,
            'reviewed_by_user_id' => $associateId,
            'reviewed_by_role' => 'associate',
        ]);

        $metaStmt->execute([
            'registration_id' => $registrationId,
            'referral_code' => $case['email'] === 'usuario.directo@psme.local' ? null : 'ASOC-RED-2026',
            'referrer_user_id' => $case['email'] === 'usuario.directo@psme.local' ? null : $associateId,
            'network_id' => 10,
            'country_code' => $case['country_code'],
        ]);

        $attendanceStmt->execute([
            'registration_id' => $registrationId,
            'attendance_percent' => $case['attendance_percent'],
        ]);

        if ($case['admin_status'] === 'approved' && $case['attendance_percent'] >= 75) {
            $certificateStmt->execute([
                'user_id' => $userId,
                'forum_id' => $forumId,
                'type' => 'attendance',
                'created_by_user_id' => $associateId,
            ]);
        }
    }

    $ebookSeed = [
        [
            'title' => 'Manual PSME Base',
            'description' => 'Contenido introductorio para participantes de cualquier foro publicado.',
            'provider' => 'external',
            'local_path' => null,
            'external_url' => 'https://example.com/ebooks/manual-psme-base.pdf',
            'min_attendance' => 0,
            'requires_approved' => 0,
            'forums' => ['morning', 'afternoon', 'evening_premium'],
        ],
        [
            'title' => 'Toolkit Intervención Avanzada',
            'description' => 'Beneficio para cohortes aprobadas con asistencia alta.',
            'provider' => 'external',
            'local_path' => null,
            'external_url' => 'https://example.com/ebooks/toolkit-intervencion-avanzada.pdf',
            'min_attendance' => 80,
            'requires_approved' => 1,
            'forums' => ['morning', 'evening_premium'],
        ],
        [
            'title' => 'Guía Premium de Coordinación Clínica',
            'description' => 'Material exclusivo vinculado al foro premium nocturno.',
            'provider' => 'external',
            'local_path' => null,
            'external_url' => 'https://example.com/ebooks/guia-premium-coordinacion-clinica.pdf',
            'min_attendance' => 90,
            'requires_approved' => 1,
            'forums' => ['evening_premium'],
        ],
    ];

    $insertEbookStmt = $pdo->prepare(
        'INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved, created_at, updated_at)
         SELECT :title, :description, :status, :provider, :local_path, :external_url, :min_attendance, :requires_approved, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
         WHERE NOT EXISTS (SELECT 1 FROM ebooks WHERE title = :title)'
    );

    $updateEbookStmt = $pdo->prepare(
        'UPDATE ebooks
         SET description = :description,
             status = :status,
             provider = :provider,
             local_path = :local_path,
             external_url = :external_url,
             min_attendance = :min_attendance,
             requires_approved = :requires_approved,
             updated_at = CURRENT_TIMESTAMP
         WHERE title = :title'
    );

    $ebookIdStmt = $pdo->prepare('SELECT id FROM ebooks WHERE title = :title LIMIT 1');
    $forumEbookStmt = $pdo->prepare(
        'INSERT INTO forum_ebooks (forum_id, ebook_id, is_active, updated_at)
         VALUES (:forum_id, :ebook_id, 1, CURRENT_TIMESTAMP)
         ON CONFLICT(forum_id, ebook_id) DO UPDATE SET is_active = 1, updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($ebookSeed as $ebook) {
        $insertEbookStmt->execute([
            'title' => $ebook['title'],
            'description' => $ebook['description'],
            'status' => 'published',
            'provider' => $ebook['provider'],
            'local_path' => $ebook['local_path'],
            'external_url' => $ebook['external_url'],
            'min_attendance' => $ebook['min_attendance'],
            'requires_approved' => $ebook['requires_approved'],
        ]);

        $updateEbookStmt->execute([
            'title' => $ebook['title'],
            'description' => $ebook['description'],
            'status' => 'published',
            'provider' => $ebook['provider'],
            'local_path' => $ebook['local_path'],
            'external_url' => $ebook['external_url'],
            'min_attendance' => $ebook['min_attendance'],
            'requires_approved' => $ebook['requires_approved'],
        ]);

        $ebookIdStmt->execute(['title' => $ebook['title']]);
        $ebookId = $ebookIdStmt->fetchColumn();
        if ($ebookId === false) {
            continue;
        }

        foreach ($ebook['forums'] as $forumCode) {
            $forumEbookStmt->execute([
                'forum_id' => $forumIds[$forumCode],
                'ebook_id' => (int)$ebookId,
            ]);
        }
    }
};
