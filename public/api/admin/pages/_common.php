<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';
require_once __DIR__ . '/../../../../app/Services/HtmlSanitizer.php';

function admin_pages_require_admin(): array
{
    $user = api_current_user();
    if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
        api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
    }

    return $user;
}

function admin_pages_validate_slug(string $slug): bool
{
    return (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function admin_pages_normalize_payload(array $input): array
{
    $slug = trim((string)($input['slug'] ?? ''));
    $title = trim((string)($input['title'] ?? ''));
    $contentHtml = HtmlSanitizer::sanitize((string)($input['content_html'] ?? ''));
    $status = trim((string)($input['status'] ?? 'draft'));
    $seoTitle = trim((string)($input['seo_title'] ?? ''));
    $seoDescription = trim((string)($input['seo_description'] ?? ''));

    if ($slug === '' || $title === '' || $contentHtml === '') {
        api_json(['ok' => false, 'error' => 'slug, title y content_html son obligatorios.'], 422);
    }

    if (!admin_pages_validate_slug($slug)) {
        api_json(['ok' => false, 'error' => 'Slug inválido. Usa minúsculas, números y guiones.'], 422);
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        api_json(['ok' => false, 'error' => 'Estado inválido.'], 422);
    }

    return [
        'slug' => $slug,
        'title' => $title,
        'content_html' => $contentHtml,
        'status' => $status,
        'seo_title' => $seoTitle !== '' ? $seoTitle : null,
        'seo_description' => $seoDescription !== '' ? $seoDescription : null,
        'published_at' => $status === 'published' ? gmdate('Y-m-d\TH:i:s\Z') : null,
    ];
}
