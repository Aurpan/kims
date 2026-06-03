<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3 px-md-0">
        <h1>Orders</h1>
        <a href="/orders/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Order
        </a>
    </div>

    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/orders" class="row g-3" id="ordersFilterForm">
                <input type="hidden" name="search_type" value="order_number">
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="document.getElementById('ordersFilterForm').submit();">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?= $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?= $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="in_transit" <?= $status === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                        <option value="delivered" <?= $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="incomplete" <?= $status === 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by order #..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate); ?>" onchange="document.getElementById('ordersFilterForm').submit();">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate); ?>" onchange="document.getElementById('ordersFilterForm').submit();">
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $deliveryStatus = $order['delivery_status'] ?? '';
                            $rowClass = '';
                            if ($deliveryStatus === 'delivered') $rowClass = 'table-success';
                            elseif (in_array($deliveryStatus, ['on_hold', 'cancelled', 'returned'])) $rowClass = 'table-danger';
                            $deliveryBadges = [
                                'pending'         => 'warning text-dark',
                                'package_ready'   => 'info text-dark',
                                'courier_pickup'  => 'warning text-dark',
                                'personal_pickup' => 'warning text-dark',
                                'in_transit'      => 'warning text-dark',
                                'delivered'       => 'success',
                                'on_hold'         => 'danger',
                                'cancelled'       => 'danger',
                                'returned'        => 'danger',
                            ];
                            $deliveryBadgeClass = $deliveryBadges[$deliveryStatus] ?? 'warning text-dark';
                            $paymentBadgeClass = $order['payment_status'] === 'paid' ? 'success' : 'danger';
                            ?>
                            <tr class="<?= $rowClass; ?>">
                                <td>
                                    <a href="/orders/<?= $order['id']; ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?>
                                    </a>
                                    <?php if ($order['has_stock_issue']): ?>
                                        <span class="badge bg-warning text-dark ms-1" title="Stock unavailable"><i class="fas fa-exclamation-triangle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($order['customer_name']); ?></td>
                                <td><small>৳<?= number_format($order['total_amount'], 2); ?></small></td>
                                <td>
                                    <div><span class="badge bg-<?= $paymentBadgeClass; ?>" style="font-size:0.7rem;">
                                        <?= ucfirst($order['payment_status']); ?>
                                    </span></div>
                                    <div class="mt-1"><span class="badge bg-<?= $deliveryBadgeClass; ?>" style="font-size:0.7rem;">
                                        <?= ucfirst(str_replace('_', ' ', $deliveryStatus)); ?>
                                    </span></div>
                                </td>
                                <td>
                                    <small><?= date('M d, Y', strtotime($order['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($deliveryStatus !== 'delivered'): ?>
                                    <a href="/orders/edit/<?= $order['id']; ?>" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <form method="POST" action="/orders/<?= $order['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this order?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <p class="text-muted mb-3"><i class="fas fa-inbox fa-3x"></i></p>
                                <p class="text-muted mb-0">No orders found. <a href="/orders/create">Create one now</a></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1):
        $filterQuery = '';
        if ($status) $filterQuery .= '&status=' . urlencode($status);
        if ($search) $filterQuery .= '&search=' . urlencode($search) . '&search_type=' . urlencode($searchType);
        if ($startDate) $filterQuery .= '&start_date=' . urlencode($startDate);
        if ($endDate) $filterQuery .= '&end_date=' . urlencode($endDate);
    ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="/orders?page=1<?= $filterQuery; ?>">First</a></li>
                    <li class="page-item"><a class="page-link" href="/orders?page=<?= $page - 1; ?><?= $filterQuery; ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="/orders?page=<?= $i; ?><?= $filterQuery; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="/orders?page=<?= $page + 1; ?><?= $filterQuery; ?>">Next</a></li>
                    <li class="page-item"><a class="page-link" href="/orders?page=<?= $totalPages; ?><?= $filterQuery; ?>">Last</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    if (this.value.trim() === '') {
        document.getElementById('ordersFilterForm').submit();
        return;
    }
    searchTimeout = setTimeout(() => {
        document.getElementById('ordersFilterForm').submit();
    }, 300);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
