<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\Api\V1\PostsController;
use App\Core\Router;
use App\Middleware\AdminSessionApiGuard;
use App\Middleware\AgentApiAuth;

$router = new Router();

$agentAuth = new AgentApiAuth();
$adminGuard = new AdminSessionApiGuard();

$router->post('/api/v1/posts', [PostsController::class, 'store'], [$agentAuth]);
$router->get('/api/v1/posts/{id}', [PostsController::class, 'show'], [$agentAuth]);
$router->post('/api/v1/posts/{id}/approve', [PostsController::class, 'approve'], [$adminGuard]);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
