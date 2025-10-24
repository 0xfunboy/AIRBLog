<?php
declare(strict_types=1);

namespace App\Core;

final class Logger
{
    private static string $path;
    private static bool $debug = false;

    public static function init(string $path, bool $debug = false): void
    {
        self::$path = $path;
        self::$debug = $debug;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        if (!isset(self::$path)) {
            return;
        }

        $line = sprintf(
            "[%s] %s %s %s\n",
            date('c'),
            $level,
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents(self::$path, $line, FILE_APPEND);

        if (self::$debug) {
            error_log($line);
        }
    }
}
