<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Expenses</h1>
        <a href="/expenses/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Record Expense
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
            <form method="GET" action="/expenses" class="row g-3" id="expensesFilterForm">
                <div class="col-md-4">
                    <select name="category" class="form-select" onchange="document.getElementById('expensesFilterForm').submit();">
                        <option value="">All Categories</option>
                        <option value="cogs" <?= $category === 'cogs' ? 'selected' : ''; ?>>Cost of Goods Sold</option>
                        <option value="operational" <?= $category === 'operational' ? 'selected' : ''; ?>>Operational</option>
                        <option value="shipping" <?= $category === 'shipping' ? 'selected' : ''; ?>>Shipping</option>
                        <option value="marketing" <?= $category === 'marketing' ? 'selected' : ''; ?>>Marketing</option>
                        <option value="other" <?= $category === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate); ?>" placeholder="From" onchange="document.getElementById('expensesFilterForm').submit();">
                </div>
                <div class="col-md-4">
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate); ?>" placeholder="To" onchange="document.getElementById('expensesFilterForm').submit();">
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <?php if ($totalForFilter > 0): ?>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Total for Filter</p>
                        <h4 class="mb-0">$<?= number_format($totalForFilter, 2); ?></h4>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Number of Expenses</p>
                        <h4 class="mb-0"><?= $total; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Category Breakdown (if no date range filter) -->
    <?php if (!$startDate && !$endDate && empty($category)): ?>
        <div class="card border-0 mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Expense Breakdown by Category</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($categoryBreakdown as $cb): ?>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <p class="text-muted small mb-1">
                                    <?= ucfirst(str_replace('_', ' ', htmlspecialchars($cb['category']))); ?>
                                </p>
                                <h5 class="mb-2">$<?= number_format($cb['total'], 2); ?></h5>
                                <small class="text-muted"><?= $cb['count']; ?> expense(s)</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Expenses Table -->
    <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expenses)): ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td>
                                    <small><?= date('M d, Y', strtotime($expense['expense_date'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($expense['category']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/expenses/<?= $expense['id']; ?>" class="text-decoration-none fw-500">
                                        <?= htmlspecialchars($expense['description']); ?>
                                    </a>
                                </td>
                                <td class="text-end fw-bold">$<?= number_format($expense['amount'], 2); ?></td>
                                <td>
                                    <a href="/expenses/<?= $expense['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/expenses/edit/<?= $expense['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/expenses/<?= $expense['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this expense?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted mb-3"><i class="fas fa-inbox fa-3x"></i></p>
                                <p class="text-muted mb-0">No expenses found. <a href="/expenses/create">Record one now</a></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="/expenses">First</a></li>
                    <li class="page-item"><a class="page-link" href="/expenses?page=<?= $page - 1; ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="/expenses?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="/expenses?page=<?= $page + 1; ?>">Next</a></li>
                    <li class="page-item"><a class="page-link" href="/expenses?page=<?= $totalPages; ?>">Last</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
