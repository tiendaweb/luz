<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
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
        'INSERT OR IGNORE INTO forum_guests (forum_id, full_name, role, bio, sort_order)
         VALUES (:forum_id, :full_name, :role, :bio, :sort_order)'
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
    ];

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
