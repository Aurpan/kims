<?php
namespace App\Models;

class OrderItem extends Model
{
    protected string $table = 'order_items';

    public function getByOrder(int $orderId): array
    {
        $sql = "SELECT oi.*, p.name as product_name, pv.size, pv.sku, pv.stock AS current_stock
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

    public function getStockShortages(): array
    {
        $sql = "SELECT
                    p.id          AS product_id,
                    p.name        AS product_name,
                    pv.id         AS variant_id,
                    pv.size,
                    pv.sku,
                    pv.stock      AS current_stock,
                    SUM(oi.quantity)              AS required_qty,
                    SUM(oi.quantity) - pv.stock   AS shortage,
                    COUNT(DISTINCT oi.order_id)   AS order_count
                FROM {$this->table} oi
                JOIN orders o        ON oi.order_id   = o.id
                JOIN product_variants pv ON oi.variant_id = pv.id
                JOIN products p      ON oi.product_id  = p.id
                WHERE oi.stock_deducted = 0
                  AND oi.is_return = 0
                  AND o.is_deleted = 0
                  AND o.delivery_status NOT IN ('cancelled', 'returned', 'delivered')
                GROUP BY pv.id, p.id, p.name, pv.size, pv.sku, pv.stock
                HAVING SUM(oi.quantity) > pv.stock
                ORDER BY shortage DESC, p.name, pv.size";
        return $this->db->fetchAll($sql);
    }
}
