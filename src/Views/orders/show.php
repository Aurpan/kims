<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3 px-md-0">
        <div>
            <p class="text-muted small mb-0">Order Number</p>
            <h1 class="mb-0"><?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></h1>
        </div>
        <div class="d-flex gap-2">
            <?php if ($order['delivery_status'] === 'delivered'): ?>
            <a href="/orders/exchange/<?= $order['id']; ?>" class="btn btn-outline-primary">
                <i class="fas fa-exchange-alt"></i> Exchange
            </a>
            <?php endif; ?>
            <a href="/orders/edit/<?= $order['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <?php
                $waLines = [
                    'Order ID: ' . str_replace('ORD-', '', $order['order_number']),
                    $order['customer_name'],
                    $order['customer_phone'],
                    $order['delivery_address'],
                    '',
                    'Products:',
                ];
                foreach ($items as $item) {
                    $extras = [];
                    if (!empty($item['kit_name'])) $extras[] = $item['kit_name'];
                    if (!empty($item['kit_number'])) $extras[] = $item['kit_number'];
                    if (!empty($item['patches_extra']) && $item['patches_extra'] > 0) $extras[] = 'Patch';

                    $line = '- ' . $item['product_name'] . ' (' . $item['size'] . ')';
                    if ($extras) $line .= ' --> ' . implode(' - ', $extras);
                    $waLines[] = $line;
                }
                $waLines[] = '';
                $waLines[] = 'Amount: ' . number_format($order['total_amount'], 2);
                $waText = implode("\n", $waLines);
            ?>
            <a href="https://wa.me/?text=<?= rawurlencode($waText); ?>" target="_blank" class="btn btn-outline-success">
                <i class="fab fa-whatsapp"></i> Share
            </a>
            <form method="POST" action="/orders/<?= $order['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this order? This will restore stock for all items.');">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <?php if ($flash ?? null): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']); ?> alert-dismissible fade show mb-4" role="alert">
        <?= htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php
        $undeductedItems = array_filter($items, fn($item) =>
            !($item['is_return'] ?? 0) && !($item['stock_deducted'] ?? 1)
        );
        $adjustableItems = array_filter($undeductedItems, fn($item) =>
            (int)($item['current_stock'] ?? 0) >= (int)$item['quantity']
        );
        $showStockWarning = count($undeductedItems) > 0;
        $canAdjust = count($adjustableItems) > 0;
    ?>
    <?php if ($showStockWarning): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert" id="stockIssueAlert" data-permanent>
        <i class="fas fa-exclamation-triangle fa-lg flex-shrink-0"></i>
        <div class="flex-grow-1">
            <strong>Stock Unavailable</strong> — One or more items in this order are out of stock. Delivery status is locked to <strong>Pending</strong> until stock is replenished.
        </div>
        <?php if ($canAdjust): ?>
        <form method="POST" action="/orders/<?= $order['id']; ?>/adjustStock" class="flex-shrink-0">
            <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Deduct available stock now for items that can be fulfilled?')">
                <i class="fas fa-boxes"></i> Adjust Order
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Header -->
            <div class="card border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <p class="text-muted small mb-1">Status</p>
                            <?php
                            $deliveryBadges = [
                                'pending'           => 'warning text-dark',
                                'waiting_for_print' => 'primary',
                                'package_ready'     => 'info text-dark',
                                'courier_pickup'  => 'warning text-dark',
                                'personal_pickup' => 'warning text-dark',
                                'in_transit'      => 'warning text-dark',
                                'delivered'       => 'success',
                                'on_hold'         => 'danger',
                                'cancelled'       => 'danger',
                                'returned'        => 'danger',
                            ];
                            $deliveryBadgeClass = $deliveryBadges[$order['delivery_status']] ?? 'warning text-dark';
                            $paymentBadgeClass = $order['payment_status'] === 'paid' ? 'success' : 'danger';
                            ?>
                            <div class="d-flex gap-1 flex-wrap">
                                <span class="badge bg-<?= $paymentBadgeClass; ?>">
                                    <?= ucfirst($order['payment_status']); ?>
                                </span>
                                <span class="badge bg-<?= $deliveryBadgeClass; ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
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
                        <div class="col-6 col-md-4">
                            <p class="text-muted small mb-1">Status</p>
                            <p class="mb-3">
                                <span class="badge bg-<?= $deliveryBadgeClass; ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-6 col-md-4">
                            <p class="text-muted small mb-1">Payment Method</p>
                            <p class="mb-3">
                                <?php
                                $paymentMethods = ['cod' => 'Cash on Delivery', 'bkash' => 'Bkash', 'bank' => 'Bank Transfer'];
                                echo htmlspecialchars($paymentMethods[$order['payment_method']] ?? 'N/A');
                                ?>
                            </p>
                        </div>
                        <div class="col-6 col-md-4">
                            <p class="text-muted small mb-1">Payment Status</p>
                            <p class="mb-3">
                                <span class="badge bg-<?= $paymentBadgeClass; ?>">
                                    <?= ucfirst($order['payment_status']); ?>
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
                                <th>Size</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $stockBadge = '';
                                    if (!($item['stock_deducted'] ?? 1) && !($item['is_return'] ?? 0)) {
                                        $currentStock = (int) ($item['current_stock'] ?? 0);
                                        $qty = (int) $item['quantity'];
                                        if ($currentStock === 0) {
                                            $stockBadge = '<span class="badge bg-danger ms-1">Out of Stock</span>';
                                        } elseif ($qty > $currentStock) {
                                            $stockBadge = '<span class="badge bg-warning text-dark ms-1"><i class="fas fa-exclamation-triangle"></i> Only ' . $currentStock . ' in stock</span>';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['product_name']); ?><?= $stockBadge; ?>
                                        <?php if (!empty($item['patches_extra']) && $item['patches_extra'] > 0): ?>
                                            <br><span class="badge bg-secondary mt-1">WC Patches +৳<?= number_format($item['patches_extra'], 2); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['kit_name']) || !empty($item['kit_number'])): ?>
                                            <br><span class="badge bg-info text-dark mt-1">
                                                Name-Kit<?= !empty($item['kit_name']) ? ': ' . htmlspecialchars($item['kit_name']) : ''; ?><?= !empty($item['kit_number']) ? ' #' . htmlspecialchars($item['kit_number']) : ''; ?>
                                                <?= (!empty($item['namekit_extra']) && $item['namekit_extra'] > 0) ? ' +৳' . number_format($item['namekit_extra'], 2) : ''; ?>
                                            </span>
                                        <?php elseif (!empty($item['namekit_extra']) && $item['namekit_extra'] > 0): ?>
                                            <br><span class="badge bg-info text-dark mt-1">Name-Kit +৳<?= number_format($item['namekit_extra'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
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
                                    <p class="small text-muted mb-1">Product Delivered</p>
                                    <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['delivered_at'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order['cancelled_at'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <p class="small text-muted mb-1">Order Cancelled</p>
                                    <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['cancelled_at'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order['returned_at'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <p class="small text-muted mb-1">Order Returned</p>
                                    <p class="small mb-0"><?= date('M d, Y H:i', strtotime($order['returned_at'])); ?></p>
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
