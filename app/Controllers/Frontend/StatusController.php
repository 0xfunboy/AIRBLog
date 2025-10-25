<?php
declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Database;
use App\Core\View;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;

final class StatusController extends Controller
{
    private AgentRepository $agents;
    private AgentPostRepository $posts;

    public function __construct(?AgentRepository $agents = null, ?AgentPostRepository $posts = null)
    {
        $this->agents = $agents ?? new AgentRepository();
        $this->posts = $posts ?? new AgentPostRepository();
    }

    public function show(): void
    {
        $dbOk = true;
        try {
            Database::connection()->query('SELECT 1');
        } catch (\Throwable $exception) {
            $dbOk = false;
        }

        $visibleAgents = $this->agents->listVisible();
        $latestPublished = $this->posts->latestPublished(1);
        $latestPost = $latestPublished[0] ?? null;

        $cards = [
            ['label' => 'Database', 'value' => $dbOk ? 'Connected' : 'Unavailable', 'ok' => $dbOk],
            ['label' => 'Visible Agents', 'value' => (string)count($visibleAgents), 'ok' => count($visibleAgents) > 0],
            ['label' => 'Latest Post', 'value' => $latestPost['title'] ?? 'Awaiting first publish', 'ok' => (bool)$latestPost],
        ];

        View::render('layouts/public', [
            'title' => 'Status',
            'contentTemplate' => 'frontend/status',
            'contentData' => [
                'cards' => $cards,
                'latestPost' => $latestPost,
                'mainSite' => 'https://airewardrop.xyz',
                'meta' => [
                    'description' => 'Live status for AIR Agent Blog services.',
                    'canonical' => $this->canonical('/status'),
                ],
            ],
        ]);
    }

    private function canonical(string $path): string
    {
        $base = rtrim((string)config('app.url', ''), '/');
        return $base !== '' ? $base . $path : $path;
    }
}
