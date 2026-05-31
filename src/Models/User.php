<?php
namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function getActiveUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    public function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    public function activate(int $userId): int
    {
        return $this->db->update(
            $this->table,
            ['is_active' => true],
            'id = ?',
            [$userId]
        );
    }

    public function deactivate(int $userId): int
    {
        return $this->db->update(
            $this->table,
            ['is_active' => false],
            'id = ?',
            [$userId]
        );
    }
}
