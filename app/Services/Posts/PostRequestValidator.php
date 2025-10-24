<?php
declare(strict_types=1);

namespace App\Services\Posts;

use App\Support\HtmlSanitizer;

final class PostRequestValidator
{
    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $agent
     * @param array<string,array<string,mixed>> $postTypesByKey
     * @return array{data:array<string,mixed>,errors:array<int,string>}
     */
    public function validate(array $payload, array $agent, array $postTypesByKey): array
    {
        $errors = [];
        $data = [
            'agent_id' => (int)($agent['id'] ?? 0),
        ];

        $agentSlug = (string)($agent['slug'] ?? '');
        if (isset($payload['agent_slug']) && $agentSlug !== '' && strtolower((string)$payload['agent_slug']) !== strtolower($agentSlug)) {
            $errors[] = 'agent_slug does not match the authenticated token.';
        }

        if (isset($payload['agent_id']) && (int)$payload['agent_id'] !== $data['agent_id']) {
            $errors[] = 'agent_id does not match the authenticated token.';
        }

        $typeKey = strtolower(trim((string)($payload['type'] ?? '')));
        if ($typeKey === '' || !isset($postTypesByKey[$typeKey])) {
            $errors[] = 'type must be one of: ' . implode(', ', array_keys($postTypesByKey));
        } else {
            $data['post_type'] = $postTypesByKey[$typeKey];
        }

        $title = trim((string)($payload['title'] ?? ''));
        if ($title === '') {
            $errors[] = 'title is required.';
        } elseif (mb_strlen($title) > 200) {
            $errors[] = 'title must be at most 200 characters.';
        } else {
            $data['title'] = $title;
        }

        $rawBody = (string)($payload['body_html'] ?? '');
        $sanitizedBody = HtmlSanitizer::sanitize($rawBody);
        if ($sanitizedBody === '') {
            $errors[] = 'body_html is required.';
        } else {
            $data['body_html'] = $sanitizedBody;
        }

        $publishMode = strtolower(trim((string)($payload['publish_mode'] ?? 'auto')));
        if (!in_array($publishMode, ['auto', 'needs_approval'], true)) {
            $errors[] = 'publish_mode must be auto or needs_approval.';
        } else {
            $data['publish_mode'] = $publishMode;
        }

        $ticker = strtoupper(trim((string)($payload['ticker'] ?? '')));
        $chain = trim((string)($payload['chain'] ?? ''));
        $timeframe = trim((string)($payload['timeframe'] ?? ''));

        if (($data['post_type']['key'] ?? '') === 'signal') {
            if ($ticker === '') {
                $errors[] = 'ticker is required for signal posts.';
            }
            if ($chain === '') {
                $errors[] = 'chain is required for signal posts.';
            }
            if ($timeframe === '') {
                $errors[] = 'timeframe is required for signal posts.';
            }
        }

        $data['ticker'] = $ticker !== '' ? $ticker : null;
        $data['chain'] = $chain !== '' ? $chain : null;
        $data['timeframe'] = $timeframe !== '' ? $timeframe : null;

        $excerpt = trim((string)($payload['excerpt_280'] ?? ''));
        if ($excerpt !== '' && mb_strlen($excerpt) > 280) {
            $excerpt = mb_substr($excerpt, 0, 280);
        }
        $data['excerpt_280'] = $excerpt;

        $tags = $payload['tags'] ?? null;
        if (is_array($tags)) {
            $normalizedTags = [];
            foreach ($tags as $tag) {
                $tag = trim((string)$tag);
                if ($tag !== '') {
                    $normalizedTags[] = $tag;
                }
            }
            $data['tags'] = $normalizedTags ? implode(',', $normalizedTags) : null;
        } elseif (is_string($tags)) {
            $parts = array_filter(array_map('trim', explode(',', $tags)));
            $data['tags'] = $parts ? implode(',', $parts) : null;
        } else {
            $data['tags'] = null;
        }

        $data['entry_price'] = $this->toDecimal($payload['entry'] ?? null);
        $data['stop_price'] = $this->toDecimal($payload['stop'] ?? null);
        $data['price_at_post'] = $this->toDecimal($payload['price_at_post'] ?? null);

        $targets = $payload['targets'] ?? null;
        if (is_array($targets)) {
            $targets = array_values(array_filter(array_map([$this, 'toDecimal'], $targets), static fn ($value) => $value !== null));
            if ($targets) {
                try {
                    $data['target_prices'] = json_encode($targets, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    $errors[] = 'Unable to encode targets payload.';
                    $data['target_prices'] = null;
                }
            } else {
                $data['target_prices'] = null;
            }
        } else {
            $data['target_prices'] = null;
        }

        $confidence = $payload['confidence'] ?? null;
        if ($confidence !== null) {
            $confidence = (int)$confidence;
            if ($confidence < 0 || $confidence > 100) {
                $errors[] = 'confidence must be between 0 and 100.';
            } else {
                $data['confidence'] = $confidence;
            }
        } else {
            $data['confidence'] = null;
        }

        $imageBase64 = $payload['image_base64'] ?? null;
        if (is_string($imageBase64) && trim($imageBase64) !== '') {
            $data['image_base64'] = $imageBase64;
        }

        $imageUrl = $payload['image_url'] ?? null;
        if (is_string($imageUrl) && trim($imageUrl) !== '') {
            $data['image_url'] = $imageUrl;
        }

        if (empty($data['image_base64']) && empty($data['image_url'])) {
            $errors[] = 'Provide image_base64 or image_url for the post image.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    private function toDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $float = (float)$value;
        } else {
            $sanitized = str_replace(',', '.', (string)$value);
            if (!is_numeric($sanitized)) {
                return null;
            }
            $float = (float)$sanitized;
        }

        $formatted = number_format($float, 8, '.', '');
        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}
