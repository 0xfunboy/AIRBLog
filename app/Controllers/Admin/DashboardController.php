<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentApiKeyRepository;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;

final class DashboardController extends Controller
{
    private AgentPostRepository $posts;
    private AgentRepository $agents;
    private AgentApiKeyRepository $keys;

    public function __construct(
        ?AgentPostRepository $posts = null,
        ?AgentRepository $agents = null,
        ?AgentApiKeyRepository $keys = null
    )
    {
        $this->posts = $posts ?? new AgentPostRepository();
        $this->agents = $agents ?? new AgentRepository();
        $this->keys = $keys ?? new AgentApiKeyRepository();
    }

    public function landing(): void
    {
        $this->redirect('/admin/dashboard');
    }

    public function index(): void
    {
        $stats = [
            'total' => $this->posts->countAll(),
            'published' => $this->posts->countByStatus('published'),
            'pending' => $this->posts->countByStatus('pending'),
        ];

        $recent = $this->posts->recent(8);
        $guideAgents = [];
        $endpoint = rtrim((string)config('app.url', ''), '/') . '/api/v1/posts';

        foreach ($this->agents->listAll() as $agent) {
            $latestKey = $this->keys->latestActiveForAgent((int)$agent['id']);
            $guideAgents[] = [
                'id' => (int)$agent['id'],
                'name' => $agent['name'],
                'slug' => $agent['slug'],
                'plain_token' => $latestKey['plain_token'] ?? null,
                'guide_url' => '/admin/api-keys/' . (int)$agent['id'],
            ];
        }

        View::render('layouts/admin', [
            'title' => 'Dashboard',
            'contentTemplate' => 'admin/dashboard',
            'contentData' => [
                'stats' => $stats,
                'recentPosts' => $recent,
                'guideAgents' => $guideAgents,
                'apiEndpoint' => $endpoint,
            ],
        ]);
    }
}
