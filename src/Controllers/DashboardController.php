<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $orderModel = new Order();
        $productModel = new Product();
        $variantModel = new ProductVariant();
        $expenseModel = new Expense();

        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $totalRevenue = $orderModel->getTotalRevenue();
        $monthlyRevenue = $orderModel->getTotalRevenue($monthStart, $monthEnd);
        $pendingOrders = $orderModel->getByStatus('pending', 1, 1)['total'];
        $lowStockCount = count($variantModel->getLowStock(10));
        $totalProducts = count($productModel->all());
        $recentOrders = $orderModel->getRecentOrders(5);
        $statusDistribution = $orderModel->getStatusDistribution();
        $monthlyExpenses = $expenseModel->getMonthlyTotal();
        $expenseBreakdown = $expenseModel->getMonthlyBreakdown();
        $topVariants = $variantModel->getTopSellingVariants(5);

        $statusLabels = [];
        $statusCounts = [];
        foreach ($statusDistribution as $sd) {
            $statusLabels[] = ucfirst(str_replace('_', ' ', $sd['status']));
            $statusCounts[] = $sd['count'];
        }

        $categoryLabels = [];
        $categoryCounts = [];
        foreach ($expenseBreakdown as $eb) {
            $categoryLabels[] = ucfirst(str_replace('_', ' ', $eb['category']));
            $categoryCounts[] = $eb['total'];
        }

        // Get pending orders items for printing (with patches, name, or kit number)
        $db = $orderModel->getConnection();
        $sql = "
            SELECT
                o.id as order_id,
                o.order_number,
                p.name as product_name,
                pv.size,
                oi.patches_extra,
                oi.kit_name,
                oi.kit_number
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN product_variants pv ON oi.variant_id = pv.id
            INNER JOIN products p ON oi.product_id = p.id
            WHERE o.delivery_status = 'pending'
            AND (oi.is_return = 0 OR oi.is_return IS NULL)
            AND (oi.patches_extra > 0 OR oi.kit_name IS NOT NULL OR oi.kit_number IS NOT NULL)
            ORDER BY o.id DESC, oi.id ASC
            LIMIT 10
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $printingItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('dashboard/index', [
            'page_title' => 'Dashboard',
            'user' => Auth::getCurrentUser(),
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingOrders' => $pendingOrders,
            'lowStockCount' => $lowStockCount,
            'totalProducts' => $totalProducts,
            'recentOrders' => $recentOrders,
            'printingItems' => $printingItems,
            'monthlyExpenses' => $monthlyExpenses,
            'expenseBreakdown' => $expenseBreakdown,
            'statusLabels' => json_encode($statusLabels),
            'statusCounts' => json_encode($statusCounts),
            'categoryLabels' => json_encode($categoryLabels),
            'categoryCounts' => json_encode($categoryCounts)
        ]);
    }
}
