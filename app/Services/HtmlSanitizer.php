<?php

declare(strict_types=1);

final class HtmlSanitizer
{
    /** @var array<string, string[]> */
    private const ALLOWED_TAGS = [
        'a' => ['href', 'title', 'target', 'rel'],
        'p' => ['class'],
        'br' => [],
        'strong' => [],
        'em' => [],
        'u' => [],
        's' => [],
        'ul' => ['class'],
        'ol' => ['class'],
        'li' => ['class'],
        'blockquote' => ['cite'],
        'h1' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'code' => ['class'],
        'pre' => ['class'],
        'hr' => [],
        'span' => ['class'],
        'div' => ['class'],
    ];

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (!class_exists('DOMDocument')) {
            return strip_tags($html, '<a><p><br><strong><em><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><img><code><pre><hr><span><div>');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body instanceof DOMNode) {
            return '';
        }

        self::sanitizeNode($body);

        $clean = '';
        foreach ($body->childNodes as $child) {
            $clean .= $dom->saveHTML($child);
        }

        return trim($clean);
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if (!$node->hasChildNodes()) {
            return;
        }

        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if (!$child instanceof DOMNode) {
                continue;
            }

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (in_array($tag, ['script', 'iframe', 'object', 'embed', 'form'], true)) {
                    $node->removeChild($child);
                    continue;
                }

                if (!array_key_exists($tag, self::ALLOWED_TAGS)) {
                    self::unwrapNode($child);
                    continue;
                }

                self::sanitizeAttributes($child, self::ALLOWED_TAGS[$tag]);
            }

            self::sanitizeNode($child);
        }
    }

    /** @param string[] $allowedAttributes */
    private static function sanitizeAttributes(DOMElement $element, array $allowedAttributes): void
    {
        for ($i = $element->attributes->length - 1; $i >= 0; $i--) {
            $attribute = $element->attributes->item($i);
            if ($attribute === null) {
                continue;
            }

            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue ?? '');

            if (str_starts_with($name, 'on') || $name === 'style') {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (!in_array($name, $allowedAttributes, true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['href', 'src', 'cite'], true) && !self::isAllowedUrl($value)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if ($element->tagName === 'a' && $name === 'target' && $value === '_blank') {
                $existingRel = strtolower((string)$element->getAttribute('rel'));
                if (!str_contains($existingRel, 'noopener')) {
                    $element->setAttribute('rel', trim($existingRel . ' noopener noreferrer'));
                }
            }
        }
    }

    private static function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function isAllowedUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
