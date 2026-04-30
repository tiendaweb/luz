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
    $brandPrimary = (string)($siteSettings['brand_color_primary'] ?? '#FAF0E6');
    $brandAccent = (string)($siteSettings['brand_color_accent'] ?? '#C9A67E');

    $headerMode = 'static';
    ?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <script>
        tailwind = window.tailwind || {};
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            50: '#faf5f0',
                            100: '#f0e6d8',
                            200: '#e5d4b8',
                            300: '#d9b9a0',
                            400: '#c9a67e',
                            500: '#b8925c',
                            600: '#9e7d50',
                            700: '#876946',
                            800: '#6d5437',
                            900: '#563f2a'
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
    <style>
        :root { --color-primary: <?= htmlspecialchars($brandPrimary, ENT_QUOTES, 'UTF-8') ?>; --color-accent: <?= htmlspecialchars($brandAccent, ENT_QUOTES, 'UTF-8') ?>; }
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
