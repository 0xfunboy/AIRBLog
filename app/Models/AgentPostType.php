<?php
declare(strict_types=1);

namespace App\Models;

final class AgentPostType extends Model
{
    protected string $table = 'agent_post_types';

    protected array $fillable = [
        'key',
        'label',
    ];

    public function findByKey(string $key): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE `key` = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $type = $stmt->fetch();

        return $type ?: null;
    }

    public function allIndexed(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        $rows = $stmt->fetchAll() ?: [];

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['key']] = $row;
        }

        return $indexed;
    }
}
