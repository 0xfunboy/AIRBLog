<?php
declare(strict_types=1);

namespace App\Services\Posts;

use App\Core\Database;
use App\Models\AgentPost;
use PDO;

final class AgentPostRepository
{
    private AgentPost $model;

    public function __construct(?AgentPost $model = null)
    {
        $this->model = $model ?? new AgentPost();
    }

    public function create(array $data): int
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->model->findBySlug($slug);
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        return $this->model->findPublishedBySlug($slug);
    }

    public function latestPublished(int $limit = 20): array
    {
        return $this->model->latestPublished($limit);
    }

    public function listByAgent(
        int $agentId,
        ?int $postTypeId = null,
        int $page = 1,
        int $perPage = 20,
        ?string $status = 'published',
        ?string $search = null
    ): array {
        $offset = max($page - 1, 0) * $perPage;

        return $this->model->listByAgent(
            $agentId,
            $postTypeId,
            $perPage,
            $offset,
            $status,
            $search
        );
    }

    public function listPending(int $limit = 50): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT p.*, a.name AS agent_name, a.slug AS agent_slug, t.key AS type_key
             FROM agent_posts p
             INNER JOIN agents a ON a.id = p.agent_id
             INNER JOIN agent_post_types t ON t.id = p.post_type_id
             WHERE p.status = :status
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':status', 'pending', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function listByStatus(?string $status, int $limit = 50): array
    {
        $db = Database::connection();
        $sql = 'SELECT p.*, a.name AS agent_name, a.slug AS agent_slug, t.key AS type_key
                FROM agent_posts p
                INNER JOIN agents a ON a.id = p.agent_id
                INNER JOIN agent_post_types t ON t.id = p.post_type_id';

        $params = [];
        if ($status && $status !== 'all') {
            $sql .= ' WHERE p.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY p.created_at DESC LIMIT :limit';

        $stmt = $db->prepare($sql);
        if (isset($params['status'])) {
            $stmt->bindValue(':status', $params['status'], PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countByStatus(string $status): int
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM agent_posts WHERE status = :status'
        );
        $stmt->execute(['status' => $status]);

        return (int)$stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $db = Database::connection();
        $stmt = $db->query('SELECT COUNT(*) FROM agent_posts');
        return (int)$stmt->fetchColumn();
    }

    public function recent(int $limit = 10): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT p.*, a.name AS agent_name, a.slug AS agent_slug, t.key AS type_key
             FROM agent_posts p
             INNER JOIN agents a ON a.id = p.agent_id
             INNER JOIN agent_post_types t ON t.id = p.post_type_id
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
