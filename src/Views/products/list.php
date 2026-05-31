<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3 px-md-0">
        <h1>Products</h1>
        <a href="/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
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
            <form method="GET" action="/products" class="row g-3" id="filterForm">
                <div class="col-md-8">
                    <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']); ?>" <?= $selected_category === $cat['category'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="align-middle">
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <a href="/products/<?= $product['id']; ?>/variants" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($product['name']); ?>
                                    </a>
                                </td>
                                <td class="align-middle">৳<?= number_format($product['base_price'], 0); ?></td>
                                <td class="align-middle">
                                    <span class="badge bg-info text-dark">
                                        <?= (int)$product['total_stock']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <p class="text-muted mb-3"><i class="fas fa-inbox fa-3x"></i></p>
                                <p class="text-muted mb-0">No products found. <a href="/products/create">Create one now</a></p>
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
                    <li class="page-item">
                        <a class="page-link" href="/products?page=1&per_page=<?= $perPage; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $selected_category ? '&category=' . urlencode($selected_category) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/products?page=<?= $page - 1; ?>&per_page=<?= $perPage; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $selected_category ? '&category=' . urlencode($selected_category) : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="/products?page=<?= $i; ?>&per_page=<?= $perPage; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $selected_category ? '&category=' . urlencode($selected_category) : ''; ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="/products?page=<?= $page + 1; ?>&per_page=<?= $perPage; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $selected_category ? '&category=' . urlencode($selected_category) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/products?page=<?= $totalPages; ?>&per_page=<?= $perPage; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $selected_category ? '&category=' . urlencode($selected_category) : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
// Live search on every keystroke
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchValue = this.value.trim();

    // If search is cleared, submit immediately
    if (searchValue === '') {
        document.getElementById('filterForm').submit();
        return;
    }

    // Debounce search: wait 300ms before submitting
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 300);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
