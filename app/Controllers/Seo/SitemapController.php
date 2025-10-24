<?php
declare(strict_types=1);

namespace App\Controllers\Seo;

use App\Core\Controller;
use App\Services\Agents\AgentRepository;
use App\Services\Posts\AgentPostRepository;

final class SitemapController extends Controller
{
    private AgentRepository $agents;
    private AgentPostRepository $posts;

    public function __construct(?AgentRepository $agents = null, ?AgentPostRepository $posts = null)
    {
        $this->agents = $agents ?? new AgentRepository();
        $this->posts = $posts ?? new AgentPostRepository();
    }

    public function index(): void
    {
        $base = rtrim((string)config('app.url', ''), '/');
        $urls = [];

        $urls[] = [
            'loc' => $this->canonical($base, '/'),
            'lastmod' => date('c'),
        ];

        foreach ($this->agents->listAll() as $agent) {
            $slug = '/' . trim((string)$agent['slug'], '/');
            $urls[] = [
                'loc' => $this->canonical($base, $slug),
                'lastmod' => date('c', strtotime((string)($agent['updated_at'] ?? 'now'))),
            ];
            $urls[] = [
                'loc' => $this->canonical($base, $slug . '/signal'),
                'lastmod' => date('c', strtotime((string)($agent['updated_at'] ?? 'now'))),
            ];
            $urls[] = [
                'loc' => $this->canonical($base, $slug . '/news'),
                'lastmod' => date('c', strtotime((string)($agent['updated_at'] ?? 'now'))),
            ];
        }

        foreach ($this->posts->latestPublished(500) as $post) {
            $slug = '/' . ltrim((string)$post['slug'], '/');
            $urls[] = [
                'loc' => $this->canonical($base, $slug),
                'lastmod' => $post['published_at'] ?? $post['updated_at'] ?? $post['created_at'] ?? date('c'),
            ];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo $this->buildXml($urls);
    }

    private function canonical(string $base, string $path): string
    {
        if ($base === '') {
            return $path;
        }
        return $base . $path;
    }

    /**
     * @param array<int,array<string,string>> $urls
     */
    private function buildXml(array $urls): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        foreach ($urls as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $entry['loc']);
            if (!empty($entry['lastmod'])) {
                $url->addChild('lastmod', date('c', strtotime($entry['lastmod'])));
            }
        }

        return $xml->asXML() ?: '';
    }
}
