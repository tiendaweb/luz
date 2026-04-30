<?php

declare(strict_types=1);

require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <article class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 sm:p-10">
        <p class="text-xs font-extrabold uppercase tracking-widest text-amber-700 mb-3">
            Página personalizada
        </p>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-6"><?= htmlspecialchars((string)$page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="prose prose-slate max-w-none">
            <?= (string)$page['content_html'] ?>
        </div>
    </article>
</section>
<?php
$content = (string)ob_get_clean();

render_main_layout([
    'title' => (string)($page['seo_title'] ?: $page['title']),
    'metaDescription' => (string)($page['seo_description'] ?: 'Contenido institucional de Foros PSME.'),
    'content' => $content,
]);
