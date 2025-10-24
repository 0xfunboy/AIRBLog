<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Container;
use App\Services\Auth\SessionGuard;
use Closure;

final class AdminSessionApiGuard
{
    public function __invoke(array $params, Closure $next): mixed
    {
        $guard = new SessionGuard();
        if (!$guard->check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Authentication required.'], JSON_THROW_ON_ERROR);
            exit;
        }

        Container::set('api.admin_id', $guard->id());

        return $next($params);
    }
}
