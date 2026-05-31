<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Inventory Management'); ?> - Kitzoholic</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <!-- Sidebar backdrop (mobile only) -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <button class="btn btn-dark d-md-none me-2 p-1" id="sidebarToggle" aria-label="Toggle navigation">
                <i class="fas fa-bars fs-5"></i>
            </button>
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-warehouse"></i> Kitzoholic
            </a>
            <?php if ($user = \App\Core\Auth::getCurrentUser()): ?>
                <div class="dropdown ms-auto">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/auth/logout">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="nav-link text-white ms-auto" href="/auth/login">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        (function () {
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            function openSidebar() {
                document.querySelector('.sidebar').classList.add('show');
                backdrop.classList.add('show');
            }
            function closeSidebar() {
                document.querySelector('.sidebar').classList.remove('show');
                backdrop.classList.remove('show');
            }

            toggle.addEventListener('click', function () {
                document.querySelector('.sidebar').classList.contains('show') ? closeSidebar() : openSidebar();
            });
            backdrop.addEventListener('click', closeSidebar);
        })();
    </script>

    <!-- Flash Messages -->
    <?php if ($flash = $GLOBALS['flash'] ?? null): ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show m-3" role="alert">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
