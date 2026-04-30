<?php

declare(strict_types=1);

require_once __DIR__ . '/../../_bootstrap.php';

$user = api_current_user();
if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
    api_json(['ok' => false, 'error' => 'Acceso denegado'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_json(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$pdo = api_require_db();
$rows = $pdo->query(
    'SELECT blog_posts.id,
            blog_posts.slug,
            blog_posts.title,
            blog_posts.excerpt,
            blog_posts.content_html,
            blog_posts.status,
            blog_posts.author_user_id,
            blog_posts.published_at,
            blog_posts.created_at,
            blog_posts.updated_at,
            users.full_name AS author_name
     FROM blog_posts
     LEFT JOIN users ON users.id = blog_posts.author_user_id
     ORDER BY COALESCE(blog_posts.published_at, blog_posts.created_at) DESC, blog_posts.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

api_json(['ok' => true, 'items' => $rows]);
