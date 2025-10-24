<?php
declare(strict_types=1);

namespace App\Models;

final class Agent extends Model
{
    protected string $table = 'agents';

    protected array $fillable = [
        'name',
        'slug',
        'chain',
        'status',
        'summary',
        'site_url',
        'image_url',
        'badge',
        'featured_order',
    ];

    public function allOrdered(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY featured_order ASC, name ASC"
        );

        return $stmt->fetchAll() ?: [];
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $agent = $stmt->fetch();

        return $agent ?: null;
    }

    public function slugs(): array
    {
        $stmt = $this->db->query("SELECT slug FROM {$this->table}");
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }
}
