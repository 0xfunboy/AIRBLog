<?php
declare(strict_types=1);

namespace App\Support;

final class Flash
{
    private const SESSION_KEY = '_flash';

    public static function set(string $key, string $message): void
    {
        Session::ensureStarted();
        $_SESSION[self::SESSION_KEY][$key] = $message;
    }

    public static function pull(string $key): ?string
    {
        Session::ensureStarted();
        if (!isset($_SESSION[self::SESSION_KEY][$key])) {
            return null;
        }

        $message = $_SESSION[self::SESSION_KEY][$key];
        unset($_SESSION[self::SESSION_KEY][$key]);

        if (empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }

        return $message;
    }
}
