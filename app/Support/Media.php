<?php
declare(strict_types=1);

namespace App\Support;

final class Media
{
    private const PUBLIC_DIR = '/public';
    private const MEDIA_SVG_BASE = '/media/svg';
    private const DEFAULT_SVG_BASE = '/assets/svg-default';
    private const DEFAULT_PLACEHOLDER = 'placeholder.svg';

    /**
     * Resolve a public URL for an SVG asset, automatically falling back to the
     * versioned default copy when the customised media file is missing.
     */
    public static function assetSvg(string $relativePath, ?string $fallbackRelativePath = null): string
    {
        $candidate = self::stripKnownPrefixes(self::sanitizeRelative($relativePath));
        foreach (self::expandCandidates($candidate) as $option) {
            $mediaCandidate = self::MEDIA_SVG_BASE . '/' . $option;
            if (self::fileExists($mediaCandidate)) {
                return self::publicUrl($mediaCandidate);
            }
        }

        $fallbackRelative = $fallbackRelativePath !== null
            ? self::stripKnownPrefixes(self::sanitizeRelative($fallbackRelativePath))
            : ($candidate !== '' ? $candidate : '');

        foreach (self::expandCandidates($fallbackRelative) as $option) {
            $defaultCandidate = self::DEFAULT_SVG_BASE . '/' . $option;
            if (self::fileExists($defaultCandidate)) {
                return self::publicUrl($defaultCandidate);
            }
        }

        $placeholder = self::DEFAULT_SVG_BASE . '/' . self::DEFAULT_PLACEHOLDER;
        if (self::fileExists($placeholder)) {
            return self::publicUrl($placeholder);
        }

        return self::publicUrl(self::DEFAULT_SVG_BASE . '/' . self::DEFAULT_PLACEHOLDER);
    }

    /**
     * Return the on-disk path for an SVG asset or null when nothing is found.
     */
    public static function resolveSvgPath(string $relativePath, ?string $fallbackRelativePath = null): ?string
    {
        $candidate = self::stripKnownPrefixes(self::sanitizeRelative($relativePath));
        foreach (self::expandCandidates($candidate) as $option) {
            $mediaCandidate = self::absolutePath(self::MEDIA_SVG_BASE . '/' . $option);
            if ($mediaCandidate !== null && is_file($mediaCandidate)) {
                return $mediaCandidate;
            }
        }

        $fallbackRelative = $fallbackRelativePath !== null
            ? self::stripKnownPrefixes(self::sanitizeRelative($fallbackRelativePath))
            : ($candidate !== '' ? $candidate : '');

        foreach (self::expandCandidates($fallbackRelative) as $option) {
            $defaultCandidate = self::absolutePath(self::DEFAULT_SVG_BASE . '/' . $option);
            if ($defaultCandidate !== null && is_file($defaultCandidate)) {
                return $defaultCandidate;
            }
        }

        $placeholder = self::absolutePath(self::DEFAULT_SVG_BASE . '/' . self::DEFAULT_PLACEHOLDER);
        return $placeholder !== null && is_file($placeholder) ? $placeholder : null;
    }

    /**
     * Convert a relative /media path to the canonical form used by settings.
     */
    public static function normalizeMediaPath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $sanitized = self::sanitizeRelative($trimmed);
        if ($sanitized === '') {
            return '';
        }

        if (str_starts_with($sanitized, 'media/')) {
            return '/' . $sanitized;
        }

        if (str_starts_with($sanitized, 'assets/svg-default/')) {
            return '/' . $sanitized;
        }

        return self::MEDIA_SVG_BASE . '/' . $sanitized;
    }

    public static function siteLogoUrl(?string $value): string
    {
        $trimmed = trim((string)$value);
        if ($trimmed !== '') {
            if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
                return $trimmed;
            }

            $publicPath = '/' . ltrim($trimmed, '/');
            $absolute = self::absolutePath($publicPath);
            if ($absolute !== null && is_file($absolute)) {
                return $publicPath;
            }
        }

        return self::assetSvg('logo/site-logo.svg');
    }

    public static function promoteToSvgLibrary(string $relativePath, string $targetBasename): ?string
    {
        $normalized = self::normalizeMediaPath($relativePath);
        $source = dirname(__DIR__, 2) . '/public/' . ltrim($normalized, '/');

        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'svg';
        }

        $targetBase = trim($targetBasename, '/');
        if ($targetBase === '') {
            $targetBase = 'site-logo';
        }

        $targetRelative = 'media/svg/' . $targetBase . '.' . $extension;
        $targetAbsolute = dirname(__DIR__, 2) . '/public/' . $targetRelative;

        $targetDir = dirname($targetAbsolute);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return null;
        }

        $pattern = $targetDir . '/' . basename($targetBase) . '.*';
        foreach (glob($pattern) ?: [] as $existing) {
            if (is_file($existing)) {
                @unlink($existing);
            }
        }

        if (!copy($source, $targetAbsolute)) {
            return null;
        }

        @chmod($targetAbsolute, 0644);
        return '/' . ltrim($targetRelative, '/');
    }

    private static function sanitizeRelative(string $relativePath): string
    {
        $relativePath = str_replace(["\\", "\0"], ['/', ''], trim($relativePath));
        $segments = array_filter(explode('/', $relativePath), static function (string $segment) {
            return $segment !== '' && $segment !== '.' && $segment !== '..';
        });

        return implode('/', $segments);
    }

    private static function stripKnownPrefixes(string $relative): string
    {
        $relative = ltrim($relative, '/');
        if ($relative === '') {
            return '';
        }

        if (str_starts_with($relative, 'media/svg/')) {
            $relative = substr($relative, strlen('media/svg/')) ?: '';
        }

        if (str_starts_with($relative, 'assets/svg-default/')) {
            $relative = substr($relative, strlen('assets/svg-default/')) ?: '';
        }

        return $relative;
    }

    /**
     * @return array<int,string>
     */
    private static function expandCandidates(string $relative): array
    {
        $relative = ltrim($relative, '/');
        if ($relative === '') {
            return [];
        }

        $candidates = [];
        $info = pathinfo($relative);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        $extension = strtolower($info['extension'] ?? '');

        if ($filename === '') {
            $filename = $relative;
            $dir = '';
        }

        $base = $dir !== '' && $dir !== '.' ? $dir . '/' . $filename : $filename;

        $preferred = ['svg', 'png', 'webp'];

        if ($extension === '') {
            foreach ($preferred as $ext) {
                $candidates[] = $base . '.' . $ext;
            }
        } else {
            $candidates[] = $base . '.' . $extension;
            foreach ($preferred as $ext) {
                if ($ext === $extension) {
                    continue;
                }
                $candidates[] = $base . '.' . $ext;
            }
        }

        return array_values(array_unique($candidates));
    }

    private static function publicUrl(string $publicPath): string
    {
        return '/' . ltrim($publicPath, '/');
    }

    private static function fileExists(string $publicPath): bool
    {
        $absolute = self::absolutePath($publicPath);
        return $absolute !== null && is_file($absolute);
    }

    private static function absolutePath(string $publicPath): ?string
    {
        $root = dirname(__DIR__, 2) . self::PUBLIC_DIR;
        $normalized = '/' . ltrim($publicPath, '/');
        $full = $root . $normalized;

        $real = realpath($full);
        if ($real === false) {
            return null;
        }

        if (!str_starts_with($real, $root)) {
            return null;
        }

        return $real;
    }
}
