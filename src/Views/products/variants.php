<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <!-- Product Header -->
    <div class="px-3 px-md-0">
        <div class="row mb-2">
            <div class="col-12">
                <h1><?= htmlspecialchars($product['name']); ?></h1>
                <p class="text-muted">Manage variants for this product</p>
            </div>
        </div>

        <!-- Stats Row: always in one row -->
        <div class="row g-2 mb-4">
            <div class="col-4">
                <div class="card border-0 bg-info bg-opacity-10">
                    <div class="card-body text-center py-2 px-1">
                        <h4 class="mb-0 text-info"><?= intval($totalStock ?? 0); ?></h4>
                        <small class="text-muted">Total Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-2 px-1">
                        <h4 class="mb-0">৳<?= number_format($product['base_price'], 0); ?></h4>
                        <small class="text-muted">Selling Price</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 bg-success bg-opacity-10">
                    <div class="card-body text-center py-2 px-1">
                        <h4 class="mb-0 text-success">
                            <?= $product['sourcing_price'] ? '৳' . number_format($product['sourcing_price'], 0) : '<span class="text-muted" style="font-size:.85rem">N/A</span>'; ?>
                        </h4>
                        <small class="text-muted">Sourcing Price</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/products">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']); ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Variants Table -->
    <div class="card border-0 mb-4 mx-3 mx-md-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Variants</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addVariantForm">
                <i class="fas fa-plus"></i> Add Variant
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th class="text-center pe-md-4" style="white-space: nowrap; width: 1%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($product['variants'])): ?>
                        <?php foreach ($product['variants'] as $variant): ?>
                            <tr <?= in_array($variant['id'], $lowStockVariantIds) ? 'class="table-warning"' : ''; ?>>
                                <td style="word-break: break-all;">
                                    <code><?= htmlspecialchars($variant['sku']); ?></code>
                                    <?php if (in_array($variant['id'], $lowStockVariantIds)): ?>
                                        <br><span class="badge bg-warning text-dark">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($variant['size']); ?></td>
                                <td style="white-space: nowrap;">
                                    <span class="stock-display-<?= $variant['id']; ?>">
                                        <strong><?= intval($variant['stock']); ?></strong>
                                        <button class="btn btn-link text-primary p-0 ms-1"
                                                onclick="showStockInput(<?= $variant['id']; ?>)"
                                                title="Add Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </span>
                                    <span class="stock-inline-<?= $variant['id']; ?> d-none" style="display:inline-flex;align-items:center;gap:4px;">
                                        <form method="POST" action="/products/variants/<?= $variant['id']; ?>/updateStock"
                                              style="display:inline-flex;align-items:center;gap:4px;">
                                            <input type="number" name="stock" min="0" required
                                                   class="form-control form-control-sm"
                                                   style="width:70px;"
                                                   placeholder="qty">
                                            <button type="submit" class="btn btn-sm btn-success p-1 lh-1" title="Save">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-secondary p-1 lh-1" title="Cancel"
                                                onclick="hideStockInput(<?= $variant['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </td>
                                <td>
                                    ৳<?= number_format($variant['variant_price'] ?: $product['base_price'], 0); ?>
                                </td>
                                <td class="text-center pe-md-4" style="white-space: nowrap; width: 1%;">
                                    <form method="POST" action="/products/variants/<?= $variant['id']; ?>/delete" style="display: inline;"
                                          onsubmit="return confirm('Delete this variant?');">
                                        <button type="submit" class="btn btn-link text-danger p-0" title="Delete Variant">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted mb-3"><i class="fas fa-cube fa-3x"></i></p>
                                <p class="text-muted">No variants yet. Add one to get started!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Variant Form (Collapsible) -->
    <div class="collapse" id="addVariantForm">
        <div class="card border-0 mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Add New Variant</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $field => $message): ?>
                                <li><?= htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/products/<?= $product['id']; ?>/variants">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU *</label>
                                <input type="text" class="form-control <?= isset($errors['sku']) ? 'is-invalid' : ''; ?>"
                                       id="sku" name="sku" readonly
                                       value="<?= htmlspecialchars($old['sku'] ?? ''); ?>"
                                       placeholder="Auto-generated">
                                <?php if (isset($errors['sku'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['sku']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="size" class="form-label">Size *</label>
                                <div class="input-group">
                                    <select class="form-select <?= isset($errors['size']) ? 'is-invalid' : ''; ?>"
                                            id="sizeSelect" name="size">
                                        <option value="">-- Select --</option>
                                        <option value="S" <?= ($old['size'] ?? '') === 'S' ? 'selected' : ''; ?>>S</option>
                                        <option value="M" <?= ($old['size'] ?? '') === 'M' ? 'selected' : ''; ?>>M</option>
                                        <option value="L" <?= ($old['size'] ?? '') === 'L' ? 'selected' : ''; ?>>L</option>
                                        <option value="XL" <?= ($old['size'] ?? '') === 'XL' ? 'selected' : ''; ?>>XL</option>
                                        <option value="2XL" <?= ($old['size'] ?? '') === '2XL' ? 'selected' : ''; ?>>2XL</option>
                                        <option value="3XL" <?= ($old['size'] ?? '') === '3XL' ? 'selected' : ''; ?>>3XL</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" id="addSizeBtn" title="Add new option">+</button>
                                </div>
                                <?php if (isset($errors['size'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['size']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Initial Stock *</label>
                                <input type="number" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : ''; ?>"
                                       id="stock" name="stock" required
                                       value="<?= htmlspecialchars($old['stock'] ?? '0'); ?>"
                                       placeholder="0">
                                <?php if (isset($errors['stock'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['stock']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="variant_price" class="form-label">Override Price (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="1" min="0" class="form-control"
                                           id="variant_price" name="variant_price"
                                           value="<?= htmlspecialchars($old['variant_price'] ?? ''); ?>"
                                           placeholder="Leave empty for base price">
                                </div>
                                <small class="text-muted">Different price for this variant</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="mb-3 w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Add Variant
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="px-3 px-md-0 d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-2">
        <div class="d-flex flex-column flex-md-row gap-2">
            <a href="/products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
            <a href="/products/edit/<?= $product['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-edit"></i> Edit Product
            </a>
        </div>
        <form method="POST" action="/products/delete/<?= $product['id']; ?>"
              onsubmit="return confirm('Delete this product? This cannot be undone.');">
            <button type="submit" class="btn btn-outline-danger w-100 w-md-auto">
                <i class="fas fa-trash"></i> Delete Product
            </button>
        </form>
    </div>
</div>

<script>
function showStockInput(variantId) {
    document.querySelector('.stock-display-' + variantId).classList.add('d-none');
    document.querySelector('.stock-inline-' + variantId).classList.remove('d-none');
    document.querySelector('.stock-inline-' + variantId + ' input[name="stock"]').focus();
}

function hideStockInput(variantId) {
    document.querySelector('.stock-display-' + variantId).classList.remove('d-none');
    document.querySelector('.stock-inline-' + variantId).classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', function() {
    const sizeSelect = document.getElementById('sizeSelect');
    const skuInput = document.getElementById('sku');
    const productId = <?= $product['id']; ?>;
    const productPrefix = <?= json_encode(strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $product['name']), 0, 4))); ?>;

    function generateSKU() {
        const size = sizeSelect.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (size) {
            skuInput.value = `${productPrefix}-${productId}-${size}`;
        } else {
            skuInput.value = '';
        }
    }

    sizeSelect.addEventListener('change', generateSKU);

    // Trigger SKU generation if a size is already selected (e.g. after validation error)
    if (sizeSelect.value) generateSKU();

    // Add new size option
    document.getElementById('addSizeBtn').addEventListener('click', function() {
        const newVal = prompt('Enter new size/variant option:');
        if (newVal && newVal.trim()) {
            const val = newVal.trim();
            // Check it's not already in the list
            const exists = Array.from(sizeSelect.options).some(o => o.value.toLowerCase() === val.toLowerCase());
            if (!exists) {
                const option = new Option(val, val, true, true);
                sizeSelect.add(option);
            } else {
                // Just select the existing one
                for (let o of sizeSelect.options) {
                    if (o.value.toLowerCase() === val.toLowerCase()) {
                        o.selected = true;
                        break;
                    }
                }
            }
            generateSKU();
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
