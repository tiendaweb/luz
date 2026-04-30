<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $authorId = $pdo->query(
        "SELECT users.id
         FROM users
         INNER JOIN roles ON roles.id = users.role_id
         WHERE roles.slug = 'admin'
         ORDER BY users.id ASC
         LIMIT 1"
    )->fetchColumn();

    $authorId = $authorId === false ? null : (int)$authorId;

    $posts = [
        [
            'slug' => 'psme-que-es-y-por-que-importa',
            'title' => 'PSME: qué es y por qué importa hoy',
            'excerpt' => 'Una introducción clara al enfoque de salud mental y emocional que guía nuestros foros.',
            'content_html' => '<p>En los foros PSME abordamos la salud mental y emocional como un proceso colectivo. Trabajamos con herramientas de escucha, lectura de emergentes y diseño de intervenciones situadas.</p><p>La propuesta integra teoría y práctica para fortalecer equipos, comunidades e instituciones educativas y de salud.</p>',
            'status' => 'published',
            'published_at' => '2026-03-01T12:00:00Z',
        ],
        [
            'slug' => 'coordinacion-grupal-en-crisis',
            'title' => 'Coordinación grupal en crisis: aprendizajes del ciclo PSME',
            'excerpt' => 'Recorridos y decisiones clave para sostener grupos en momentos de alta carga emocional.',
            'content_html' => '<p>Cuando una comunidad atraviesa crisis, la coordinación grupal requiere sostén, encuadre y lectura contextual. En PSME desarrollamos protocolos para facilitar participación cuidada y continuidad de procesos.</p><p>El foco está en la construcción de respuestas posibles con recursos reales del territorio.</p>',
            'status' => 'published',
            'published_at' => '2026-03-10T16:00:00Z',
        ],
        [
            'slug' => 'territorio-redes-y-salud-mental',
            'title' => 'Territorio, redes y salud mental comunitaria',
            'excerpt' => 'Cómo articulamos intervenciones entre escuelas, centros de salud y organizaciones locales.',
            'content_html' => '<p>El enfoque PSME promueve prácticas intersectoriales. La articulación entre actores locales mejora la detección temprana de malestar y amplía capacidades de cuidado.</p><p>Compartimos herramientas para mapear redes, definir roles y sostener acuerdos de intervención.</p>',
            'status' => 'published',
            'published_at' => '2026-03-18T18:30:00Z',
        ],
        [
            'slug' => 'autocuidado-profesional-equipos-psme',
            'title' => 'Autocuidado profesional en equipos PSME',
            'excerpt' => 'Pautas concretas para prevenir desgaste y sostener la tarea psicosocial en el tiempo.',
            'content_html' => '<p>El autocuidado no es individualista: es una estrategia institucional. Diseñamos rutinas de revisión, pausa y supervisión para cuidar a quienes cuidan.</p><p>Estos recursos reducen la fatiga emocional y fortalecen la calidad de las intervenciones comunitarias.</p>',
            'status' => 'published',
            'published_at' => '2026-03-25T14:15:00Z',
        ],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO blog_posts (slug, title, excerpt, content_html, status, author_user_id, published_at, updated_at)
         VALUES (:slug, :title, :excerpt, :content_html, :status, :author_user_id, :published_at, CURRENT_TIMESTAMP)
         ON CONFLICT(slug) DO UPDATE SET
            title = excluded.title,
            excerpt = excluded.excerpt,
            content_html = excluded.content_html,
            status = excluded.status,
            author_user_id = excluded.author_user_id,
            published_at = excluded.published_at,
            updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($posts as $post) {
        $stmt->execute([
            'slug' => $post['slug'],
            'title' => $post['title'],
            'excerpt' => $post['excerpt'],
            'content_html' => $post['content_html'],
            'status' => $post['status'],
            'author_user_id' => $authorId,
            'published_at' => $post['published_at'],
        ]);
    }
};
