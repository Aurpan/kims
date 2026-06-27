<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
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
                            <h3 class="mb-0">৳<?= number_format($totalRevenue, 2); ?></h3>
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
                            <h3 class="mb-0">৳<?= number_format($monthlyRevenue, 2); ?></h3>
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
                                <h3 class="mb-0">৳<?= number_format($monthlyExpenses, 2); ?></h3>
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

    <!-- Recent Orders -->
    <div class="row mb-4">
        <div class="col-12">
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
                                        <td class="fw-bold">৳<?= number_format($order['total_amount'], 2); ?></td>
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
    </div>

    <!-- Items Ready for Printing -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Printings</h5>
                    <a href="/reports/printing" class="btn btn-sm btn-outline-primary">View Full Report</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Size</th>
                                <th class="text-center">Patch</th>
                                <th>Name</th>
                                <th>Kit No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($printingItems)): ?>
                                <?php foreach ($printingItems as $item): ?>
                                    <tr>
                                        <td>
                                            <a href="/orders/<?= $item['order_id']; ?>" class="text-decoration-none fw-bold" target="_blank">
                                                <?= htmlspecialchars($item['order_number']); ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($item['product_name']); ?></td>
                                        <td><?= htmlspecialchars($item['size']); ?></td>
                                        <td class="text-center">
                                            <?php if ($item['patches_extra'] > 0): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['kit_name'])): ?>
                                                <?= htmlspecialchars($item['kit_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['kit_number'])): ?>
                                                <?= htmlspecialchars($item['kit_number']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No items pending for printing</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-hover { transition: box-shadow 0.15s ease, transform 0.15s ease; cursor: pointer; }
.card-hover:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); transform: translateY(-2px); }
</style>


<?php include __DIR__ . '/../layouts/footer.php'; ?>
