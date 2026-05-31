<?php
namespace App\Models;

class ProductVariant extends Model
{
    protected string $table = 'product_variants';

    public function getByProduct(int $productId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY size";
        return $this->db->fetchAll($sql, [$productId]);
    }

    public function findBySku(string $sku): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE sku = ?";
        return $this->db->fetch($sql, [$sku]);
    }

    public function getLowStock(): array
    {
        $sql = "SELECT pv.*, p.name as product_name, p.id as product_id FROM {$this->table} pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.stock <= pv.reorder_point ORDER BY pv.stock ASC";
        return $this->db->fetchAll($sql);
    }

    public function updateStock(int $variantId, int $quantity): int
    {
        if ($quantity < 0) {
            $variant = $this->find($variantId);
            if (!$variant || ($variant['stock'] + $quantity) < 0) {
                $sku = $variant['sku'] ?? "ID {$variantId}";
                $available = $variant['stock'] ?? 0;
                throw new \RuntimeException("Insufficient stock for {$sku} (available: {$available}, requested: " . abs($quantity) . ")");
            }
        }
        $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE id = ?";
        return $this->db->query($sql, [$quantity, $variantId])->rowCount();
    }

    public function setStock(int $variantId, int $stock): int
    {
        return $this->db->update(
            $this->table,
            ['stock' => $stock],
            'id = ?',
            [$variantId]
        );
    }

    public function getByProductAndVariant(int $productId, string $size): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? AND size = ?";
        return $this->db->fetch($sql, [$productId, $size]);
    }

    public function getTopSellingVariants(int $limit = 5): array
    {
        $sql = "SELECT pv.*, p.name as product_name, SUM(oi.quantity) as total_sold
                FROM {$this->table} pv
                JOIN products p ON pv.product_id = p.id
                JOIN order_items oi ON pv.id = oi.variant_id
                GROUP BY pv.id
                ORDER BY total_sold DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getAllWithProduct(): array
    {
        $sql = "SELECT pv.*, p.name as product_name, p.base_price FROM {$this->table} pv
                JOIN products p ON pv.product_id = p.id
                WHERE p.is_active = TRUE
                ORDER BY p.name, pv.size";
        return $this->db->fetchAll($sql);
    }
}
