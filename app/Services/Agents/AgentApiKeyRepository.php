<?php
declare(strict_types=1);

namespace App\Services\Agents;

use App\Core\Database;
use App\Models\AgentApiKey;
use PDO;

final class AgentApiKeyRepository
{
    private AgentApiKey $model;

    public function __construct(?AgentApiKey $model = null)
    {
        $this->model = $model ?? new AgentApiKey();
    }

    public function listForAgent(int $agentId): array
    {
        return $this->model->listForAgent($agentId);
    }

    public function findActiveByToken(string $token): ?array
    {
        $hash = $this->hashToken($token);
        return $this->model->findActiveByHash($hash);
    }

    public function create(int $agentId, string $token, ?string $label = null): array
    {
        $hash = $this->hashToken($token);
        $id = $this->model->create([
            'agent_id' => $agentId,
            'key_hash' => $hash,
            'plain_token' => $token,
            'label' => $label,
            'is_active' => 1,
        ]);

        return [
            'id' => $id,
            'token' => $token,
        ];
    }

    public function rotate(int $agentId, ?string $label = null): array
    {
        $token = bin2hex(random_bytes(32));

        $this->model->deactivateAllForAgent($agentId);

        return $this->create($agentId, $token, $label);
    }

    public function markUsed(int $id): void
    {
        $this->model->markAsUsed($id);
    }

    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function deactivate(int $id): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE agent_api_keys SET is_active = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function latestActiveForAgent(int $agentId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT * FROM agent_api_keys WHERE agent_id = :agent AND is_active = 1 ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['agent' => $agentId]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }
}
