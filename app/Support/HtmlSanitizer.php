<?php
declare(strict_types=1);

namespace App\Support;

final class HtmlSanitizer
{
    /**
     * Sanitize limited inline HTML snippets for inline editing.
     */
    public static function sanitize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $allowedTags = [
            'p', 'br', 'strong', 'em', 'ul', 'ol', 'li',
            'h1', 'h2', 'h3', 'h4', 'blockquote', 'a', 'span',
        ];

        $attributeAllowlist = [
            '*' => ['class'],
            'a' => ['href', 'target', 'rel', 'title', 'class'],
            'span' => ['class'],
        ];

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $html = '<!DOCTYPE html><html><body>' . $value . '</body></html>';
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        self::sanitizeNode($body, $allowedTags, $attributeAllowlist);

        $sanitized = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $sanitized .= $doc->saveHTML($child);
        }

        return trim($sanitized);
    }

    /**
     * @param array<string> $allowedTags
     * @param array<string, array<string>> $attributeAllowlist
     */
    private static function sanitizeNode(\DOMNode $node, array $allowedTags, array $attributeAllowlist): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if (!in_array($tag, $allowedTags, true)) {
                    self::unwrapNode($child);
                    continue;
                }

                self::sanitizeAttributes($child, $tag, $attributeAllowlist);
                self::sanitizeNode($child, $allowedTags, $attributeAllowlist);
            } elseif ($child instanceof \DOMComment) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    private static function unwrapNode(\DOMNode $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) {
            return;
        }

        while ($node->hasChildNodes()) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    /**
     * @param array<string, array<string>> $attributeAllowlist
     */
    private static function sanitizeAttributes(\DOMElement $element, string $tag, array $attributeAllowlist): void
    {
        $allowed = array_merge($attributeAllowlist['*'] ?? [], $attributeAllowlist[$tag] ?? []);

        /** @var \DOMAttr $attribute */
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if (str_starts_with($name, 'on')) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (!in_array($name, $allowed, true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            $value = trim($attribute->value);
            if ($value === '') {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'class') {
                $sanitized = preg_replace('/[^a-z0-9\\-\\s_]/i', '', $value) ?? '';
                if ($sanitized === '') {
                    $element->removeAttribute($attribute->name);
                } else {
                    $element->setAttribute($attribute->name, preg_replace('/\\s+/', ' ', $sanitized) ?? $sanitized);
                }
                continue;
            }

            if ($tag === 'a' && $name === 'href') {
                $href = self::sanitizeHref($value);
                if ($href === '') {
                    $element->removeAttribute('href');
                } else {
                    $element->setAttribute('href', $href);
                }
                continue;
            }

            if ($tag === 'a' && $name === 'target') {
                $target = strtolower($value);
                if (!in_array($target, ['_blank', '_self'], true)) {
                    $element->removeAttribute('target');
                } else {
                    $element->setAttribute('target', $target);
                    if ($target === '_blank') {
                        $rel = strtolower($element->getAttribute('rel'));
                        $parts = array_filter(array_unique(explode(' ', $rel . ' noopener noreferrer')));
                        $element->setAttribute('rel', implode(' ', $parts));
                    }
                }
                continue;
            }

            if ($tag === 'a' && $name === 'rel') {
                $rel = preg_replace('/[^a-z\\s_-]/i', '', $value) ?? '';
                $element->setAttribute('rel', trim(preg_replace('/\\s+/', ' ', $rel) ?? ''));
            }
        }
    }

    private static function sanitizeHref(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $lower = strtolower($value);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return '';
    }
}

