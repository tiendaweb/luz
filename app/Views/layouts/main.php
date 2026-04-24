<?php

declare(strict_types=1);

/**
 * @param array{title?:string,metaDescription?:string,bodyClass?:string,content:string,role?:string,scripts?:array<string>} $config
 */
function render_main_layout(array $config): void
{
    $title = $config['title'] ?? 'Foros LATAM PSME';
    $metaDescription = $config['metaDescription'] ?? 'Foros y contenidos de Salud Mental y Emocional.';
    $bodyClass = $config['bodyClass'] ?? 'bg-slate-50 text-slate-900';
    $content = $config['content'] ?? '';
    $role = $config['role'] ?? 'guest';
    $scripts = $config['scripts'] ?? [];

    $headerMode = 'static';
    ?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }

        /* Role-based visibility */
        .admin-only, .associate-only, .user-only { display: none !important; }

        body[data-active-role="admin"] .admin-only { display: block !important; }
        body[data-active-role="admin"] .admin-only.hidden { display: none !important; }

        body[data-active-role="associate"] .associate-only { display: block !important; }
        body[data-active-role="associate"] .associate-only.hidden { display: none !important; }

        body[data-active-role="user"] .user-only { display: block !important; }
        body[data-active-role="user"] .user-only.hidden { display: none !important; }

        /* Espaciado para divs con role-based visibility */
        .admin-only.space-y-2, .associate-only.space-y-2, .user-only.space-y-2 {
            display: flex !important;
            flex-direction: column;
            gap: 0.5rem;
        }
    </style>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>" data-active-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
<?php require __DIR__ . '/../partials/header.php'; ?>
    <main class="pt-24 min-h-[70vh]">
        <?= $content ?>
    </main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
<?php foreach ($scripts as $script): ?>
    <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}
