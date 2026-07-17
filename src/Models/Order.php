<?php
namespace App\Models;

class Order extends Model
{
    protected string $table = 'orders';

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND is_deleted = 0";
        return $this->db->fetch($sql, [$id]);
    }

    public function softDelete(int $id): int
    {
        return $this->db->update($this->table, ['is_deleted' => 1], 'id = ?', [$id]);
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = ? AND is_deleted = 0";
        return $this->db->fetch($sql, [$orderNumber]);
    }

    public function getByStatus(string $status, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE delivery_status = ? AND is_deleted = 0";
        $total = $this->db->fetch($countSql, [$status])['total'];

        $sql = "SELECT * FROM {$this->table} WHERE delivery_status = ? AND is_deleted = 0 ORDER BY created_at DESC LIMIT ? OFFSET ?";
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
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = 0 AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }

    public function searchOrders(array $filters, int $page = 1, int $perPage = 20): array
    {
        $query = "SELECT * FROM {$this->table} WHERE is_deleted = 0";
        $params = [];

        if (!empty($filters['delivery_status'])) {
            if (is_array($filters['delivery_status'])) {
                $placeholders = implode(',', array_fill(0, count($filters['delivery_status']), '?'));
                $query .= " AND delivery_status IN ({$placeholders})";
                foreach ($filters['delivery_status'] as $status) {
                    $params[] = $status;
                }
            } else {
                $query .= " AND delivery_status = ?";
                $params[] = $filters['delivery_status'];
            }
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
        $updateData = ['delivery_status' => $status];

        if ($status === 'delivered') {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'cancelled') {
            $updateData['cancelled_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'returned') {
            $updateData['returned_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update(
            $this->table,
            $updateData,
            'id = ?',
            [$orderId]
        );
    }

    public function setDeliveryTimestamp(int $orderId, string $newStatus, string $oldStatus): void
    {
        $now = date('Y-m-d H:i:s');
        $updateData = [];

        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $updateData['delivered_at'] = $now;
        } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $updateData['cancelled_at'] = $now;
        } elseif ($newStatus === 'returned' && $oldStatus !== 'returned') {
            $updateData['returned_at'] = $now;
        }

        if (!empty($updateData)) {
            $this->db->update($this->table, $updateData, 'id = ?', [$orderId]);
        }
    }

    public function getStatusDistribution(): array
    {
        $sql = "SELECT delivery_status as status, COUNT(*) as count FROM {$this->table} WHERE is_deleted = 0 GROUP BY delivery_status";
        return $this->db->fetchAll($sql);
    }

    public function getTotalRevenue(string $startDate = null, string $endDate = null): float
    {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE is_deleted = 0 AND delivery_status NOT IN ('returned', 'cancelled')";
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
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table} WHERE is_deleted = 0 AND DATE(created_at) = ?";
        $result = $this->db->fetch($sql, [$date]);
        return (float) ($result['total'] ?? 0);
    }

    public function getAllActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = 0 ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getRecentOrders(int $limit = 10): array
    {
        $sql = "SELECT id, order_number, customer_name, total_amount, delivery_status, created_at FROM {$this->table} WHERE is_deleted = 0 ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
