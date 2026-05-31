<?php
namespace App\Models;

class Order extends Model
{
    protected string $table = 'orders';

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = ?";
        return $this->db->fetch($sql, [$orderNumber]);
    }

    public function getByStatus(string $status, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = ?";
        $total = $this->db->fetch($countSql, [$status])['total'];

        $sql = "SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $items = $this->db->fetchAll($sql, [$status, $perPage, $offset]);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function getByDateRange(string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }

    public function searchOrders(array $filters, int $page = 1, int $perPage = 20): array
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['customer_name'])) {
            $query .= " AND customer_name LIKE ?";
            $params[] = "%{$filters['customer_name']}%";
        }

        if (!empty($filters['order_number'])) {
            $query .= " AND order_number LIKE ?";
            $params[] = "%{$filters['order_number']}%";
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        $query .= " ORDER BY created_at DESC";

        $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as t";
        $total = $this->db->fetch($countQuery, $params)['total'];

        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = $this->db->fetchAll($query, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function updateStatus(int $orderId, string $status): int
    {
        $updateData = ['status' => $status];

        if ($status === 'shipped') {
            $updateData['shipped_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update(
            $this->table,
            $updateData,
            'id = ?',
            [$orderId]
        );
    }

    public function getStatusDistribution(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        return $this->db->fetchAll($sql);
    }

    public function getTotalRevenue(string $startDate = null, string $endDate = null): float
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE status != 'returned'";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $result = $this->db->fetch($sql, $params);
        return (float) ($result['total'] ?? 0);
    }

    public function getDailyRevenue(string $date): float
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE DATE(created_at) = ?";
        $result = $this->db->fetch($sql, [$date]);
        return (float) ($result['total'] ?? 0);
    }

    public function getRecentOrders(int $limit = 10): array
    {
        $sql = "SELECT id, order_number, customer_name, total_amount, status, created_at FROM {$this->table} ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
