<?php
declare(strict_types=1);

if (defined('APP_BOOTSTRAPPED')) {
    return;
}
define('APP_BOOTSTRAPPED', true);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Core/Autoloader.php';

\App\Core\Autoloader::register();

$configPath = BASE_PATH . '/.env.php';
if (!file_exists($configPath)) {
    throw new RuntimeException('Missing .env.php configuration file. Copy .env.example.php and update credentials.');
}

$config = require $configPath;

date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

\App\Core\Container::set('config', $config);

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $value = \App\Core\Container::get('config', []);
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

$GLOBALS['config'] = $config;

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    $sessionName = $config['app']['session_name'] ?? 'aire_session';
    session_name($sessionName);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

\App\Core\Logger::init(BASE_PATH . '/storage/logs/app.log', (bool)($config['app']['debug'] ?? false));

try {
$GLOBALS['pdo'] = \App\Core\Database::connection();
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'DB error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
