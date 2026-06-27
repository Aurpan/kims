<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Top Products Report</h1>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/reports/products" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Top Selling Products</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Size</th>
                        <th class="text-center">Units Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topVariants)): ?>
                        <?php foreach ($topVariants as $index => $variant): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?= $index + 1; ?></span>
                                </td>
                                <td>
                                    <a href="/products/<?= $variant['product_id']; ?>" class="text-decoration-none fw-500">
                                        <?= htmlspecialchars($variant['product_name']); ?>
                                    </a>
                                </td>
                                <td><code><?= htmlspecialchars($variant['sku']); ?></code></td>
                                <td><?= htmlspecialchars($variant['size']); ?></td>
                                <td class="text-center fw-bold"><?= $variant['total_sold']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No sales data available for the selected period
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
