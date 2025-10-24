<?php
declare(strict_types=1);

namespace App\Core;

final class Container
{
    /**
     * @var array<string, mixed>
     */
    private static array $items = [];

    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }
}
