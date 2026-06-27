<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Stock Shortage Report</h1>
            <p class="text-muted small mb-0">Variants needed to fulfill pending orders but not available in stock</p>
        </div>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Variants Short</p>
                    <h4 class="mb-0 <?= $variantCount > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?= $variantCount; ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Units Short</p>
                    <h4 class="mb-0 <?= $totalShortageUnits > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?= $totalShortageUnits; ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($shortages)): ?>
        <div class="card border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>All stocked up</h5>
                <p class="text-muted mb-0">Every pending order has enough stock to be fulfilled.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Variants with Insufficient Stock</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Size</th>
                            <th class="text-center">In Stock</th>
                            <th class="text-center">Required</th>
                            <th class="text-center">Shortage</th>
                            <th class="text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shortages as $row): ?>
                            <?php
                                $current  = (int) $row['current_stock'];
                                $required = (int) $row['required_qty'];
                                $short    = (int) $row['shortage'];
                                $pct      = $required > 0 ? min(100, round(($current / $required) * 100)) : 0;
                                $barClass = $current === 0 ? 'bg-danger' : 'bg-warning';
                            ?>
                            <tr>
                                <td>
                                    <a href="/products/<?= $row['product_id']; ?>" class="text-decoration-none fw-semibold">
                                        <?= htmlspecialchars($row['product_name']); ?>
                                    </a>
                                </td>
                                <td><code><?= htmlspecialchars($row['sku']); ?></code></td>
                                <td><?= htmlspecialchars($row['size']); ?></td>
                                <td class="text-center">
                                    <?php if ($current === 0): ?>
                                        <span class="badge bg-danger">0</span>
                                    <?php else: ?>
                                        <span class="text-warning fw-semibold"><?= $current; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-semibold"><?= $required; ?></td>
                                <td class="text-center">
                                    <span class="text-danger fw-bold"><?= $short; ?></span>
                                    <div class="progress mt-1" style="height:4px; width:80px; margin:0 auto;">
                                        <div class="progress-bar <?= $barClass; ?>" style="width:<?= $pct; ?>%"></div>
                                    </div>
                                    <div class="text-muted" style="font-size:0.7rem;"><?= $pct; ?>% covered</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= (int) $row['order_count']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
