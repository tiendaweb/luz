<?php

declare(strict_types=1);

return static function (PDO $pdo): void {

    $forumInsertStmt = $pdo->prepare(
        'INSERT INTO forums (code, title, description, platform_type, platform_url, timezone, status, speaker_json, starts_at)
         SELECT :code, :title, :description, :platform_type, :platform_url, :timezone, :status, :speaker_json, :starts_at
         WHERE NOT EXISTS (SELECT 1 FROM forums WHERE code = :code)'
    );
    $forumUpdateStmt = $pdo->prepare(
        'UPDATE forums
         SET objective = :objective,
             topics_json = :topics_json,
             modality = :modality,
             requirements = :requirements,
             seats_total = :seats_total,
             seats_available = :seats_available,
             cta_label = :cta_label,
             cta_url = :cta_url
         WHERE code = :code'
    );

    $forumGuestStmt = $pdo->prepare(
        'INSERT INTO forum_guests (forum_id, full_name, role, bio, sort_order)
         SELECT :forum_id, :full_name, :role, :bio, :sort_order
         WHERE NOT EXISTS (
            SELECT 1
            FROM forum_guests
            WHERE forum_id = :forum_id
              AND full_name = :full_name
              AND role = :role
         )'
    );

    $forums = [
        [
            'code' => 'morning',
            'objective' => 'Profundizar estrategias de intervención psicosocial en contextos clínicos y comunitarios.',
            'topics_json' => json_encode([
                'Intervención grupal en crisis',
                'Lectura de emergentes vinculares',
                'Diseño de dispositivos de cuidado',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'modality' => 'Virtual en vivo por Zoom',
            'requirements' => 'Dirigido a profesionales y estudiantes avanzados del área psicosocial. Se recomienda experiencia previa en coordinación de grupos.',
            'seats_total' => 80,
            'seats_available' => 26,
            'cta_label' => 'Reservar cupo mañana',
            'cta_url' => '/#view-forums',
            'guests' => [
                [
                    'full_name' => 'Maria Luz Genovese',
                    'role' => 'Directora y moderadora',
                    'bio' => 'Psicóloga Social especializada en coordinación de grupos operativos y salud mental comunitaria.',
                    'sort_order' => 1,
                ],
                [
                    'full_name' => 'Dra. Claudia Vaca',
                    'role' => 'Invitada internacional',
                    'bio' => 'Psicóloga Clínica (Colombia) enfocada en trauma complejo y abordajes interdisciplinarios.',
                    'sort_order' => 2,
                ],
            ],
        ],
        [
            'code' => 'afternoon',
            'objective' => 'Integrar herramientas teórico-prácticas para la atención en salud mental desde una perspectiva latinoamericana.',
            'topics_json' => json_encode([
                'Salud mental y territorio',
                'Construcción de redes comunitarias',
                'Autocuidado profesional',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'modality' => 'Virtual en vivo por Google Meet',
            'requirements' => 'Abierto a estudiantes y equipos de trabajo de instituciones educativas o de salud.',
            'seats_total' => 100,
            'seats_available' => 41,
            'cta_label' => 'Inscribirme al foro tarde',
            'cta_url' => '/#view-forums',
            'guests' => [
                [
                    'full_name' => 'Maria Luz Genovese',
                    'role' => 'Directora y moderadora',
                    'bio' => 'Psicóloga Social especializada en coordinación de grupos operativos y salud mental comunitaria.',
                    'sort_order' => 1,
                ],
                [
                    'full_name' => 'Lic. Tomás Riera',
                    'role' => 'Invitado especial',
                    'bio' => 'Especialista en salud pública y diseño de programas de intervención territorial con jóvenes.',
                    'sort_order' => 2,
                ],
            ],
        ],
        [
            'code' => 'evening_premium',
            'objective' => 'Desarrollar estrategias avanzadas de coordinación clínica para equipos que requieren supervisión especializada.',
            'topics_json' => json_encode([
                'Supervisión de casos críticos',
                'Diseño de planes de intervención escalonada',
                'Derivación y seguimiento interdisciplinario',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'modality' => 'Virtual en vivo por Zoom (cupo premium)',
            'requirements' => 'Acceso por evaluación previa. Incluye materiales premium, sesión de preguntas extendida y mentoría posterior.',
            'seats_total' => 40,
            'seats_available' => 12,
            'cta_label' => 'Aplicar al foro premium',
            'cta_url' => '/#view-forums',
            'guests' => [
                [
                    'full_name' => 'Maria Luz Genovese',
                    'role' => 'Directora y moderadora',
                    'bio' => 'Psicóloga Social especializada en coordinación de grupos operativos y salud mental comunitaria.',
                    'sort_order' => 1,
                ],
                [
                    'full_name' => 'Lic. Andrea Sosa',
                    'role' => 'Supervisora clínica invitada',
                    'bio' => 'Especialista en intervención clínica breve y acompañamiento a equipos de alto desgaste.',
                    'sort_order' => 2,
                ],
            ],
        ]
    ];


    $forumInsertStmt->execute([
        'code' => 'evening_premium',
        'title' => 'Foro Premium Nocturno',
        'description' => 'Cohorte intensiva con beneficios exclusivos y seguimiento personalizado para casos complejos.',
        'platform_type' => 'zoom',
        'platform_url' => 'https://zoom.us/j/psme-premium-noche',
        'timezone' => 'America/Mexico_City',
        'status' => 'published',
        'speaker_json' => '[{"name":"Maria Luz Genovese","role":"Directora"},{"name":"Lic. Andrea Sosa","role":"Supervisora clínica"}]',
        'starts_at' => '2026-05-10T01:00:00Z',
    ]);

    $forumIdStmt = $pdo->prepare('SELECT id FROM forums WHERE code = :code LIMIT 1');

    foreach ($forums as $forum) {
        $forumUpdateStmt->execute([
            'code' => $forum['code'],
            'objective' => $forum['objective'],
            'topics_json' => $forum['topics_json'],
            'modality' => $forum['modality'],
            'requirements' => $forum['requirements'],
            'seats_total' => $forum['seats_total'],
            'seats_available' => $forum['seats_available'],
            'cta_label' => $forum['cta_label'],
            'cta_url' => $forum['cta_url'],
        ]);

        $forumIdStmt->execute(['code' => $forum['code']]);
        $forumId = $forumIdStmt->fetchColumn();
        if ($forumId === false) {
            continue;
        }

        foreach ($forum['guests'] as $guest) {
            $forumGuestStmt->execute([
                'forum_id' => (int)$forumId,
                'full_name' => $guest['full_name'],
                'role' => $guest['role'],
                'bio' => $guest['bio'],
                'sort_order' => $guest['sort_order'],
            ]);
        }
    }
};
