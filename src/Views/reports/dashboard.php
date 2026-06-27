<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4 px-3 px-md-0">
    <h1>Reports & Analytics</h1>
    <p class="text-muted mb-4">View detailed reports and export data</p>

    <!-- Report Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Revenue Report</h5>
                            <p class="text-muted small mb-0">View revenue trends and daily breakdowns</p>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-primary opacity-50"></i>
                    </div>
                    <a href="/reports/revenue" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Top Products</h5>
                            <p class="text-muted small mb-0">Most popular products and variants</p>
                        </div>
                        <i class="fas fa-star fa-2x text-warning opacity-50"></i>
                    </div>
                    <a href="/reports/products" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Expense Report</h5>
                            <p class="text-muted small mb-0">Expense breakdown by category</p>
                        </div>
                        <i class="fas fa-money-bill fa-2x text-secondary opacity-50"></i>
                    </div>
                    <a href="/reports/expenses" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Inventory Report</h5>
                            <p class="text-muted small mb-0">Stock levels and inventory value</p>
                        </div>
                        <i class="fas fa-boxes fa-2x text-success opacity-50"></i>
                    </div>
                    <a href="/reports/inventory" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Stock Shortage</h5>
                            <p class="text-muted small mb-0">Variants needed for pending orders but out of stock</p>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                    </div>
                    <a href="/reports/stock-shortage" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5>Printing Report</h5>
                            <p class="text-muted small mb-0">Items from pending orders ready for printing</p>
                        </div>
                        <i class="fas fa-print fa-2x text-info opacity-50"></i>
                    </div>
                    <a href="/reports/printing" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Export Data</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted small mb-3">Download your data as CSV for use in Excel or other tools</p>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-sm-6 col-md-3">
                    <form method="POST" action="/reports/export" class="d-inline">
                        <input type="hidden" name="type" value="orders">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-download"></i> Orders
                        </button>
                    </form>
                </div>
                <div class="col-sm-6 col-md-3">
                    <form method="POST" action="/reports/export" class="d-inline">
                        <input type="hidden" name="type" value="expenses">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-download"></i> Expenses
                        </button>
                    </form>
                </div>
                <div class="col-sm-6 col-md-3">
                    <form method="POST" action="/reports/export" class="d-inline">
                        <input type="hidden" name="type" value="products">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-download"></i> Products
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
