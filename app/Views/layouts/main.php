<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Support/SiteSettings.php';

/**
 * @param array{title?:string,metaDescription?:string,bodyClass?:string,content:string,role?:string,scripts?:array<string>,siteSettings?:array<string,string>} $config
 */
function render_main_layout(array $config): void
{
    $title = $config['title'] ?? 'Foros LATAM PSME';
    $metaDescription = $config['metaDescription'] ?? 'Foros y contenidos de Salud Mental y Emocional.';
    $bodyClass = $config['bodyClass'] ?? 'bg-slate-50 text-slate-900';
    $content = $config['content'] ?? '';
    $role = $config['role'] ?? 'guest';
    $scripts = $config['scripts'] ?? [];
    $siteSettings = is_array($config['siteSettings'] ?? null) ? $config['siteSettings'] : app_public_site_settings();
    $brandPrimary = (string)($siteSettings['brand_color_primary'] ?? '#F6EEE2');
    $brandAccent = (string)($siteSettings['brand_color_accent'] ?? '#A67C52');

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
        :root { --color-primary: <?= htmlspecialchars($brandPrimary, ENT_QUOTES, 'UTF-8') ?>; --color-primary-contrast: #4E3B2A; --color-accent: <?= htmlspecialchars($brandAccent, ENT_QUOTES, 'UTF-8') ?>; }
        .bg-brand { background-color: var(--color-primary); }
        .text-brand { color: var(--color-primary-contrast); }
        .btn-primary {
            background-color: var(--color-accent);
            color: var(--color-primary);
            border: 1px solid color-mix(in srgb, var(--color-accent) 80%, #000 20%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--color-accent) 88%, #000 12%);
            transform: translateY(-1px);
        }
        .btn-primary:focus-visible {
            outline: 2px solid color-mix(in srgb, var(--color-accent) 65%, #fff 35%);
            outline-offset: 2px;
        }

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
