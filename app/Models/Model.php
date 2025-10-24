<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $attributes): int
    {
        $attributes = $this->filterFillable($attributes);
        $columns = array_keys($attributes);
        $placeholders = array_map(fn ($column) => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($attributes);

        return (int)$this->db->lastInsertId();
    }

    public function update(int|string $id, array $attributes): bool
    {
        $attributes = $this->filterFillable($attributes);
        $columns = array_keys($attributes);
        $assignments = implode(', ', array_map(fn ($column) => "{$column} = :{$column}", $columns));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :id',
            $this->table,
            $assignments,
            $this->primaryKey
        );

        $stmt = $this->db->prepare($sql);
        $attributes['id'] = $id;

        return $stmt->execute($attributes);
    }

    public function delete(int|string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    protected function filterFillable(array $attributes): array
    {
        if (empty($this->fillable)) {
            return $attributes;
        }

        return array_intersect_key($attributes, array_flip($this->fillable));
    }
}
