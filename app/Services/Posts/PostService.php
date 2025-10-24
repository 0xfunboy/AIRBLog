<?php
declare(strict_types=1);

namespace App\Services\Posts;

use App\Services\Audit\AuditLogger;
use App\Services\Auth\AdminRepository;
use App\Services\Media\MediaStorage;
use DateTimeImmutable;
use DateTimeZone;

final class PostService
{
    private AgentPostRepository $posts;
    private PostSlugGenerator $slugger;
    private ExcerptGenerator $excerptGenerator;
    private MediaStorage $media;
    private AuditLogger $audit;
    private AdminRepository $admins;

    public function __construct(
        ?AgentPostRepository $posts = null,
        ?PostSlugGenerator $slugger = null,
        ?ExcerptGenerator $excerptGenerator = null,
        ?MediaStorage $media = null,
        ?AuditLogger $audit = null,
        ?AdminRepository $admins = null
    ) {
        $this->posts = $posts ?? new AgentPostRepository();
        $this->slugger = $slugger ?? new PostSlugGenerator($this->posts);
        $this->excerptGenerator = $excerptGenerator ?? new ExcerptGenerator();
        $this->media = $media ?? new MediaStorage();
        $this->audit = $audit ?? new AuditLogger();
        $this->admins = $admins ?? new AdminRepository();
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $agent
     * @return array<string,mixed>
     */
    public function create(array $data, array $agent): array
    {
        $postType = $data['post_type'] ?? null;
        if (!$postType || !isset($postType['id'])) {
            throw new \RuntimeException('Invalid post type payload.');
        }

        $imageUrl = null;
        if (!empty($data['image_base64'])) {
            $imageUrl = $this->media->storeBase64((string)$data['image_base64'], (string)($data['title'] ?? 'post'));
        } elseif (!empty($data['image_url'])) {
            $imageUrl = $this->media->validateExternalUrl((string)$data['image_url']);
        }

        $excerpt = $data['excerpt_280'] ?? '';
        if ($excerpt === '' && isset($data['body_html'])) {
            $excerpt = $this->excerptGenerator->fromHtml((string)$data['body_html']);
        }

        $slug = $this->slugger->generate(
            $agent,
            (string)($postType['key'] ?? ''),
            (string)($data['title'] ?? ''),
            $data['ticker'] ?? null,
            $data['timeframe'] ?? null
        );

        $status = $data['publish_mode'] === 'auto' ? 'published' : 'pending';
        $publishedAt = null;
        if ($status === 'published') {
            $publishedAt = $this->now();
        }

        $payload = [
            'agent_id' => $data['agent_id'],
            'post_type_id' => (int)$postType['id'],
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt_280' => $excerpt,
            'body_html' => $data['body_html'],
            'image_url' => $imageUrl,
            'tags' => $data['tags'] ?? null,
            'ticker' => $data['ticker'] ?? null,
            'chain' => $data['chain'] ?? null,
            'timeframe' => $data['timeframe'] ?? null,
            'entry_price' => $data['entry_price'] ?? null,
            'stop_price' => $data['stop_price'] ?? null,
            'target_prices' => $data['target_prices'] ?? null,
            'confidence' => $data['confidence'] ?? null,
            'price_at_post' => $data['price_at_post'] ?? null,
            'publish_mode' => $data['publish_mode'],
            'status' => $status,
            'published_at' => $publishedAt,
        ];

        $postId = $this->posts->create($payload);
        $post = $this->posts->find($postId);
        if (!$post) {
            throw new \RuntimeException('Unable to load post after creation.');
        }

        $actor = 'agent:' . ($agent['slug'] ?? $agent['id'] ?? 'unknown');
        $this->audit->log($actor, 'agent_posts', 'status', null, $status, (string)$postId);

        return $post;
    }

    public function approve(int $postId, int $adminId): array
    {
        $post = $this->posts->find($postId);
        if (!$post) {
            throw new \RuntimeException('Post not found.');
        }

        $admin = $this->admins->findById($adminId);
        if (!$admin) {
            throw new \RuntimeException('Admin not found.');
        }

        if ($post['status'] === 'published') {
            return $post;
        }

        $this->posts->update($postId, [
            'status' => 'published',
            'approved_by_admin_id' => $adminId,
            'published_at' => $this->now(),
        ]);

        $updated = $this->posts->find($postId);
        if (!$updated) {
            throw new \RuntimeException('Unable to reload post after approval.');
        }

        $actor = strtolower((string)$admin['wallet_address'] ?? ('admin:' . $adminId));
        $this->audit->log($actor, 'agent_posts', 'status', (string)$post['status'], 'published', (string)$postId);

        return $updated;
    }

    public function reject(int $postId, int $adminId): array
    {
        $post = $this->posts->find($postId);
        if (!$post) {
            throw new \RuntimeException('Post not found.');
        }

        $admin = $this->admins->findById($adminId);
        if (!$admin) {
            throw new \RuntimeException('Admin not found.');
        }

        if ($post['status'] === 'rejected') {
            return $post;
        }

        $this->posts->update($postId, [
            'status' => 'rejected',
            'approved_by_admin_id' => $adminId,
        ]);

        $updated = $this->posts->find($postId);
        if (!$updated) {
            throw new \RuntimeException('Unable to reload post after rejection.');
        }

        $actor = strtolower((string)$admin['wallet_address'] ?? ('admin:' . $adminId));
        $this->audit->log($actor, 'agent_posts', 'status', (string)$post['status'], 'rejected', (string)$postId);

        return $updated;
    }

    private function now(): string
    {
        $zone = new DateTimeZone(config('app.timezone', 'UTC'));
        return (new DateTimeImmutable('now', $zone))->format('Y-m-d H:i:s');
    }
}
