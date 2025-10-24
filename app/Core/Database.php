<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = Container::get('config');
            if (!$config || !isset($config['database'])) {
                throw new RuntimeException('Database configuration is missing.');
            }

            $db = $config['database'];

            $charset = $db['charset'] ?? 'utf8mb4';
            if (!empty($db['socket'])) {
                $dsn = sprintf(
                    'mysql:unix_socket=%s;dbname=%s;charset=%s',
                    $db['socket'],
                    $db['database'],
                    $charset
                );
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $db['host'] ?? '127.0.0.1',
                    $db['port'] ?? 3306,
                    $db['database'],
                    $charset
                );
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$connection = new PDO($dsn, $db['username'], $db['password'], $options);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
            }

            self::configureTimezone(self::$connection, $config['app']['timezone'] ?? 'UTC');
        }

        return self::$connection;
    }

    private static function configureTimezone(PDO $connection, string $appTimezone): void
    {
        $offset = '+00:00';

        try {
            $zone = new \DateTimeZone($appTimezone);
            $now = new \DateTimeImmutable('now', $zone);
            $seconds = $now->getOffset();
            $sign = $seconds >= 0 ? '+' : '-';
            $seconds = abs($seconds);
            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $offset = sprintf('%s%02d:%02d', $sign, $hours, $minutes);
        } catch (\Throwable $e) {
            // Fallback to UTC if timezone conversion fails.
        }

        try {
            $connection->exec("SET time_zone = '{$offset}'");
        } catch (PDOException $e) {
            // Ignore failure to set timezone; authentication will still function.
        }
    }
}
