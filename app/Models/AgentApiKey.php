<?php
declare(strict_types=1);

namespace App\Models;

final class AgentApiKey extends Model
{
    protected string $table = 'agent_api_keys';

    protected array $fillable = [
        'agent_id',
        'key_hash',
        'label',
        'is_active',
        'last_used_at',
    ];

    public function findActiveByHash(string $hash): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE key_hash = :hash AND is_active = 1 LIMIT 1"
        );
        $stmt->execute(['hash' => $hash]);
        $record = $stmt->fetch();

        return $record ?: null;
    }

    public function listForAgent(int $agentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE agent_id = :agent ORDER BY created_at DESC"
        );
        $stmt->execute(['agent' => $agentId]);

        return $stmt->fetchAll() ?: [];
    }

    public function deactivateAllForAgent(int $agentId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE agent_id = :agent"
        );
        $stmt->execute(['agent' => $agentId]);
    }

    public function markAsUsed(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET last_used_at = NOW(), updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }
}
