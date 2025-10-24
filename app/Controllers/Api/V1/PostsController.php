<?php
declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Container;
use App\Core\Controller;
use App\Services\Posts\AgentPostRepository;
use App\Services\Posts\AgentPostTypeRepository;
use App\Services\Posts\PostRequestValidator;
use App\Services\Posts\PostService;

final class PostsController extends Controller
{
    private PostRequestValidator $validator;
    private PostService $service;
    private AgentPostTypeRepository $types;
    private AgentPostRepository $posts;

    public function __construct(
        ?PostRequestValidator $validator = null,
        ?PostService $service = null,
        ?AgentPostTypeRepository $types = null,
        ?AgentPostRepository $posts = null
    ) {
        $this->validator = $validator ?? new PostRequestValidator();
        $this->service = $service ?? new PostService();
        $this->types = $types ?? new AgentPostTypeRepository();
        $this->posts = $posts ?? new AgentPostRepository();
    }

    public function store(): void
    {
        $agent = Container::get('api.agent');
        if (!$agent) {
            $this->json(['error' => 'Agent context missing.'], 500);
            return;
        }

        $payload = $this->parseJson();
        if ($payload === null) {
            $this->json(['error' => 'Invalid JSON payload.'], 400);
            return;
        }

        $types = $this->types->indexedByKey();
        $result = $this->validator->validate($payload, $agent, $types);

        if ($result['errors']) {
            $this->json(['errors' => $result['errors']], 422);
            return;
        }

        try {
            $post = $this->service->create($result['data'], $agent);
        } catch (\Throwable $exception) {
            $this->json(['error' => $exception->getMessage()], 500);
            return;
        }

        $url = $this->canonicalUrl($post['slug'] ?? '');

        $this->json([
            'status' => $post['status'],
            'url' => $url,
            'slug' => $post['slug'],
            'id' => (int)$post['id'],
            'excerpt_280' => $post['excerpt_280'],
        ], 201);
    }

    public function show(string $id): void
    {
        $agent = Container::get('api.agent');
        if (!$agent) {
            $this->json(['error' => 'Agent context missing.'], 500);
            return;
        }

        $post = $this->posts->find((int)$id);
        if (!$post || (int)$post['agent_id'] !== (int)$agent['id']) {
            $this->json(['error' => 'Post not found.'], 404);
            return;
        }

        $this->json([
            'id' => (int)$post['id'],
            'status' => $post['status'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'url' => $this->canonicalUrl($post['slug']),
            'excerpt_280' => $post['excerpt_280'],
            'body_html' => $post['body_html'],
            'image_url' => $post['image_url'],
            'ticker' => $post['ticker'],
            'chain' => $post['chain'],
            'timeframe' => $post['timeframe'],
            'published_at' => $post['published_at'],
        ]);
    }

    public function approve(string $id): void
    {
        $adminId = Container::get('api.admin_id');
        if (!$adminId) {
            $this->json(['error' => 'Admin context missing.'], 500);
            return;
        }

        try {
            $post = $this->service->approve((int)$id, (int)$adminId);
        } catch (\Throwable $exception) {
            $status = str_contains(strtolower($exception->getMessage()), 'not found') ? 404 : 400;
            $this->json(['error' => $exception->getMessage()], $status);
            return;
        }

        $this->json([
            'id' => (int)$post['id'],
            'status' => $post['status'],
            'url' => $this->canonicalUrl($post['slug']),
            'published_at' => $post['published_at'],
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function parseJson(): ?array
    {
        $payload = file_get_contents('php://input');
        if ($payload === false) {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function canonicalUrl(string $slug): string
    {
        $slug = ltrim($slug, '/');
        $base = rtrim((string)config('app.url', ''), '/');

        return $base !== '' ? $base . '/' . $slug : '/' . $slug;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
