<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductVariant;

class ReportController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $this->render('reports/dashboard', [
            'page_title' => 'Reports & Analytics'
        ]);
    }

    public function revenue(): void
    {
        Auth::requireLogin();

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $orderModel = new Order();
        $periodTotal = $orderModel->getTotalRevenue($startDate, $endDate);

        $previousStart = date('Y-m-01', strtotime('-1 month', strtotime($startDate)));
        $previousEnd = date('Y-m-t', strtotime('-1 month', strtotime($startDate)));
        $previousTotal = $orderModel->getTotalRevenue($previousStart, $previousEnd);

        $dailyData = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $revenue = $orderModel->getDailyRevenue($date);
            $dailyData[] = [
                'date' => $date,
                'revenue' => $revenue
            ];
            $current = strtotime('+1 day', $current);
        }

        $dates = array_map(fn($d) => $d['date'], $dailyData);
        $revenues = array_map(fn($d) => $d['revenue'], $dailyData);
        $avgDaily = count($dailyData) > 0 ? $periodTotal / count($dailyData) : 0;

        $this->render('reports/revenue', [
            'page_title' => 'Revenue Report',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodTotal' => $periodTotal,
            'previousTotal' => $previousTotal,
            'avgDaily' => $avgDaily,
            'dailyData' => $dailyData,
            'dateLabels' => json_encode($dates),
            'revenueData' => json_encode($revenues)
        ]);
    }

    public function topProducts(): void
    {
        Auth::requireLogin();

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $variantModel = new ProductVariant();
        $topVariants = $variantModel->getTopSellingVariants(20);

        $this->render('reports/products', [
            'page_title' => 'Top Products Report',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'topVariants' => $topVariants
        ]);
    }

    public function expenses(): void
    {
        Auth::requireLogin();

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $expenseModel = new Expense();
        $categoryBreakdown = $expenseModel->getCategoryBreakdown($startDate, $endDate);
        $totalExpenses = $expenseModel->getTotalExpenses($startDate, $endDate);
        $expensesByCategory = $expenseModel->getByDateRange($startDate, $endDate);

        $categoryLabels = [];
        $categoryCounts = [];
        foreach ($categoryBreakdown as $cb) {
            $categoryLabels[] = ucfirst(str_replace('_', ' ', $cb['category']));
            $categoryCounts[] = $cb['total'];
        }

        $this->render('reports/expenses', [
            'page_title' => 'Expense Report',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryBreakdown' => $categoryBreakdown,
            'totalExpenses' => $totalExpenses,
            'expensesByCategory' => $expensesByCategory,
            'categoryLabels' => json_encode($categoryLabels),
            'categoryAmounts' => json_encode($categoryCounts)
        ]);
    }

    public function inventory(): void
    {
        Auth::requireLogin();

        $variantModel = new ProductVariant();
        $lowStockVariants = $variantModel->getLowStock(10);
        $allVariants = $variantModel->all();

        $totalInventoryValue = 0;
        $totalUnits = 0;
        foreach ($allVariants as $variant) {
            $price = $variant['variant_price'] ?? $variant['base_price'] ?? 0;
            $totalInventoryValue += $variant['stock'] * $price;
            $totalUnits += $variant['stock'];
        }

        $this->render('reports/inventory', [
            'page_title' => 'Inventory Report',
            'lowStockVariants' => $lowStockVariants,
            'totalUnits' => $totalUnits,
            'totalInventoryValue' => $totalInventoryValue,
            'lowStockCount' => count($lowStockVariants)
        ]);
    }

    public function export(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/reports');
            return;
        }

        $type = $_POST['type'] ?? '';

        if ($type === 'orders') {
            $this->exportOrders();
        } elseif ($type === 'expenses') {
            $this->exportExpenses();
        } elseif ($type === 'products') {
            $this->exportProducts();
        } else {
            $this->redirect('/reports');
        }
    }

    private function exportOrders(): void
    {
        $orderModel = new Order();
        $orders = $orderModel->getAllActive();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Order #', 'Customer', 'Email', 'Phone', 'Address', 'Total', 'Status', 'Date']);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order['order_number'],
                $order['customer_name'],
                $order['customer_email'],
                $order['customer_phone'],
                $order['delivery_address'],
                $order['total_amount'],
                $order['status'],
                $order['created_at']
            ]);
        }

        fclose($handle);
        exit;
    }

    private function exportExpenses(): void
    {
        $expenseModel = new Expense();
        $expenses = $expenseModel->all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="expenses_' . date('Y-m-d') . '.csv"');

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Date', 'Category', 'Description', 'Amount', 'Notes']);

        foreach ($expenses as $expense) {
            fputcsv($handle, [
                $expense['expense_date'],
                $expense['category'],
                $expense['description'],
                $expense['amount'],
                $expense['notes']
            ]);
        }

        fclose($handle);
        exit;
    }

    private function exportProducts(): void
    {
        $productModel = new Product();
        $products = $productModel->all();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Product', 'Category', 'Base Price', 'Status']);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product['name'],
                $product['category'],
                $product['base_price'],
                $product['is_active'] ? 'Active' : 'Inactive'
            ]);
        }

        fclose($handle);
        exit;
    }
}
