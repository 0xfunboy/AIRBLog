<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Services\Auth\SessionGuard;
use Closure;

final class RequireAdmin
{
    public function __invoke(array $params, Closure $next): mixed
    {
        $guard = new SessionGuard();
        if (!$guard->check()) {
            header('Location: /admin/login');
            exit;
        }

        return $next($params);
    }
}
