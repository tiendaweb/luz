<?php

declare(strict_types=1);

require_once __DIR__ . '/../Database/connection.php';

/** @var array<string, array<string, array<string,mixed>>> */
static $contentBlockCache = [];

function content_blocks_for_context(string $context, string $locale = 'es'): array
{
    global $contentBlockCache;
    $cacheKey = $context . '::' . $locale;
    if (isset($contentBlockCache[$cacheKey])) {
        return $contentBlockCache[$cacheKey];
    }

    $pdo = db();

    try {
        $stmt = $pdo->prepare('SELECT block_key, value, content_type, version FROM content_blocks WHERE context = :context AND locale = :locale');
        $stmt->execute(['context' => $context, 'locale' => $locale]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        $message = strtolower($exception->getMessage());
        if (strpos($message, 'no such table: content_blocks') === false) {
            throw $exception;
        }

        $contentBlockCache[$cacheKey] = [];
        return [];
    }

    $blocks = [];
    foreach ($rows as $row) {
        $key = (string)($row['block_key'] ?? '');
        if ($key === '') {
            continue;
        }
        $blocks[$key] = $row;
    }

    $contentBlockCache[$cacheKey] = $blocks;
    return $blocks;
}

function content_block_value(string $context, string $key, string $default, string $locale = 'es'): string
{
    $blocks = content_blocks_for_context($context, $locale);
    $value = $blocks[$key]['value'] ?? null;
    return is_string($value) && trim($value) !== '' ? $value : $default;
}
