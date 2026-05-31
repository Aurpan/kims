<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Expense Report</h1>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/reports/expenses" class="row g-3">
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

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Expenses</p>
                    <h4 class="mb-0">$<?= number_format($totalExpenses, 2); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Summary Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Expense Breakdown</h5>
                </div>
                <div class="card-body" style="position: relative; height: 300px;">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Category Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($categoryBreakdown)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categoryBreakdown as $cb): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-1 fw-500">
                                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($cb['category']))); ?>
                                            </p>
                                            <small class="text-muted"><?= $cb['count']; ?> expense(s)</small>
                                        </div>
                                        <h6 class="mb-0">$<?= number_format($cb['total'], 2); ?></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No expenses recorded in this period</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Details Table -->
    <div class="card border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">All Expenses</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expensesByCategory)): ?>
                        <?php foreach ($expensesByCategory as $expense): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($expense['category']))); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($expense['description']); ?></td>
                                <td class="text-end fw-bold">$<?= number_format($expense['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No expenses found for the selected period
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'doughnut',
    data: {
        labels: <?= $categoryLabels; ?>,
        datasets: [{
            data: <?= $categoryAmounts; ?>,
            backgroundColor: [
                '#0d6efd',
                '#6f42c1',
                '#20c997',
                '#fd7e14',
                '#6c757d'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
