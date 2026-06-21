<!-- Sidebar Navigation -->
<nav class="col-md-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>

            <hr class="my-2">

            <li class="nav-item">
                <span class="nav-link text-muted small"><strong>Inventory</strong></span>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/products">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/products/create">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </li>

            <hr class="my-2">

            <li class="nav-item">
                <span class="nav-link text-muted small"><strong>Orders</strong></span>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/orders?delivery_status=pending">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/orders/create">
                    <i class="fas fa-plus"></i> New Order
                </a>
            </li>

            <hr class="my-2">

            <li class="nav-item">
                <span class="nav-link text-muted small"><strong>Finances</strong></span>
            </li>
            <li class="nav-item">
                <span class="nav-link ps-4 text-muted disabled" style="cursor:not-allowed;opacity:0.5;">
                    <i class="fas fa-credit-card"></i> Expenses
                </span>
            </li>
            <li class="nav-item">
                <span class="nav-link ps-4 text-muted disabled" style="cursor:not-allowed;opacity:0.5;">
                    <i class="fas fa-plus"></i> Log Expense
                </span>
            </li>

            <hr class="my-2">

            <li class="nav-item">
                <span class="nav-link text-muted small"><strong>Reports</strong></span>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/reports">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/reports/revenue">
                    <i class="fas fa-chart-bar"></i> Revenue
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/reports/products">
                    <i class="fas fa-trophy"></i> Top Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/reports/expenses">
                    <i class="fas fa-pie-chart"></i> Expenses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link ps-4" href="/reports/inventory">
                    <i class="fas fa-cubes"></i> Inventory
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<main class="col-md-10 ms-sm-auto px-md-4">
