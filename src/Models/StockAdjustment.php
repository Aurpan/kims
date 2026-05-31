<?php
namespace App\Models;

class StockAdjustment extends Model
{
    protected string $table = 'stock_adjustments';

    public function getByVariant(int $variantId): array
    {
        $sql = "SELECT sa.*, u.name as adjusted_by_name FROM {$this->table} sa
                LEFT JOIN users u ON sa.adjusted_by = u.id
                WHERE sa.variant_id = ? ORDER BY sa.created_at DESC";
        return $this->db->fetchAll($sql, [$variantId]);
    }

    public function getAdjustmentHistory(string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT sa.*, u.name as adjusted_by_name, pv.sku, p.name as product_name
                FROM {$this->table} sa
                LEFT JOIN users u ON sa.adjusted_by = u.id
                JOIN product_variants pv ON sa.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE DATE(sa.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $sql .= " ORDER BY sa.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function recordAdjustment(int $variantId, int $quantity, string $reason, int $userId): int
    {
        return $this->db->insert(
            $this->table,
            [
                'variant_id' => $variantId,
                'adjustment_quantity' => $quantity,
                'reason' => $reason,
                'adjusted_by' => $userId
            ]
        );
    }
}
