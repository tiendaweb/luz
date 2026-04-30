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
<?php if ($role === 'admin'): ?>
    <button id="adminThemeWidgetButton" type="button" class="fixed bottom-6 right-6 z-[70] w-14 h-14 rounded-full text-white shadow-xl hover:scale-105 transition-transform" style="background-color: var(--color-accent);" aria-label="Abrir panel de tema">
        <i class="fa-solid fa-palette text-lg"></i>
    </button>

    <div id="adminThemeWidgetModal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-900/50" data-theme-close></div>
        <div class="relative max-w-3xl mx-auto mt-12 bg-white rounded-3xl shadow-2xl p-6 sm:p-8 max-h-[86vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-slate-900">Theme rápido (Admin)</h3>
                <button type="button" class="text-slate-500 hover:text-slate-900" data-theme-close><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <form id="adminThemeWidgetForm" class="space-y-6">
                <section>
                    <h4 class="font-bold text-slate-800 mb-3">Colores rápidos</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="text-sm font-semibold text-slate-700">Primario<input data-theme-input="colors.primary" type="color" class="mt-1 block w-full h-10 rounded-lg border"/></label>
                        <label class="text-sm font-semibold text-slate-700">Secundario<input data-theme-input="colors.secondary" type="color" class="mt-1 block w-full h-10 rounded-lg border"/></label>
                        <label class="text-sm font-semibold text-slate-700">Acento<input data-theme-input="colors.accent" type="color" class="mt-1 block w-full h-10 rounded-lg border"/></label>
                    </div>
                </section>
                <section>
                    <h4 class="font-bold text-slate-800 mb-3">Tipografía</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="text-sm font-semibold text-slate-700">Fuente
                            <select data-theme-input="typography.font_family" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option>Plus Jakarta Sans</option><option>Inter</option><option>Roboto</option><option>Montserrat</option><option>Lato</option>
                            </select>
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Tamaño base
                            <input data-theme-input="typography.font_size_base" type="text" placeholder="16px" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"/>
                        </label>
                    </div>
                </section>
                <section>
                    <h4 class="font-bold text-slate-800 mb-3">Vista previa</h4>
                    <div id="adminThemeWidgetPreview" class="rounded-2xl border border-slate-200 p-4" style="background: var(--color-surface); color: var(--color-text);">
                        <p class="font-bold mb-2">Así se verá el tema</p>
                        <button type="button" class="px-4 py-2 rounded-full text-white" style="background: var(--color-accent);">Botón ejemplo</button>
                    </div>
                </section>
                <div class="flex flex-wrap gap-3 justify-end">
                    <button type="button" id="adminThemeWidgetReset" class="px-4 py-2 rounded-xl border border-slate-300 font-semibold">Restablecer</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold" style="background: var(--color-accent);">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<script src="/assets/js/admin-theme-widget.js"></script>
<?php foreach ($scripts as $script): ?>
    <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}
