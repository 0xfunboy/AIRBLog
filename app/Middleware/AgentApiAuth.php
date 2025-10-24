<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Container;
use App\Services\Agents\AgentApiKeyRepository;
use App\Services\Agents\AgentRepository;
use App\Support\RateLimiter;
use Closure;

final class AgentApiAuth
{
    private const RATE_LIMIT_PREFIX = 'agent_token:';

    public function __invoke(array $params, Closure $next): mixed
    {
        $token = $this->extractBearerToken();
        if ($token === null) {
            $this->deny(401, 'Missing bearer token.');
        }

        $apiKeys = new AgentApiKeyRepository();
        $record = $apiKeys->findActiveByToken($token);
        if (!$record) {
            $this->deny(401, 'Invalid or inactive API token.');
        }

        $agents = new AgentRepository();
        $agent = $agents->findById((int)$record['agent_id']);
        if (!$agent) {
            $this->deny(401, 'Associated agent not found.');
        }

        $limitWindow = (int)config('security.rate_limit_window', 60);
        $limitMax = (int)config('security.rate_limit_max', 120);
        if ($limitWindow > 0 && $limitMax > 0) {
            $bucket = self::RATE_LIMIT_PREFIX . $record['id'];
            if (!RateLimiter::hit($bucket, $limitMax, $limitWindow)) {
                $this->deny(429, 'Rate limit exceeded. Try again later.');
            }
        }

        $apiKeys->markUsed((int)$record['id']);

        Container::set('api.agent', $agent);
        Container::set('api.api_key', $record);
        Container::set('api.token', $token);

        return $next($params);
    }

    private function extractBearerToken(): ?string
    {
        $header = $this->authorizationHeader();
        if ($header === null) {
            return null;
        }

        if (!preg_match('/^Bearer\\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        $token = trim($matches[1]);
        return $token !== '' ? $token : null;
    }

    private function authorizationHeader(): ?string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim((string)$_SERVER['HTTP_AUTHORIZATION']);
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, 'Authorization') === 0) {
                    return trim((string)$value);
                }
            }
        }

        return null;
    }

    private function deny(int $status, string $message): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_THROW_ON_ERROR);
        exit;
    }
}
