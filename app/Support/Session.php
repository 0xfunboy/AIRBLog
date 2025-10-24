<?php
declare(strict_types=1);

namespace App\Support;

use App\Core\Container;

final class Session
{
    public static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = Container::get('config');
        $sessionName = $config['app']['session_name'] ?? 'aire_session';

        session_name($sessionName);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
    }
}
