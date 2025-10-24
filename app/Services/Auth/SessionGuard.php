<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Support\Session;

final class SessionGuard
{
    public function __construct()
    {
        Session::ensureStarted();
    }

    public function check(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public function id(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public function login(int $adminId): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['login_time'] = time();
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_regenerate_id(true);
    }
}
