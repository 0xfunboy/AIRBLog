<?php
declare(strict_types=1);

namespace App\Services\Media;

use DateTimeImmutable;

final class MediaStorage
{
    private const ALLOWED_MIME = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    private string $baseDir;
    private string $baseUrl;
    private int $maxBytes;

    public function __construct()
    {
        $this->baseDir = (string)config('media.directory', BASE_PATH . '/public/media');
        $this->baseUrl = rtrim((string)config('media.url', '/media'), '/');
        $this->maxBytes = (int)config('media.max_upload_bytes', 5 * 1024 * 1024);
    }

    public function storeBase64(string $payload, string $nameHint = 'post'): string
    {
        $decoded = $this->decodeBase64($payload);
        $size = strlen($decoded);
        if ($size === 0) {
            throw new \RuntimeException('Uploaded image payload is empty.');
        }

        if ($this->maxBytes > 0 && $size > $this->maxBytes) {
            throw new \RuntimeException('Uploaded image exceeds the maximum allowed size.');
        }

        $mime = $this->detectMimeType($decoded);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new \RuntimeException('Unsupported image type. Allowed: PNG, JPEG, WEBP.');
        }

        $extension = self::ALLOWED_MIME[$mime];
        $relative = $this->relativePath($extension, $nameHint);
        $absolute = $this->baseDir . '/' . ltrim($relative, '/');

        $directory = dirname($absolute);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create media directory.');
        }

        if (file_put_contents($absolute, $decoded) === false) {
            throw new \RuntimeException('Unable to write media file to disk.');
        }

        @chmod($absolute, 0644);

        return $this->baseUrl . '/' . ltrim($relative, '/');
    }

    public function validateExternalUrl(string $url): string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            throw new \RuntimeException('Image URL is empty.');
        }

        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid image URL.');
        }

        $parsed = parse_url($trimmed);
        if (!in_array(($parsed['scheme'] ?? ''), ['https', 'http'], true)) {
            throw new \RuntimeException('Image URL must use HTTP or HTTPS.');
        }

        return $trimmed;
    }

    private function decodeBase64(string $payload): string
    {
        $payload = trim($payload);

        if (str_starts_with($payload, 'data:')) {
            $parts = explode(',', $payload, 2);
            if (count($parts) === 2) {
                $payload = $parts[1];
            }
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 image payload.');
        }

        return $decoded;
    }

    private function detectMimeType(string $binary): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary);
        if ($mime === false) {
            throw new \RuntimeException('Unable to detect image type.');
        }
        return $mime;
    }

    private function relativePath(string $extension, string $nameHint): string
    {
        $date = new DateTimeImmutable('now', new \DateTimeZone(config('app.timezone', 'UTC')));
        $folder = $date->format('Y/m');
        $slug = $this->slugify($nameHint);
        $suffix = bin2hex(random_bytes(4));

        return $folder . '/' . $slug . '-' . $suffix . '.' . $extension;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'image';
    }
}
