<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = $path === null ? '/' : $path;
$fullPath = __DIR__ . $path;

if ($path !== '/' && $fullPath !== __FILE__ && is_file($fullPath)) {
    return false;
}

require __DIR__ . '/../app/bootstrap.php';

if (str_starts_with($path, '/admin')) {
    require __DIR__ . '/admin.php';
    return true;
}

if (str_starts_with($path, '/api')) {
    require __DIR__ . '/api.php';
    return true;
}

require __DIR__ . '/index.php';
return true;
