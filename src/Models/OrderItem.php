<?php
namespace App\Models;

class OrderItem extends Model
{
    protected string $table = 'order_items';

    public function getByOrder(int $orderId): array
    {
        $sql = "SELECT oi.*, p.name as product_name, pv.size, pv.sku
                FROM {$this->table} oi
                JOIN products p ON oi.product_id = p.id
                JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.order_id = ?";
        return $this->db->fetchAll($sql, [$orderId]);
    }

    public function getOrderTotal(int $orderId): float
    {
        $sql = "SELECT SUM(line_total) as total FROM {$this->table} WHERE order_id = ?";
        $result = $this->db->fetch($sql, [$orderId]);
        return (float) ($result['total'] ?? 0);
    }

    public function getTotalQuantity(int $orderId): int
    {
        $sql = "SELECT SUM(quantity) as total FROM {$this->table} WHERE order_id = ?";
        $result = $this->db->fetch($sql, [$orderId]);
        return (int) ($result['total'] ?? 0);
    }
}
