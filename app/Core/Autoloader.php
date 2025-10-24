<?php
declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }

            $relative = substr($class, 4);
            $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($path)) {
                require_once $path;
            }
        });
    }
}
