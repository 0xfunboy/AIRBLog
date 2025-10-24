<?php
declare(strict_types=1);

namespace App\Services\Posts;

use DateTimeImmutable;
use DateTimeZone;

final class PostSlugGenerator
{
    private AgentPostRepository $posts;

    public function __construct(?AgentPostRepository $posts = null)
    {
        $this->posts = $posts ?? new AgentPostRepository();
    }

    /**
     * @param array<string,mixed> $agent
     */
    public function generate(array $agent, string $typeKey, string $title, ?string $ticker, ?string $timeframe): string
    {
        $agentSlug = (string)($agent['slug'] ?? '');
        if ($agentSlug === '') {
            throw new \RuntimeException('Agent slug is required to generate post slug.');
        }

        $date = new DateTimeImmutable('now', new DateTimeZone(config('app.timezone', 'UTC')));

        $segments = [
            $agentSlug,
            $this->sanitizeType($typeKey),
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
        ];

        $typeKey = strtolower($typeKey);
        if ($typeKey === 'signal') {
            if ($ticker) {
                $segments[] = strtolower(preg_replace('/[^a-z0-9]/i', '', $ticker) ?? '');
            }
            if ($timeframe) {
                $segments[] = strtolower($this->sanitizeTimeframe($timeframe));
            }
        }

        $segments[] = $this->slugifyShortTitle($title);

        $segments = array_values(array_filter($segments, static fn (string $segment) => $segment !== ''));
        $slug = implode('/', $segments);
        $slug = $this->ensureMaxLength($slug, 255);

        return $this->ensureUnique($slug);
    }

    private function sanitizeType(string $type): string
    {
        $normalized = strtolower(trim($type));
        return $normalized !== '' ? $normalized : 'post';
    }

    private function sanitizeTimeframe(string $timeframe): string
    {
        $normalized = strtolower(trim($timeframe));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized) ?? '';

        $map = [
            '1m' => '1m',
            '5m' => '5m',
            '15m' => '15m',
            '30m' => '30m',
            '1h' => '1h',
            '4h' => '4h',
            '6h' => '6h',
            '12h' => '12h',
            '1d' => '1d',
            '1w' => '1w',
        ];

        return $map[$normalized] ?? $normalized;
    }

    private function slugifyShortTitle(string $title): string
    {
        $title = strip_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $words = preg_split('/\s+/', $title) ?: [];
        $words = array_slice($words, 0, 7);
        $candidate = implode(' ', $words);

        return $this->slugify($candidate);
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'post';
    }

    private function ensureUnique(string $slug): string
    {
        $existing = $this->posts->findBySlug($slug);
        if (!$existing) {
            return $slug;
        }

        $base = $slug;
        $suffix = 2;

        $lastSeparator = strrpos($base, '/');
        $prefix = $lastSeparator !== false ? substr($base, 0, $lastSeparator + 1) : '';
        $lastSegment = $lastSeparator !== false ? substr($base, $lastSeparator + 1) : $base;

        $lastSegment = rtrim($lastSegment, '-0123456789');
        if ($lastSegment === '') {
            $lastSegment = 'post';
        }

        do {
            $candidate = $prefix . $lastSegment . '-' . $suffix;
            $candidate = $this->ensureMaxLength($candidate, 255);
            $exists = $this->posts->findBySlug($candidate);
            if (!$exists) {
                return $candidate;
            }
            $suffix++;
        } while ($suffix < 1000);

        throw new \RuntimeException('Unable to generate a unique slug for the post.');
    }

    private function ensureMaxLength(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max);
    }
}
