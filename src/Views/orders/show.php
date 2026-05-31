<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-0">Order Number</p>
            <h1 class="mb-0"><?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></h1>
        </div>
        <div class="d-flex gap-2">
            <?php if (!in_array($order['delivery_status'], ['delivered', 'cancelled', 'on_hold'])): ?>
            <a href="/orders/edit/<?= $order['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <?php endif; ?>
            <form method="POST" action="/orders/<?= $order['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this order? This will restore stock for all items.');">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Header -->
            <div class="card border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Status</p>
                            <?php
                            $statusBadges = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'in_transit' => 'secondary',
                                'delivered' => 'success',
                                'returned' => 'danger',
                                'incomplete' => 'warning',
                                'cancelled' => 'danger'
                            ];
                            $badgeClass = $statusBadges[$order['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeClass; ?> fs-6">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Order Date</p>
                            <p class="mb-0"><?= date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Total Amount</p>
                            <h4 class="mb-0">৳<?= number_format($order['total_amount'], 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Name</p>
                            <p class="mb-3"><?= htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Email</p>
                            <p class="mb-3"><?= htmlspecialchars($order['customer_email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Phone</p>
                            <p class="mb-3"><?= htmlspecialchars($order['customer_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Delivery Address</p>
                            <p class="mb-3"><?= htmlspecialchars($order['delivery_address']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment & Delivery Information -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment & Delivery</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Payment Method</p>
                            <p class="mb-3">
                                <?php
                                $paymentMethods = ['cod' => 'Cash on Delivery', 'bkash' => 'Bkash', 'bank' => 'Bank Transfer'];
                                echo htmlspecialchars($paymentMethods[$order['payment_method']] ?? 'N/A');
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Payment Status</p>
                            <p class="mb-3">
                                <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?= ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Delivery Status</p>
                            <p class="mb-3">
                                <span class="badge bg-info">
                                    <?= ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?>
                                </span>
                            </p>
                        </div>
                        <?php if ($order['delivery_status'] === 'personal_pickup' && $order['pickup_person_name']): ?>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Pickup Person's Name</p>
                                <p class="mb-3"><?= htmlspecialchars($order['pickup_person_name']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Size</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']); ?></td>
                                    <td><code><?= htmlspecialchars($item['sku']); ?></code></td>
                                    <td><?= htmlspecialchars($item['size']); ?></td>
                                    <td class="text-center"><?= $item['quantity']; ?></td>
                                    <td class="text-end">৳<?= number_format($item['unit_price'], 2); ?></td>
                                    <td class="text-end fw-bold">৳<?= number_format($item['line_total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Totals -->
            <div class="row mb-4">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>৳<?= number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <h5 class="mb-0">৳<?= number_format($order['total_amount'], 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($order['notes'])): ?>
                <div class="card border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Update -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/orders/<?= $order['id']; ?>/status">
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="in_transit" <?= $order['status'] === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="returned" <?= $order['status'] === 'returned' ? 'selected' : ''; ?>>Returned</option>
                                <option value="incomplete" <?= $order['status'] === 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-check"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tracking Information -->
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Tracking</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Tracking Number</p>
                    <p class="mb-0">
                        <?php if (!empty($order['tracking_number'])): ?>
                            <code><?= htmlspecialchars($order['tracking_number']); ?></code>
                        <?php else: ?>
                            <span class="text-muted">Not set</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="small text-muted mb-1">Order Created</p>
                                <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php if (!empty($order['shipped_at'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <p class="small text-muted mb-1">Order Shipped</p>
                                    <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['shipped_at'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order['delivered_at'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="small text-muted mb-1">Order Delivered</p>
                                    <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['delivered_at'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -15px;
        top: 20px;
        width: 2px;
        height: calc(100% + 10px);
        background-color: #e9ecef;
    }

    .timeline-marker {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #fff;
    }

    .timeline-content {
        margin-left: 0;
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
