<?php
declare(strict_types=1);

namespace App\Services\Posts;

final class ExcerptGenerator
{
    public function fromHtml(string $html, int $maxLength = 240): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        $text = trim($text);

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $trimmed = mb_substr($text, 0, $maxLength - 1);
        $lastSpace = mb_strrpos($trimmed, ' ');
        if ($lastSpace !== false) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }

        return rtrim($trimmed, '.,;:-–— ') . '…';
    }
}
