<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard</h1>
        <small class="text-muted"><?= date('F Y'); ?></small>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Revenue</p>
                            <h3 class="mb-0">$<?= number_format($totalRevenue, 2); ?></h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Monthly Revenue</p>
                            <h3 class="mb-0">$<?= number_format($monthlyRevenue, 2); ?></h3>
                        </div>
                        <i class="fas fa-calendar fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <a href="/orders?delivery_status=pending" class="text-decoration-none">
                <div class="card border-0 bg-light card-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Pending Orders</p>
                                <h3 class="mb-0"><?= $pendingOrders; ?></h3>
                            </div>
                            <i class="fas fa-shopping-cart fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="/reports/inventory" class="text-decoration-none">
                <div class="card border-0 bg-light card-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Low Stock Items</p>
                                <h3 class="mb-0 <?= $lowStockCount > 0 ? 'text-danger' : ''; ?>"><?= $lowStockCount; ?></h3>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Monthly Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="/expenses" class="text-decoration-none">
                <div class="card border-0 bg-light card-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Monthly Expenses</p>
                                <h3 class="mb-0">$<?= number_format($monthlyExpenses, 2); ?></h3>
                            </div>
                            <i class="fas fa-money-bill fa-2x text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="/products" class="text-decoration-none">
                <div class="card border-0 bg-light card-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Products</p>
                                <h3 class="mb-0"><?= $totalProducts; ?></h3>
                            </div>
                            <i class="fas fa-box fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body" style="position: relative; height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown Chart -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Monthly Expense Breakdown</h5>
                </div>
                <div class="card-body" style="position: relative; height: 300px;">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Expense Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($expenseBreakdown)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($expenseBreakdown as $eb): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?= ucfirst(str_replace('_', ' ', htmlspecialchars($eb['category']))); ?></span>
                                        <div class="text-right">
                                            <strong>$<?= number_format($eb['total'], 2); ?></strong>
                                            <small class="text-muted d-block"><?= $eb['count']; ?> item(s)</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No expenses recorded this month</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Top Products -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="/orders" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php
                                    $statusBadges = [
                                        'pending'           => 'warning',
                                        'waiting_for_print' => 'primary',
                                        'package_ready'     => 'info',
                                        'courier_pickup'  => 'warning',
                                        'personal_pickup' => 'warning',
                                        'in_transit'      => 'secondary',
                                        'delivered'       => 'success',
                                        'on_hold'         => 'danger',
                                        'cancelled'       => 'danger',
                                        'returned'        => 'danger',
                                    ];
                                    $badgeClass = $statusBadges[$order['delivery_status']] ?? 'secondary';
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="/orders/<?= $order['id']; ?>" class="text-decoration-none fw-bold">
                                                <?= htmlspecialchars($order['order_number']); ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($order['customer_name']); ?></td>
                                        <td class="fw-bold">$<?= number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?= $badgeClass; ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('M d, Y', strtotime($order['created_at'])); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No recent orders</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Selling Products</h5>
                    <a href="/reports/products" class="btn btn-sm btn-outline-primary">View Report</a>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!empty($topVariants)): ?>
                        <?php foreach ($topVariants as $variant): ?>
                            <a href="/products/<?= $variant['product_id']; ?>" class="list-group-item list-group-item-action px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-500"><?= htmlspecialchars($variant['product_name']); ?></p>
                                        <small class="text-muted"><?= htmlspecialchars($variant['size']); ?></small>
                                    </div>
                                    <span class="badge bg-primary"><?= $variant['total_sold']; ?> sold</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-muted text-center">
                            No sales data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-hover { transition: box-shadow 0.15s ease, transform 0.15s ease; cursor: pointer; }
.card-hover:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); transform: translateY(-2px); }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= $statusLabels; ?>,
        datasets: [{
            data: <?= $statusCounts; ?>,
            backgroundColor: [
                '#ffc107',
                '#0dcaf0',
                '#0d6efd',
                '#6c757d',
                '#198754',
                '#dc3545'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'doughnut',
    data: {
        labels: <?= $categoryLabels; ?>,
        datasets: [{
            data: <?= $categoryCounts; ?>,
            backgroundColor: [
                '#0d6efd',
                '#6f42c1',
                '#20c997',
                '#fd7e14',
                '#6c757d'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
