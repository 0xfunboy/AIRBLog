<?php
declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;

final class SitemapPageController extends Controller
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
        $totalAgents = count($this->agents->listAll());
        $totalPosts = $this->posts->countByStatus('published');
        $base = rtrim((string)config('app.url', ''), '/');

        View::render('layouts/public', [
            'title' => 'Sitemap',
            'contentTemplate' => 'frontend/sitemap',
            'contentData' => [
                'totalAgents' => $totalAgents,
                'totalPosts' => $totalPosts,
                'sitemapUrl' => ($base !== '' ? $base : '') . '/sitemap.xml',
                'mainSite' => 'https://airewardrop.xyz',
                'meta' => [
                    'description' => 'Overview of the AIR Agent Blog sitemap and indexing endpoints.',
                    'canonical' => $this->canonical('/sitemap-info'),
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
