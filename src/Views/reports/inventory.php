<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
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
                    <h4 class="mb-0">৳<?= number_format($totalInventoryValue, 2); ?></h4>
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

    <!-- All Inventory -->
    <div class="card border-0 mb-4">
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
                <tbody id="all-inventory-body">
                    <?php if (empty($allVariants)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No inventory items found.
                        </td>
                    </tr>
                    <?php else: foreach ($allVariants as $variant): ?>
                    <tr>
                        <td>
                            <a href="/products/<?= $variant['product_id']; ?>" class="text-decoration-none fw-500">
                                <?= htmlspecialchars($variant['product_name']); ?>
                            </a>
                        </td>
                        <td><code><?= htmlspecialchars($variant['sku']); ?></code></td>
                        <td><?= htmlspecialchars($variant['size']); ?></td>
                        <td class="text-center"><?= $variant['stock']; ?></td>
                        <td class="text-end">
                            ৳<?= number_format($variant['variant_price'] ?? $variant['base_price'] ?? 0, 2); ?>
                        </td>
                        <td class="text-end fw-bold">
                            ৳<?= number_format(($variant['stock'] * ($variant['variant_price'] ?? $variant['base_price'] ?? 0)), 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <nav class="d-flex justify-content-center py-2">
            <ul class="pagination pagination-sm mb-0" id="all-inventory-pagination"></ul>
        </nav>
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
                    <tbody id="low-stock-body">
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
                                    ৳<?= number_format($variant['variant_price'] ?? $variant['base_price'] ?? 0, 2); ?>
                                </td>
                                <td class="text-end fw-bold">
                                    ৳<?= number_format(($variant['stock'] * ($variant['variant_price'] ?? $variant['base_price'] ?? 0)), 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav class="d-flex justify-content-center py-2">
                <ul class="pagination pagination-sm mb-0" id="low-stock-pagination"></ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function paginateTable(tbodyId, paginationId, perPage) {
    const tbody = document.getElementById(tbodyId);
    const pagination = document.getElementById(paginationId);
    if (!tbody || !pagination) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));
    const totalPages = Math.ceil(rows.length / perPage);
    if (totalPages <= 1) return;

    function showPage(page) {
        rows.forEach((row, i) => {
            row.style.display = (i >= (page - 1) * perPage && i < page * perPage) ? '' : 'none';
        });

        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item' + (i === page ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', (e) => { e.preventDefault(); showPage(i); });
            li.appendChild(a);
            pagination.appendChild(li);
        }
    }

    showPage(1);
}

document.addEventListener('DOMContentLoaded', function () {
    paginateTable('low-stock-body', 'low-stock-pagination', 10);
    paginateTable('all-inventory-body', 'all-inventory-pagination', 15);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
