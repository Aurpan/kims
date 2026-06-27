<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Printing Report</h1>
        <div class="d-flex gap-2">
            <a href="/reports" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Items to Print</p>
                    <h4 class="mb-0"><?= $total_items; ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Pending Orders - Items to Print</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th class="text-center">Patch</th>
                        <th>Name</th>
                        <th>Kit No.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
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
                            <td colspan="6" class="text-center text-muted py-4">
                                No pending orders to print
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .card-header, .d-flex.justify-content-between {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        table {
            font-size: 12px;
        }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
