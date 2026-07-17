<?php
namespace App\Models;

class Product extends Model
{
    protected string $table = 'products';

    public function getByCategory(string $category): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? AND is_active = TRUE ORDER BY name";
        return $this->db->fetchAll($sql, [$category]);
    }

    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE is_active = TRUE ORDER BY category";
        return $this->db->fetchAll($sql);
    }

    public function search(string $query): array
    {
        $query = "%$query%";
        $sql = "SELECT * FROM {$this->table} WHERE (name LIKE ? OR description LIKE ?) AND is_active = TRUE";
        return $this->db->fetchAll($sql, [$query, $query]);
    }

    public function getWithVariants(int $productId): ?array
    {
        $product = $this->find($productId);
        if (!$product) {
            return null;
        }

        $variantModel = new ProductVariant();
        $product['variants'] = $variantModel->getByProduct($productId);

        return $product;
    }

    public function countActive(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = TRUE";
        return (int) $this->db->fetch($sql)['total'];
    }

    public function getTotalStock(int $productId): int
    {
        $sql = "SELECT SUM(stock) as total FROM product_variants WHERE product_id = ?";
        $result = $this->db->fetch($sql, [$productId]);
        return $result['total'] ?? 0;
    }

    public function activate(int $productId): int
    {
        return $this->db->update(
            $this->table,
            ['is_active' => true],
            'id = ?',
            [$productId]
        );
    }

    public function deactivate(int $productId): int
    {
        return $this->db->update(
            $this->table,
            ['is_active' => false],
            'id = ?',
            [$productId]
        );
    }

    public function getActiveProducts(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = TRUE";
        $total = $this->db->fetch($countSql)['total'];

        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name LIMIT ? OFFSET ?";
        $items = $this->db->fetchAll($sql, [$perPage, $offset]);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function searchFiltered(string $search = '', string $category = '', int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];

        $where = 'WHERE is_active = TRUE';

        if (!empty($search)) {
            $where .= ' AND (name LIKE ? OR description LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($category)) {
            $where .= ' AND category = ?';
            $params[] = $category;
        }

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p $where";
        $total = $this->db->fetch($countSql, $params)['total'];

        $sql = "SELECT p.*, COALESCE((SELECT SUM(pv.stock) FROM product_variants pv WHERE pv.product_id = p.id), 0) AS total_stock
                FROM {$this->table} p $where ORDER BY p.name LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}
