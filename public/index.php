<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;
use App\Controllers\Frontend\HomeController;
use App\Controllers\Frontend\AgentFeedController;
use App\Controllers\Frontend\PostController;
use App\Controllers\Seo\SitemapController;
use App\Controllers\Seo\RobotsController;
use App\Controllers\System\HealthController;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/healthz', [HealthController::class, 'show']);
$router->get('/sitemap.xml', [SitemapController::class, 'index']);
$router->get('/robots.txt', [RobotsController::class, 'index']);

$router->get('/{agent}', [AgentFeedController::class, 'index']);
$router->get('/{agent}/{type}', [AgentFeedController::class, 'filtered']);
$router->get('/{agent}/{type}/{year}/{month}/{day}/{slug}', [PostController::class, 'show']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
