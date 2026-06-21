# KIMS — Project Reference

Jersey Store **Inventory Management System**. A PHP 7.4+ web application with a hand-rolled MVC framework. No external PHP framework (no Laravel, Symfony, etc.).

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 7.4+ |
| Web server | PHP built-in (`php -S localhost:8000 router.php`) or Apache/Nginx |
| Database | MySQL 5.7+ / MariaDB |
| DB access | PDO (prepared statements only) |
| Frontend | Bootstrap 5, vanilla JS |
| PHP dependencies | `phpoffice/phpspreadsheet` ^1.25 (Excel export), `phpmailer/phpmailer` ^6.9 (password reset emails) |
| Dev dependencies | `phpunit/phpunit` ^9.5 |
| E2E testing | Playwright ^1.60 (Node.js) |
| Autoloading | PSR-4: `App\` → `src/` |

---

## Directory Layout

```
F:/KIMS/
├── public/               ← Web root (only publicly accessible dir)
│   ├── index.php         ← Entry point: config load, autoloader, auth check, route registration
│   ├── css/style.css
│   ├── js/main.js
│   └── uploads/products/ ← User-uploaded product images
├── src/
│   ├── Core/
│   │   ├── Auth.php      ← Session auth, CSRF, password hashing (bcrypt cost 12)
│   │   ├── Database.php  ← PDO singleton, query/fetch/insert/update/delete helpers
│   │   └── Router.php    ← URL dispatch, {param} extraction via regex
│   ├── Controllers/
│   │   ├── Controller.php        ← Base: render(), redirect(), validate(), setFlash(), jsonResponse()
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ExpenseController.php
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   └── ReportController.php
│   ├── Models/
│   │   ├── Model.php             ← Base: all(), find(), where(), create(), update(), delete(), paginate()
│   │   ├── Expense.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── StockAdjustment.php
│   │   └── User.php
│   └── Views/
│       ├── layouts/              ← header.php, sidebar.php, footer.php
│       ├── auth/login.php
│       ├── dashboard/index.php
│       ├── expenses/             ← list, form, show
│       ├── orders/               ← list, form, show, exchange
│       ├── products/             ← list, form, variants
│       └── reports/              ← dashboard, revenue, products, expenses, inventory, stock_shortage
├── config/
│   ├── config.php        ← APP constants
│   ├── database.php      ← DB credentials from env vars (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)
│   ├── .env              ← Local env file (not committed)
│   └── .env.example      ← Template
├── migrations/           ← Sequential SQL files (apply in order)
│   ├── 001_initial_schema.sql
│   ├── 002_feature_updates.sql
│   ├── 002_order_delivery_timestamps.sql
│   ├── 003_order_soft_delete.sql
│   ├── 003_sourcing_price.sql
│   ├── 004_exchange_orders.sql
│   ├── 004_remove_color_reorder_default.sql
│   ├── 005_order_stock_issue.sql
│   └── 006_delivery_status_package_ready.sql
├── deploy/               ← Deployment artifacts and guide
├── router.php            ← URL rewriting shim for PHP built-in server
├── install.php           ← Web-based DB installer
└── composer.json
```

---

## Request Flow

```
HTTP Request
  → public/index.php       (session_start, config, autoloader, DB init, session timeout check)
  → Router::dispatch()     (match URL+method → "ControllerName@method")
  → Controller method      (Auth::requireLogin(), business logic, model calls)
  → $this->render('view/path', $data)
  → src/Views/**/*.php     (Bootstrap 5 templates, data extracted into scope)
```

Routes are all registered in `public/index.php`. URL params use `{id}` syntax; Router extracts them into `$_GET`.

---

## Database Schema (8 tables)

| Table | Key columns / notes |
|---|---|
| `users` | id, email (unique), password_hash, name, is_active, last_login |
| `products` | id, name, category, base_price, description, image_url, is_active |
| `product_variants` | id, product_id (FK), size, color, sku (unique), stock, reorder_point, variant_price |
| `orders` | id, order_number, customer info, delivery_address, status\*, payment_method\*, payment_status\*, delivery_status\*\*, pickup_person_name, total_amount, tracking_number, shipped_at, delivered_at, is_deleted, has_stock_issue, exchange_for_order_id |
| `order_items` | id, order_id, product_id, variant_id, quantity, unit_price, line_total, is_return, stock_deducted |
| `expenses` | id, category\*, amount, expense_date, description, notes, attachment_url, created_by |
| `stock_adjustments` | id, variant_id, adjustment_quantity, reason, adjusted_by |
| `password_reset_tokens` | id, user_id, token, expires_at |

**`orders.status`** ENUM: `pending`, `processing`, `shipped`, `in_transit`, `delivered`, `returned`

**`orders.delivery_status`** ENUM: `pending`, `package_ready`, `courier_pickup`, `personal_pickup`, `in_transit`, `delivered`, `on_hold`, `cancelled`, `returned`

**`orders.payment_method`** ENUM: `cod`, `bkash`, `bank`

**`expenses.category`** ENUM: `cogs`, `operational`, `shipping`, `marketing`, `other`

---

## Core Patterns

### Database access
```php
$db = Database::getInstance();
$db->query($sql, $params);        // returns PDOStatement
$db->fetch($sql, $params);        // returns ?array
$db->fetchAll($sql, $params);     // returns array
$db->insert($table, $data);       // returns lastInsertId (int)
$db->update($table, $data, $where, $conditions);
$db->beginTransaction(); $db->commit(); $db->rollback();
```

### Model base class
```php
$model->all();
$model->find($id);                // returns ?array
$model->where($col, $op, $val);   // returns array
$model->create($data);            // returns new id
$model->update($id, $data);
$model->paginate($page, $perPage); // returns {items, total, page, perPage, totalPages}
```
Models extend Model, set `$this->table`, and add custom query methods.

### Controller helpers
```php
$this->render('orders/list', ['key' => $val]);
$this->redirect('/orders');
$this->setFlash('success', 'Order saved');
$this->getFlash();                // consumed once
$this->jsonResponse(['ok' => true], 200);
$this->validate($_POST, ['name' => 'required|min:2|max:255', 'email' => 'email']);
Auth::requireLogin();             // redirects to /auth/login if not authed
```

### CSRF
Every form must include:
```php
<?= Auth::generateCSRFToken() ?>
```
Validate on POST/PUT/DELETE:
```php
Auth::validateCSRFToken($_POST['csrf_token'])
```

### Views
- Layouts: `src/Views/layouts/header.php`, `sidebar.php`, `footer.php`
- Data variables are `extract()`-ed in Controller::render() — pass flat arrays
- Escape output: `htmlspecialchars($value)`

---

## All Routes

```
GET/POST  auth/login
GET/POST  auth/register
GET       auth/logout
GET/POST  auth/forgot-password
GET/POST  auth/reset-password/{token}

GET       /                         → DashboardController@index
GET       dashboard

GET       products
GET/POST  products/create           → list/store
GET/POST  products/edit/{id}        → edit/update
POST      products/delete/{id}
GET       products/{id}
GET/POST  products/{id}/variants    → list/storeVariant
POST      products/variants/{variantId}/delete
POST      products/variants/{variantId}/updateStock

GET       orders
GET/POST  orders/create             → list/store
GET       orders/{id}
GET/POST  orders/edit/{id}          → edit/update
POST      orders/{id}/status        → updateStatus
POST      orders/{id}/delete        (soft delete)
POST      orders/{id}/adjustStock
GET       orders/exchange/{id}      → exchange form
POST      orders/exchange/store/{id} → storeExchange

GET       expenses
GET/POST  expenses/create           → list/store
GET       expenses/{id}
GET/POST  expenses/edit/{id}        → edit/update
POST      expenses/delete/{id}

GET       reports                   → reports dashboard
GET       reports/revenue
GET       reports/products          → top products
GET       reports/expenses
GET       reports/inventory
GET       reports/stock-shortage
POST      reports/export            → Excel download
```

---

## Key Features

- **Products & Variants**: Products have size+color variants with individual SKU, stock level, and reorder point. Stock is deducted when orders are placed and restored on soft-delete or manual adjustment.
- **Orders**: Full lifecycle with two status axes — `status` (fulfillment) and `delivery_status` (courier tracking). Supports payment method/status tracking, soft delete, and stock adjustment post-sale.
- **Exchange Orders**: An order can be linked to a return order via `exchange_for_order_id`. Exchange items are flagged `is_return = 1` in `order_items`.
- **Stock Issues**: `has_stock_issue` flag is set when stock was insufficient at order time; `order_items.stock_deducted` tracks whether each line actually reduced stock.
- **Expenses**: Categorised business expenses with optional file attachment and date range queries.
- **Reports**: Revenue, top products, expense breakdown, inventory levels, stock shortage (variants below `reorder_point`). Exportable to Excel via PhpSpreadsheet.
- **Auth**: Session-based, 1-hour timeout, bcrypt passwords, CSRF on all mutations, password reset via emailed token (PHPMailer).

---

## Migrations (apply in order)

```
001_initial_schema.sql              ← base schema + admin seed user
002_feature_updates.sql             ← payment_method, payment_status, delivery_status, pickup_person_name
002_order_delivery_timestamps.sql   ← shipped_at, delivered_at timestamps
003_order_soft_delete.sql           ← is_deleted column
003_sourcing_price.sql              ← sourcing_price on product_variants
004_exchange_orders.sql             ← exchange_for_order_id, order_items.is_return
004_remove_color_reorder_default.sql
005_order_stock_issue.sql           ← has_stock_issue, order_items.stock_deducted
006_delivery_status_package_ready.sql ← adds package_ready to delivery_status ENUM
```

**Default admin credentials:** `admin@jerseystore.com` / `admin123`

---

## Development Quick Reference

```bash
# Start dev server
php -S localhost:8000 router.php

# Install PHP deps
composer install

# Install Node deps (Playwright)
npm install

# Database setup
php test-db.php                         # test connection
# then navigate to http://localhost:8000/install.php
# or: mysql -u root -p kims_db < migrations/001_initial_schema.sql
```

**Environment variables** (copy `config/.env.example` to `config/.env`):
```
DB_HOST=localhost
DB_NAME=inventory_mgmt
DB_USER=root
DB_PASSWORD=
APP_URL=http://localhost:8000
```

---

## Security Checklist

- All SQL via PDO prepared statements — no string interpolation
- CSRF token on every POST/PUT/DELETE form
- `htmlspecialchars()` on all view output
- `Auth::requireLogin()` at top of every controller method
- Passwords hashed with `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])`
- `public/` is the only web-accessible directory
