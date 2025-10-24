<?php
declare(strict_types=1);

namespace App\Support;

use App\Services\Auth\AdminRepository;
use App\Services\Auth\SessionGuard;

final class AdminMode
{
    private const SESSION_FLAG = '_admin_mode_enabled';
    private const SESSION_WALLET = 'admin_wallet';
    private const SESSION_THROTTLE = '_admin_throttle';

    private static ?bool $cache = null;

    public static function isAdmin(): bool
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $guard = new SessionGuard();
        if (!$guard->check()) {
            self::$cache = false;
            return false;
        }

        $wallet = self::wallet();
        if ($wallet === null) {
            self::$cache = false;
            return false;
        }

        $repository = new AdminRepository();
        $admin = $repository->findByWallet($wallet);
        self::$cache = $admin !== null;

        return self::$cache;
    }

    public static function wallet(): ?string
    {
        Session::ensureStarted();
        $wallet = $_SESSION[self::SESSION_WALLET] ?? null;
        if (!is_string($wallet) || $wallet === '') {
            return null;
        }
        return strtolower($wallet);
    }

    public static function setWallet(string $address): void
    {
        Session::ensureStarted();
        $_SESSION[self::SESSION_WALLET] = strtolower($address);
        self::$cache = null;
    }

    public static function enable(): void
    {
        Session::ensureStarted();
        $_SESSION[self::SESSION_FLAG] = true;
    }

    public static function disable(): void
    {
        Session::ensureStarted();
        $_SESSION[self::SESSION_FLAG] = false;
    }

    public static function isEnabled(): bool
    {
        Session::ensureStarted();
        return self::isAdmin() && !empty($_SESSION[self::SESSION_FLAG]);
    }

    public static function dataAttrs(string $model, string $key, ?string $id = null, string $type = 'text'): string
    {
        if (!self::isAdmin()) {
            return '';
        }

        $attrs = sprintf(
            ' data-model="%s" data-key="%s" data-field-type="%s"',
            htmlspecialchars($model, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($type, ENT_QUOTES, 'UTF-8')
        );

        if ($id !== null && $id !== '') {
            $attrs .= ' data-id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $attrs;
    }

    /**
     * Basic rate limiter for admin API calls (per session bucket).
     */
    public static function throttle(string $bucket, int $limit = 60, int $seconds = 60): bool
    {
        Session::ensureStarted();
        $now = time();
        $records = $_SESSION[self::SESSION_THROTTLE][$bucket] ?? [];
        $records = array_filter((array)$records, static fn ($ts) => is_int($ts) && ($now - $ts) < $seconds);

        if (count($records) >= $limit) {
            return false;
        }

        $records[] = $now;
        $_SESSION[self::SESSION_THROTTLE][$bucket] = $records;
        return true;
    }
}
