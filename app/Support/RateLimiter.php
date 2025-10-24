<?php
declare(strict_types=1);

namespace App\Support;

final class RateLimiter
{
    private const DIRECTORY = '/storage/cache/ratelimits';

    public static function hit(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $now = time();
        $timeline = self::readTimeline($key);
        $timeline = array_filter(
            $timeline,
            static fn (int $timestamp) => ($now - $timestamp) < $decaySeconds
        );

        if (count($timeline) >= $maxAttempts) {
            return false;
        }

        $timeline[] = $now;
        self::writeTimeline($key, $timeline);
        return true;
    }

    public static function clear(string $key): void
    {
        $path = self::filePath($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * @return array<int,int>
     */
    private static function readTimeline(string $key): array
    {
        $path = self::filePath($key);
        if (!is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }

    /**
     * @param array<int,int> $timeline
     */
    private static function writeTimeline(string $key, array $timeline): void
    {
        $path = self::filePath($key);
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create rate limit cache directory.');
        }

        $tempPath = $path . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $payload = json_encode(array_values($timeline), JSON_THROW_ON_ERROR);

        if (file_put_contents($tempPath, $payload, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to persist rate limit cache.');
        }

        @chmod($tempPath, 0644);
        @rename($tempPath, $path);
    }

    private static function filePath(string $key): string
    {
        $hashed = hash('sha256', $key);
        return BASE_PATH . self::DIRECTORY . '/' . substr($hashed, 0, 2) . '/' . $hashed . '.json';
    }
}
