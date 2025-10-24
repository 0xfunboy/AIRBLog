<?php
declare(strict_types=1);

namespace App\Support;

final class Sanitizer
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, string> $map
     * @return array<string, string>
     */
    public static function clean(array $input, array $map): array
    {
        $result = [];

        foreach ($map as $field => $type) {
            $value = $input[$field] ?? '';
            $value = is_string($value) ? trim($value) : '';

            switch ($type) {
                case 'string':
                    $result[$field] = strip_tags($value);
                    break;
                case 'text':
                    $result[$field] = self::sanitizeTextArea($value);
                    break;
                case 'email':
                    $result[$field] = filter_var($value, FILTER_SANITIZE_EMAIL) ?: '';
                    break;
                default:
                    $result[$field] = strip_tags($value);
            }
        }

        return $result;
    }

    private static function sanitizeTextArea(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? $value;
        return trim($value);
    }
}
