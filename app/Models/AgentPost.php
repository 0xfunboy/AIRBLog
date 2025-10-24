<?php
declare(strict_types=1);

namespace App\Models;

final class AgentPost extends Model
{
    protected string $table = 'agent_posts';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'agent_id',
        'post_type_id',
        'title',
        'slug',
        'excerpt_280',
        'body_html',
        'image_url',
        'tags',
        'ticker',
        'chain',
        'timeframe',
        'entry_price',
        'stop_price',
        'target_prices',
        'confidence',
        'price_at_post',
        'publish_mode',
        'status',
        'approved_by_admin_id',
        'published_at',
    ];

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();

        return $post ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'published' LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();

        return $post ?: null;
    }

    public function listByAgent(
        int $agentId,
        ?int $postTypeId = null,
        int $limit = 20,
        int $offset = 0,
        ?string $status = 'published',
        ?string $search = null
    ): array {
        $sql = "SELECT * FROM {$this->table} WHERE agent_id = :agent";
        $params = ['agent' => $agentId];

        if ($postTypeId !== null) {
            $sql .= " AND post_type_id = :type";
            $params['type'] = $postTypeId;
        }

        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (ticker LIKE :search OR title LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY COALESCE(published_at, created_at) DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':agent', $params['agent'], \PDO::PARAM_INT);
        if (isset($params['type'])) {
            $stmt->bindValue(':type', $params['type'], \PDO::PARAM_INT);
        }
        if (isset($params['status'])) {
            $stmt->bindValue(':status', $params['status'], \PDO::PARAM_STR);
        }
        if (isset($params['search'])) {
            $stmt->bindValue(':search', $params['search'], \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function latestPublished(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE status = 'published'
             ORDER BY published_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
