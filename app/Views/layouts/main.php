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

    $themeDefaults = [
        'colors' => ['primary' => '#faf5f0', 'secondary' => '#d9b9a0', 'accent' => '#8a5a2b', 'surface' => '#ffffff', 'text' => '#0f172a'],
        'typography' => ['font_family' => 'Plus Jakarta Sans', 'font_size_base' => '16px'],
        'radius' => ['sm' => '8px', 'md' => '16px', 'lg' => '24px'],
        'shadows' => ['card' => '0 10px 25px rgba(15,23,42,0.1)', 'modal' => '0 20px 45px rgba(15,23,42,0.2)'],
        'spacing' => ['sm' => '8px', 'md' => '16px', 'lg' => '24px'],
        'buttons' => ['size' => 'md', 'padding_y' => '12px', 'padding_x' => '20px'],
    ];
    $themeRaw = (string)($siteSettings['theme_v1'] ?? '');
    $themeDecoded = json_decode($themeRaw, true);
    $theme = is_array($themeDecoded) ? array_replace_recursive($themeDefaults, $themeDecoded) : $themeDefaults;

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
    <style>
        :root { --color-primary: <?= htmlspecialchars((string)$theme['colors']['primary'], ENT_QUOTES, 'UTF-8') ?>; --color-primary-700: <?= htmlspecialchars((string)$theme['colors']['secondary'], ENT_QUOTES, 'UTF-8') ?>; --color-accent: <?= htmlspecialchars((string)$theme['colors']['accent'], ENT_QUOTES, 'UTF-8') ?>; --color-surface: <?= htmlspecialchars((string)$theme['colors']['surface'], ENT_QUOTES, 'UTF-8') ?>; --color-text: <?= htmlspecialchars((string)$theme['colors']['text'], ENT_QUOTES, 'UTF-8') ?>; --font-family-base: <?= htmlspecialchars((string)$theme['typography']['font_family'], ENT_QUOTES, 'UTF-8') ?>, sans-serif; --font-size-base: <?= htmlspecialchars((string)$theme['typography']['font_size_base'], ENT_QUOTES, 'UTF-8') ?>; --radius-sm: <?= htmlspecialchars((string)$theme['radius']['sm'], ENT_QUOTES, 'UTF-8') ?>; --radius-md: <?= htmlspecialchars((string)$theme['radius']['md'], ENT_QUOTES, 'UTF-8') ?>; --radius-lg: <?= htmlspecialchars((string)$theme['radius']['lg'], ENT_QUOTES, 'UTF-8') ?>; --shadow-card: <?= htmlspecialchars((string)$theme['shadows']['card'], ENT_QUOTES, 'UTF-8') ?>; --shadow-modal: <?= htmlspecialchars((string)$theme['shadows']['modal'], ENT_QUOTES, 'UTF-8') ?>; --space-sm: <?= htmlspecialchars((string)$theme['spacing']['sm'], ENT_QUOTES, 'UTF-8') ?>; --space-md: <?= htmlspecialchars((string)$theme['spacing']['md'], ENT_QUOTES, 'UTF-8') ?>; --space-lg: <?= htmlspecialchars((string)$theme['spacing']['lg'], ENT_QUOTES, 'UTF-8') ?>; --btn-size: <?= htmlspecialchars((string)$theme['buttons']['size'], ENT_QUOTES, 'UTF-8') ?>; --btn-pad-y: <?= htmlspecialchars((string)$theme['buttons']['padding_y'], ENT_QUOTES, 'UTF-8') ?>; --btn-pad-x: <?= htmlspecialchars((string)$theme['buttons']['padding_x'], ENT_QUOTES, 'UTF-8') ?>; }
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
