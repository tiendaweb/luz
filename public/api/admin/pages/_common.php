<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';
require_once __DIR__ . '/../../../../app/Services/HtmlSanitizer.php';

function admin_pages_require_admin(): array
{
    return api_require_role('admin');
}

function admin_pages_validate_slug(string $slug): bool
{
    return api_validate_slug($slug);
}

function admin_pages_normalize_payload(array $input): array
{
    api_require_fields($input, ['slug', 'title', 'content_html']);

    $slug = api_input_string($input, 'slug', true);
    $title = api_input_string($input, 'title', true);
    $contentHtml = HtmlSanitizer::sanitize((string)($input['content_html'] ?? ''));
    $status = trim((string)($input['status'] ?? 'draft'));
    $seoTitle = api_input_string($input, 'seo_title');
    $seoDescription = api_input_string($input, 'seo_description');

    if (!admin_pages_validate_slug($slug)) {
        api_error('Slug inválido. Usa minúsculas, números y guiones.', 422, 'validation_error', ['field' => 'slug']);
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        api_error('Estado inválido.', 422, 'validation_error', ['field' => 'status']);
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
