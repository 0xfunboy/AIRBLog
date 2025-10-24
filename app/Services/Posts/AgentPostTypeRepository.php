<?php
declare(strict_types=1);

namespace App\Services\Posts;

use App\Models\AgentPostType;

final class AgentPostTypeRepository
{
    private AgentPostType $model;

    public function __construct(?AgentPostType $model = null)
    {
        $this->model = $model ?? new AgentPostType();
    }

    public function findByKey(string $key): ?array
    {
        return $this->model->findByKey($key);
    }

    public function all(): array
    {
        return $this->model->all();
    }

    public function indexedByKey(): array
    {
        return $this->model->allIndexed();
    }
}
