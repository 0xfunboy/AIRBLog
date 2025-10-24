<?php
declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;

final class PostController extends Controller
{
    private AgentPostRepository $posts;
    private AgentRepository $agents;

    public function __construct(?AgentPostRepository $posts = null, ?AgentRepository $agents = null)
    {
        $this->posts = $posts ?? new AgentPostRepository();
        $this->agents = $agents ?? new AgentRepository();
    }

    public function show(string $agent, string $type, string $year, string $month, string $day, string $slug): void
    {
        $path = sprintf('%s/%s/%s/%s/%s/%s', $agent, $type, $year, $month, $day, $slug);
        $post = $this->posts->findPublishedBySlug($path);
        if (!$post) {
            $this->notFound();
            return;
        }

        $agentRecord = $this->agents->findBySlug(strtolower($agent));
        if (!$agentRecord) {
            $this->notFound();
            return;
        }

        $canonical = $this->canonical('/' . $path);
        $description = $post['excerpt_280'] ?? ($agentRecord['summary'] ?? '');

        View::render('layouts/public', [
            'title' => $post['title'],
            'contentTemplate' => 'frontend/post',
            'contentData' => [
                'post' => $post,
                'agent' => $agentRecord,
                'meta' => [
                    'description' => $description,
                    'canonical' => $canonical,
                    'image' => $post['image_url'] ?? '',
                ],
            ],
        ]);
    }

    private function canonical(string $path): string
    {
        $base = rtrim((string)config('app.url', ''), '/');
        return $base !== '' ? $base . $path : $path;
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('layouts/public', [
            'title' => 'Not found',
            'contentTemplate' => 'frontend/not-found',
            'contentData' => [
                'meta' => [
                    'description' => 'This post is unavailable.',
                    'canonical' => $this->canonical($_SERVER['REQUEST_URI'] ?? '/'),
                ],
            ],
        ]);
    }
}
