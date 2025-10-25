<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\Admin\ApiKeysController;
use App\Controllers\Admin\AgentsController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PostApprovalsController;
use App\Controllers\AuthController;
use App\Core\Router;
use App\Middleware\RequireAdmin;

$router = new Router();

$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->get('/admin/auth/nonce', [AuthController::class, 'issueNonce']);
$router->post('/admin/auth/verify', [AuthController::class, 'verify']);
$router->post('/admin/auth/logout', [AuthController::class, 'logout']);

$requireAdmin = new RequireAdmin();

$router->get('/admin', [DashboardController::class, 'landing'], [$requireAdmin]);
$router->get('/admin/dashboard', [DashboardController::class, 'index'], [$requireAdmin]);

$router->get('/admin/posts', [PostApprovalsController::class, 'index'], [$requireAdmin]);
$router->post('/admin/posts/{id}/approve', [PostApprovalsController::class, 'approve'], [$requireAdmin]);
$router->post('/admin/posts/{id}/reject', [PostApprovalsController::class, 'reject'], [$requireAdmin]);

$router->get('/admin/agents', [AgentsController::class, 'index'], [$requireAdmin]);
$router->post('/admin/agents', [AgentsController::class, 'store'], [$requireAdmin]);
$router->post('/admin/agents/{id}', [AgentsController::class, 'update'], [$requireAdmin]);

$router->get('/admin/api-keys', [ApiKeysController::class, 'index'], [$requireAdmin]);
$router->post('/admin/api-keys/rotate', [ApiKeysController::class, 'rotate'], [$requireAdmin]);
$router->get('/admin/api-keys/{id}', [ApiKeysController::class, 'show'], [$requireAdmin]);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
