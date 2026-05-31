<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inventory Report</h1>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Inventory Value</p>
                    <h4 class="mb-0">$<?= number_format($totalInventoryValue, 2); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Units in Stock</p>
                    <h4 class="mb-0"><?= $totalUnits; ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Low Stock Items</p>
                    <h4 class="mb-0 <?= $lowStockCount > 0 ? 'text-danger' : ''; ?>"><?= $lowStockCount; ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <?php if (!empty($lowStockVariants)): ?>
        <div class="card border-0 border-start border-warning mb-4">
            <div class="card-header bg-light border-bottom-warning">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning"></i> Low Stock Alerts</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Size</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Inventory Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockVariants as $variant): ?>
                            <tr class="table-warning">
                                <td>
                                    <a href="/products/<?= $variant['product_id']; ?>" class="text-decoration-none fw-500">
                                        <?= htmlspecialchars($variant['product_name']); ?>
                                    </a>
                                </td>
                                <td><code><?= htmlspecialchars($variant['sku']); ?></code></td>
                                <td><?= htmlspecialchars($variant['size']); ?></td>
                                <td class="text-center">
                                    <strong><?= $variant['stock']; ?></strong>
                                </td>
                                <td class="text-end">
                                    $<?= number_format($variant['variant_price'] ?? $variant['base_price'] ?? 0, 2); ?>
                                </td>
                                <td class="text-end fw-bold">
                                    $<?= number_format(($variant['stock'] * ($variant['variant_price'] ?? $variant['base_price'] ?? 0)), 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- All Inventory -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">All Inventory</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Size</th>
                        <th class="text-center">Stock Level</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Inventory Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $allVariants = [];
                    // Group variants by product for display
                    // This is a simplified display - in production you might want to paginate
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Loading inventory data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
