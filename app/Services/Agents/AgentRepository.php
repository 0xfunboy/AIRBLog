<?php
declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\Agent;

final class AgentRepository
{
    private Agent $agent;

    public function __construct(?Agent $agent = null)
    {
        $this->agent = $agent ?? new Agent();
    }

    public function listAll(): array
    {
        return $this->agent->allOrdered();
    }

    public function findById(int $id): ?array
    {
        return $this->agent->find($id);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->agent->findBySlug($slug);
    }

    public function create(array $data): int
    {
        return $this->agent->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->agent->update($id, $data);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $slugs = $this->agent->slugs();
        if ($ignoreId !== null) {
            $current = $this->agent->find($ignoreId);
            if ($current && $current['slug'] === $slug) {
                return false;
            }
        }

        return in_array($slug, $slugs, true);
    }
}
