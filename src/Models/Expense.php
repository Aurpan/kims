<?php
namespace App\Models;

class Expense extends Model
{
    protected string $table = 'expenses';

    public function getByCategory(string $category): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY expense_date DESC";
        return $this->db->fetchAll($sql, [$category]);
    }

    public function getByDateRange(string $startDate, string $endDate, ?string $category = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE expense_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY expense_date DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getCategoryTotal(string $category, string $startDate = null, string $endDate = null): float
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE category = ?";
        $params = [$category];

        if ($startDate && $endDate) {
            $sql .= " AND expense_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $result = $this->db->fetch($sql, $params);
        return (float) ($result['total'] ?? 0);
    }

    public function getTotalExpenses(string $startDate = null, string $endDate = null): float
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table}";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " WHERE expense_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }

        $result = $this->db->fetch($sql, $params);
        return (float) ($result['total'] ?? 0);
    }

    public function getCategoryBreakdown(string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT category, SUM(amount) as total, COUNT(*) as count FROM {$this->table}";

        if ($startDate && $endDate) {
            $sql .= " WHERE expense_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        } else {
            $params = [];
        }

        $sql .= " GROUP BY category";
        return $this->db->fetchAll($sql, $params);
    }

    public function getMonthlyTotal(int $year = null, int $month = null): float
    {
        if ($year === null) {
            $year = (int) date('Y');
        }
        if ($month === null) {
            $month = (int) date('m');
        }

        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        return $this->getTotalExpenses($startDate, $endDate);
    }

    public function getMonthlyBreakdown(int $year = null, int $month = null): array
    {
        if ($year === null) {
            $year = (int) date('Y');
        }
        if ($month === null) {
            $month = (int) date('m');
        }

        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        return $this->getCategoryBreakdown($startDate, $endDate);
    }
}
