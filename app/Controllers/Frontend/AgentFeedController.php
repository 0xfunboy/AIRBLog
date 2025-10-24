<?php
declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\View;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;
use App\Services\Posts\AgentPostTypeRepository;

final class AgentFeedController extends Controller
{
    private AgentRepository $agents;
    private AgentPostRepository $posts;
    private AgentPostTypeRepository $types;
    private ?array $typeCache = null;

    public function __construct(
        ?AgentRepository $agents = null,
        ?AgentPostRepository $posts = null,
        ?AgentPostTypeRepository $types = null
    ) {
        $this->agents = $agents ?? new AgentRepository();
        $this->posts = $posts ?? new AgentPostRepository();
        $this->types = $types ?? new AgentPostTypeRepository();
    }

    public function index(string $agentSlug): void
    {
        $this->renderFeed($agentSlug, 'all');
    }

    public function filtered(string $agentSlug, string $type): void
    {
        $type = strtolower($type);
        if (!in_array($type, ['signal', 'news'], true)) {
            $this->notFound();
            return;
        }

        $this->renderFeed($agentSlug, $type);
    }

    private function renderFeed(string $agentSlug, string $filter): void
    {
        $agent = $this->agents->findBySlug($agentSlug);
        if (!$agent) {
            $this->notFound();
            return;
        }

        $page = max((int)($_GET['page'] ?? 1), 1);
        $query = trim((string)($_GET['q'] ?? ''));

        $typesByKey = $this->typeMap();
        $postTypeId = null;
        if ($filter !== 'all') {
            $typeRow = $typesByKey[$filter] ?? null;
            if (!$typeRow) {
                $this->notFound();
                return;
            }
            $postTypeId = (int)$typeRow['id'];
        }

        $posts = $this->posts->listByAgent(
            (int)$agent['id'],
            $postTypeId,
            $page,
            20,
            'published',
            $query !== '' ? strtoupper($query) : null
        );

        $pathBase = '/' . trim($agentSlug, '/');
        $filters = [
            [
                'key' => 'all',
                'label' => 'All',
                'href' => $pathBase,
            ],
            [
                'key' => 'signal',
                'label' => 'Signals',
                'href' => $pathBase . '/signal',
            ],
            [
                'key' => 'news',
                'label' => 'News',
                'href' => $pathBase . '/news',
            ],
        ];

        $canonicalPath = $filter === 'all' ? $pathBase : $pathBase . '/' . $filter;
        $metaDescription = $agent['summary'] ?? 'Latest agent updates.';

        View::render('layouts/public', [
            'title' => $agent['name'] . ' feed',
            'contentTemplate' => 'frontend/agent-feed',
            'contentData' => [
                'agent' => $agent,
                'posts' => $this->decoratePosts($posts),
                'filters' => $filters,
                'activeFilter' => $filter,
                'query' => $query,
                'resetHref' => $canonicalPath,
                'meta' => [
                    'description' => $metaDescription,
                    'canonical' => $this->canonical($canonicalPath),
                ],
            ],
        ]);
    }

    private function decoratePosts(array $posts): array
    {
        foreach ($posts as &$post) {
            $post['type_key'] = $this->mapTypeIdToKey((int)$post['post_type_id']);
        }
        return $posts;
    }

    private function mapTypeIdToKey(int $id): string
    {
        $types = $this->typeMap();
        foreach ($types as $key => $row) {
            if ((int)$row['id'] === $id) {
                return (string)$key;
            }
        }
        return 'post';
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function typeMap(): array
    {
        if ($this->typeCache === null) {
            $this->typeCache = $this->types->indexedByKey();
        }

        return $this->typeCache;
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
                    'description' => 'Nothing to see here.',
                    'canonical' => $this->canonical($_SERVER['REQUEST_URI'] ?? '/'),
                ],
            ],
        ]);
    }
}
