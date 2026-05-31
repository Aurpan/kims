<?php
namespace App\Models;

use App\Core\Database;
use PDOStatement;

abstract class Model
{
    protected string $table;
    protected Database $db;
    protected array $attributes = [];
    protected array $original = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getConnection(): \PDO
    {
        return $this->db->getConnection();
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function where(string $column, string $operator, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column $operator ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $data,
            'id = ?',
            [$id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->delete(
            $this->table,
            'id = ?',
            [$id]
        );
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = $this->db->fetch($countSql)['total'];

        $sql = "SELECT * FROM {$this->table} LIMIT ? OFFSET ?";
        $items = $this->db->fetchAll($sql, [$perPage, $offset]);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        return $this->db->query($sql, $params);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
