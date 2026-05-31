<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="mb-4 px-3 px-md-0">
        <h1><?= $product ? 'Edit Product' : 'Create Product'; ?></h1>
        <p class="text-muted">
            <?= $product ? 'Update product information and image' : 'Add a new product to the inventory'; ?>
        </p>
    </div>

    <div class="card border-0">
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

            <form method="POST" action="<?= $product ? '/products/update/' . $product['id'] : '/products'; ?>" enctype="multipart/form-data">
                <div class="row g-4">

                    <!-- Product Name -->
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : ''; ?>"
                               id="name" name="name" required
                               value="<?= htmlspecialchars($product['name'] ?? $old['name'] ?? ''); ?>"
                               placeholder="e.g., Jersey XL">
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Category -->
                    <?php $currentCategory = $product['category'] ?? $old['category'] ?? ''; ?>
                    <div class="col-12 col-md-6">
                        <label for="categorySelect" class="form-label">Category *</label>
                        <input type="hidden" id="categoryInput" name="category" value="<?= htmlspecialchars($currentCategory); ?>">
                        <select id="categorySelect" class="form-select <?= isset($errors['category']) ? 'is-invalid' : ''; ?>"
                                onchange="handleCategoryChange(this)">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']); ?>"
                                    <?= $currentCategory === $cat['category'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__new__">+ Create New Category</option>
                        </select>
                        <input type="text" id="newCategoryInput" class="form-control mt-2 <?= isset($errors['category']) ? 'is-invalid' : ''; ?>"
                               placeholder="Enter new category name" style="display:none;">
                        <?php if (isset($errors['category'])): ?>
                            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['category']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Selling Price -->
                    <div class="col-12 col-md-6">
                        <label for="base_price" class="form-label">Selling Price (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="1" min="0" class="form-control <?= isset($errors['base_price']) ? 'is-invalid' : ''; ?>"
                                   id="base_price" name="base_price" required
                                   value="<?= htmlspecialchars(isset($product['base_price']) ? (int)$product['base_price'] : ($old['base_price'] ?? '')); ?>"
                                   placeholder="0">
                        </div>
                        <?php if (isset($errors['base_price'])): ?>
                            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['base_price']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Sourcing Price -->
                    <div class="col-12 col-md-6">
                        <label for="sourcing_price" class="form-label">Sourcing Price (৳)</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="1" min="0" class="form-control"
                                   id="sourcing_price" name="sourcing_price"
                                   value="<?= htmlspecialchars(isset($product['sourcing_price']) && $product['sourcing_price'] !== null ? (int)$product['sourcing_price'] : ($old['sourcing_price'] ?? '')); ?>"
                                   placeholder="0">
                        </div>
                        <small class="text-muted">What you paid to source this product</small>
                    </div>

                    <!-- Description -->
                    <div class="col-12 col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"
                                  placeholder="Product description, features, notes..."></textarea>
                        <?php if (isset($old['description'])): ?>
                            <script>document.getElementById('description').textContent = <?= json_encode($old['description']); ?>;</script>
                        <?php elseif ($product): ?>
                            <script>document.getElementById('description').textContent = <?= json_encode($product['description']); ?>;</script>
                        <?php endif; ?>
                    </div>

                    <!-- Product Image -->
                    <div class="col-12 col-md-6">
                        <label for="image" class="form-label">Product Image</label>
                        <?php if ($product && $product['image_url']): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($product['image_url']); ?>"
                                     alt="<?= htmlspecialchars($product['name']); ?>"
                                     class="img-thumbnail" style="max-width: 180px; height: auto;">
                                <small class="d-block text-muted mt-1">Current image — upload a new one to replace it</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control <?= isset($errors['image']) ? 'is-invalid' : ''; ?>"
                               id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <?php if (isset($errors['image'])): ?>
                            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['image']); ?></div>
                        <?php endif; ?>
                        <small class="text-muted">JPG, PNG or GIF · max 5 MB</small>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $product ? 'Update Product' : 'Create Product'; ?>
                            </button>
                            <a href="/products" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                        <?php if ($product): ?>
                            <form method="POST" action="/products/delete/<?= $product['id']; ?>"
                                  onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete Product
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleCategoryChange(select) {
    const newInput = document.getElementById('newCategoryInput');
    const hiddenInput = document.getElementById('categoryInput');
    if (select.value === '__new__') {
        newInput.style.display = 'block';
        newInput.focus();
        hiddenInput.value = '';
    } else {
        newInput.style.display = 'none';
        hiddenInput.value = select.value;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('categorySelect');
    const newInput = document.getElementById('newCategoryInput');
    const hiddenInput = document.getElementById('categoryInput');
    const currentValue = hiddenInput.value;

    // If current value isn't in the select options, it's a previously-entered new category
    const existsInOptions = Array.from(select.options).some(o => o.value === currentValue && o.value !== '__new__' && o.value !== '');
    if (currentValue && !existsInOptions) {
        select.value = '__new__';
        newInput.style.display = 'block';
        newInput.value = currentValue;
    }

    // On submit, copy new category text into the hidden input
    select.closest('form').addEventListener('submit', function () {
        if (select.value === '__new__') {
            hiddenInput.value = newInput.value.trim();
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
