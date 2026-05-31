# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KIMS is a Jersey Store Inventory Management System — a PHP 7.4+ web application using a hand-rolled MVC framework (no external PHP framework). It manages products with variants, orders, expenses, and analytics for small teams.

## Development Commands

**Start development server:**
```bash
php -S localhost:8000 router.php
```
The `router.php` file handles `.htaccess`-style URL rewriting for the built-in server. The public web root is `public/`.

**Install PHP dependencies:**
```bash
composer install
```

**Install Node dependencies (Playwright for testing):**
```bash
npm install
```

**Database setup:**
```bash
# Option 1: Web installer (run dev server first)
# Navigate to http://localhost:8000/install.php

# Option 2: Import schema directly
mysql -u root -p kims_db < migrations/001_initial_schema.sql

# Option 3: Test DB connection
php test-db.php
```

**Default admin credentials after schema import:**
- Email: `admin@jerseystore.com`
- Password: `admin123`

## Architecture

### Request Flow

```
HTTP Request
  → public/index.php        (entry point: loads config, autoloader, DB, auth check)
  → src/Core/Router.php     (matches URL to Controller@method, extracts {params})
  → src/Controllers/*.php   (business logic, calls models and renders views)
  → src/Views/**/*.php      (Bootstrap 5 HTML templates)
```

All routes are registered in `public/index.php`. URL parameters use `{id}` syntax in route patterns and are extracted by `Router::matchRoute()`.

### Key Directories

- `public/` — Web root. Only this directory should be publicly accessible. Contains `index.php`, CSS, JS, and uploaded files.
- `src/Core/` — Framework core: `Database.php` (PDO singleton), `Router.php` (URL dispatch), `Auth.php` (session + CSRF).
- `src/Models/` — Data models extending `Model.php` base class. Each model maps to a DB table with built-in CRUD, `where()`, and `paginate()`.
- `src/Controllers/` — Controllers extending `Controller.php`. Use `$this->render(view, data)`, `$this->redirect()`, `$this->validate()`.
- `src/Views/` — PHP templates. Layout partials in `views/layouts/` (header, sidebar, footer).
- `config/` — `config.php` (app constants), `database.php` (DB credentials via environment variables).
- `migrations/` — SQL schema files. `001_initial_schema.sql` is the full schema with seed data.

### Database

8 tables: `users`, `products`, `product_variants`, `orders`, `order_items`, `expenses`, `stock_adjustments`, `password_reset_tokens`.

Credentials are read from environment variables in `config/database.php`. Copy `config/.env.example` to `config/.env` and fill in values, or set env vars directly.

### PSR-4 Autoloading

Namespace `App\` maps to `src/`. Example: `App\Controllers\ProductController` → `src/Controllers/ProductController.php`.

### Security Conventions

- All DB queries go through the `Database` singleton using prepared statements — never interpolate user input into SQL.
- CSRF tokens are validated on all POST/PUT/DELETE routes via `Auth::validateCSRFToken()`. Include `<?= Auth::generateCSRFToken() ?>` in every form.
- Output is escaped with `htmlspecialchars()` in views to prevent XSS.
- Passwords are hashed with `Auth::hashPassword()` (bcrypt cost 12).

## Implementation Status

The foundation (Core, Models, routing, auth, views layout, DB schema) is complete. Controllers have method stubs but most feature logic (CRUD forms, order status workflow, report queries, exports) still needs to be implemented. See `IMPLEMENTATION_GUIDE.md` for the phased plan.
