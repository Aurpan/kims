<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <h1><?= $expense ? 'Edit Expense' : 'Record Expense'; ?></h1>
    <p class="text-muted"><?= $expense ? 'Update expense information' : 'Add a new expense record'; ?></p>

    <div class="row">
        <div class="col-lg-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Please fix:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $field => $message): ?>
                            <li><?= htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Expense Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $expense ? '/expenses/update/' . $expense['id'] : '/expenses'; ?>">
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="cogs" <?= ($expense['category'] ?? $old['category'] ?? '') === 'cogs' ? 'selected' : ''; ?>>Cost of Goods Sold</option>
                                <option value="operational" <?= ($expense['category'] ?? $old['category'] ?? '') === 'operational' ? 'selected' : ''; ?>>Operational</option>
                                <option value="shipping" <?= ($expense['category'] ?? $old['category'] ?? '') === 'shipping' ? 'selected' : ''; ?>>Shipping</option>
                                <option value="marketing" <?= ($expense['category'] ?? $old['category'] ?? '') === 'marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="other" <?= ($expense['category'] ?? $old['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (!empty($errors['category'])): ?>
                                <small class="text-danger"><?= htmlspecialchars($errors['category']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required
                                               value="<?= htmlspecialchars($expense['amount'] ?? $old['amount'] ?? ''); ?>">
                                    </div>
                                    <?php if (!empty($errors['amount'])): ?>
                                        <small class="text-danger"><?= htmlspecialchars($errors['amount']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date *</label>
                                    <input type="date" name="expense_date" class="form-control" required
                                           value="<?= htmlspecialchars($expense['expense_date'] ?? $old['expense_date'] ?? $today); ?>">
                                    <?php if (!empty($errors['expense_date'])): ?>
                                        <small class="text-danger"><?= htmlspecialchars($errors['expense_date']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control"
                                   value="<?= htmlspecialchars($expense['description'] ?? $old['description'] ?? ''); ?>"
                                   placeholder="e.g., Invoice #12345, Supplier ABC">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional details..."><?= htmlspecialchars($expense['notes'] ?? $old['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $expense ? 'Update Expense' : 'Record Expense'; ?>
                            </button>
                            <a href="/expenses" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle"></i> Tips</h5>
                    <ul class="small mb-0">
                        <li class="mb-2"><strong>Category:</strong> Required - choose appropriate category</li>
                        <li class="mb-2"><strong>Amount:</strong> Enter numeric value in dollars</li>
                        <li class="mb-2"><strong>Date:</strong> Date when expense occurred</li>
                        <li><strong>Description:</strong> Optional details for reference</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
