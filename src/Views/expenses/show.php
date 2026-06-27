<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><?= htmlspecialchars($expense['description']); ?></h1>
            <p class="text-muted">Expense Details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/expenses/edit/<?= $expense['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="/expenses/<?= $expense['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this expense?');">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Expense Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Category</p>
                            <span class="badge bg-secondary fs-6">
                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($expense['category']))); ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Amount</p>
                            <h4 class="mb-0">৳<?= number_format($expense['amount'], 2); ?></h4>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Expense Date</p>
                            <p class="mb-0"><?= date('M d, Y', strtotime($expense['expense_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Recorded on</p>
                            <p class="mb-0"><?= date('M d, Y H:i', strtotime($expense['created_at'])); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($expense['description'])): ?>
                        <hr>
                        <div class="mb-3">
                            <p class="text-muted small mb-1">Description</p>
                            <p class="mb-0"><?= htmlspecialchars($expense['description']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($expense['notes'])): ?>
                        <hr>
                        <div class="mb-3">
                            <p class="text-muted small mb-1">Notes</p>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($expense['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle"></i> Quick Actions</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="/expenses/edit/<?= $expense['id']; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i> Edit Expense
                        </a>
                        <a href="/expenses" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list"></i> Back to Expenses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
