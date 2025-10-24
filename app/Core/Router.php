<?php
declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<string, array{handler: callable|array, middleware: array<int, callable>}>>
     */
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->register('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->register('POST', $path, $handler, $middleware);
    }

    public function match(array $methods, string $path, callable|array $handler, array $middleware = []): void
    {
        foreach ($methods as $method) {
            $this->register(strtoupper($method), $path, $handler, $middleware);
        }
    }

    private function register(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[$method][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $route = $this->findRoute($method, $path);
        if ($route === null) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404.php';
            return;
        }

        [$handler, $params] = $route;
        $middlewareStack = $handler['middleware'] ?? [];
        $finalHandler = $this->resolveHandler($handler['handler']);

        $pipeline = array_reduce(
            array_reverse($middlewareStack),
            function (Closure $next, callable $middleware) {
                return function (array $params) use ($next, $middleware) {
                    return $middleware($params, $next);
                };
            },
            function (array $params) use ($finalHandler) {
                return $finalHandler(...$params);
            }
        );

        $pipeline($params);
    }

    /**
     * @return array{0: array{handler: callable|array, middleware: array}, 1: array<int, mixed>}|null
     */
    private function findRoute(string $method, string $path): ?array
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $routePath => $handler) {
            $pattern = preg_replace('#\{([^/]+)\}#', '(?P<$1>[^/]+)', $routePath);
            if ($pattern === null) {
                continue;
            }

            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $params = array_filter(
                    $matches,
                    fn ($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                return [$handler, array_values($params)];
            }
        }

        return null;
    }

    private function resolveHandler(callable|array $handler): Closure
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            if (is_string($class)) {
                $class = new $class();
            }

            if (!method_exists($class, $method)) {
                throw new RuntimeException(sprintf('Handler %s::%s does not exist.', $class::class, $method));
            }

            return fn (...$params) => $class->$method(...$params);
        }

        return fn (...$params) => $handler(...$params);
    }
}
