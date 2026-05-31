# Brief: Jersey Store Inventory Management System - PHP + MySQL (cPanel Hosting)

## Project Overview
Build an internal inventory management web application for a growing online jersey store. The system will track products (with variants), manage orders from creation to delivery, track detailed expenses, and generate advanced analytics reports. This system is designed to run on standard cPanel hosting with PHP and MySQL, providing a solid foundation before migration to .NET backend.

---

## Core Requirements

### 1. Product Inventory Management
- Add/edit/delete products (jerseys initially, expandable to other sports gear)
- **Product variants:** Size, Color, SKU
- Track individual stock levels per variant
- Reorder point alerts (low stock warnings)
- Product categories & filtering
- Product image upload & management
- Bulk product operations (batch edit stock levels)
- Product search with multiple filters

### 2. Order Management
- Create orders with multiple line items (products + quantities)
- **Order status tracking workflow:**
  - `Pending` → `Processing` → `Shipped` → `In Transit` → `Delivered` → Optional: `Returned/Refunded`
- Customer info (name, email, phone, delivery address)
- Order search & filter by status, date, customer, order ID
- Bulk order actions (mark as shipped, etc.)
- Order history & details view
- Order notes & internal comments
- Manual status updates through admin dashboard
- Order summary with totals

### 3. Expense Tracking
- Log expenses with detailed categorization:
  - **COGS** (Cost of Goods Sold) - supplier purchases
  - **Operational** - rent, utilities, staff costs
  - **Shipping** - courier, packaging materials
  - **Marketing** - ads, promotions, campaigns
  - **Other** - miscellaneous expenses
- Track expense date, amount, category, notes, description
- Monthly/quarterly expense summaries
- Expense search & filter by date range, category
- Edit/delete expense records
- Receipt/attachment storage (optional file upload)

### 4. Advanced Reporting & Analytics
- **Dashboards with charts:**
  - Revenue trends (line chart, daily/weekly/monthly views)
  - Top-selling products & variants (bar chart)
  - Inventory status (low stock alerts, overstock warnings)
  - Expense breakdown by category (pie/donut chart)
  - Profit margins by product
  - Order status distribution (processing, shipped, delivered, etc.)
- **Comparisons:** Period-over-period (this month vs last month, YoY)
- **Forecasting:** Basic sales forecast for next 30 days using historical data
- **Export:** CSV/Excel export for reports and data
- **Custom date range filtering** for all reports
- Dashboard widgets with key metrics (total revenue, pending orders, low stock items)

### 5. Authentication & Access Control
- User login system (email/password)
- Session management with timeout
- Basic user registration (admin-controlled)
- All authenticated users have full access (no role-based restrictions)
- Password reset functionality
- Remember me functionality (optional cookies)
- Security: SQL injection prevention, CSRF protection, password hashing

### 6. Data Persistence & Reliability
- MySQL database for data persistence
- Automated daily backups (via cPanel backup)
- Data validation before database writes
- Transaction support for critical operations (order creation)
- Database optimization (indexes on frequently queried columns)

---

## Tech Stack

### Backend
- **PHP 7.4+** (latest stable compatible with cPanel)
- **Architecture:** MVC pattern for clean code organization
- **Database:** MySQL 5.7+ or MariaDB
- **Web Server:** Apache with mod_rewrite

### Frontend
- **HTML5** with semantic markup
- **CSS3** with Bootstrap 5 (responsive grid, components)
- **JavaScript (Vanilla or jQuery)** for interactivity
- **Charts Library:** Chart.js or Highcharts (lightweight, CDN-based)

### Directory Structure
- Object-Oriented PHP with namespaces
- PDO for database abstraction
- Prepared statements for security

### Additional Libraries
- **PHPMailer** (for password reset emails)
- **phpoffice/phpexcel or PhpSpreadsheet** (for CSV/Excel export)
- **Bootstrap 5** (CSS framework)
- **Chart.js** (charting library via CDN)
- **Moment.js** (date formatting, optional)

---

## Database Structure (MySQL Schema)

### users table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  is_active BOOLEAN DEFAULT TRUE,
  INDEX idx_email (email)
);
```

### products table
```sql
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  base_price DECIMAL(10, 2) NOT NULL,
  description TEXT,
  image_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE,
  INDEX idx_category (category),
  INDEX idx_name (name)
);
```

### product_variants table
```sql
CREATE TABLE product_variants (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  size VARCHAR(50) NOT NULL,
  color VARCHAR(100) NOT NULL,
  sku VARCHAR(100) UNIQUE NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  reorder_point INT NOT NULL DEFAULT 10,
  variant_price DECIMAL(10, 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_id (product_id),
  INDEX idx_sku (sku),
  UNIQUE KEY unique_variant (product_id, size, color)
);
```

### orders table
```sql
CREATE TABLE orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255),
  customer_phone VARCHAR(20),
  delivery_address TEXT NOT NULL,
  status ENUM('pending', 'processing', 'shipped', 'in_transit', 'delivered', 'returned') DEFAULT 'pending',
  total_amount DECIMAL(10, 2) NOT NULL,
  notes TEXT,
  tracking_number VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  shipped_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  INDEX idx_order_number (order_number)
);
```

### order_items table
```sql
CREATE TABLE order_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  variant_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10, 2) NOT NULL,
  line_total DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (variant_id) REFERENCES product_variants(id),
  INDEX idx_order_id (order_id)
);
```

### expenses table
```sql
CREATE TABLE expenses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category ENUM('cogs', 'operational', 'shipping', 'marketing', 'other') NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  expense_date DATE NOT NULL,
  description VARCHAR(500),
  notes TEXT,
  attachment_url VARCHAR(500),
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_category (category),
  INDEX idx_expense_date (expense_date),
  INDEX idx_created_at (created_at)
);
```

### stock_adjustments table (for audit trail)
```sql
CREATE TABLE stock_adjustments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  variant_id INT NOT NULL,
  adjustment_quantity INT NOT NULL,
  reason VARCHAR(255),
  adjusted_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
  FOREIGN KEY (adjusted_by) REFERENCES users(id),
  INDEX idx_variant_id (variant_id),
  INDEX idx_created_at (created_at)
);
```

---

## Project Structure (Recommended)

```
inventory-management/
├── public/
│   ├── index.php (router)
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── style.css
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── chart.min.js
│   │   └── main.js
│   └── images/
├── src/
│   ├── Core/
│   │   ├── Database.php (PDO connection)
│   │   ├── Router.php (URL routing)
│   │   └── Auth.php (authentication)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Expense.php
│   │   └── StockAdjustment.php
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── ExpenseController.php
│   │   ├── ReportController.php
│   │   └── DashboardController.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   ├── sidebar.php
│   │   │   └── footer.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   └── forgot-password.php
│   │   ├── products/
│   │   │   ├── list.php
│   │   │   ├── form.php
│   │   │   └── variants.php
│   │   ├── orders/
│   │   │   ├── list.php
│   │   │   ├── form.php
│   │   │   ├── details.php
│   │   │   └── status-timeline.php
│   │   ├── expenses/
│   │   │   ├── list.php
│   │   │   ├── form.php
│   │   │   └── stats.php
│   │   ├── reports/
│   │   │   ├── dashboard.php
│   │   │   ├── charts.php
│   │   │   └── export.php
│   │   └── dashboard/
│   │       └── index.php
│   └── Services/
│       ├── ProductService.php
│       ├── OrderService.php
│       ├── ExpenseService.php
│       ├── ReportService.php
│       ├── ExportService.php
│       └── MailService.php
├── uploads/
│   ├── products/
│   ├── receipts/
│   └── .htaccess (prevent direct access)
├── config/
│   ├── database.php (connection config)
│   ├── config.php (app constants)
│   └── .env.example
├── migrations/
│   └── 001_initial_schema.sql
├── .htaccess (URL rewriting)
├── composer.json
├── README.md
└── .gitignore
```

---

## Key Files & Setup

### .htaccess (for URL rewriting)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php?url=$1 [QSA,L]
</IfModule>
```

### public/index.php (router)
```php
<?php
session_start();
require_once '../src/Core/Database.php';
require_once '../src/Core/Router.php';
require_once '../src/Core/Auth.php';

// Route handling logic here
```

### config/database.php
```php
<?php
// Database configuration (environment-specific)
define('DB_HOST', 'localhost');
define('DB_USER', 'your_cpanel_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database_name');
define('DB_PORT', 3306);
```

---

## MVP Scope (Must-Have for Launch)

- ✅ User authentication (login/register/logout)
- ✅ Product inventory management (CRUD with variants)
- ✅ Product categorization & filtering
- ✅ Order creation with multiple items
- ✅ Order status tracking (manual status updates)
- ✅ Order search & filtering
- ✅ Expense logging with 5 categories
- ✅ Basic dashboard with key metrics
- ✅ Charts & analytics (revenue, top products, expenses)
- ✅ Period-over-period comparisons
- ✅ Basic 30-day sales forecasting
- ✅ CSV export functionality
- ✅ Responsive design (Bootstrap 5)
- ✅ Session management & security (CSRF, SQL injection prevention)

---

## Nice-to-Have (Future Iterations)

- Role-based access control (admin, manager, staff)
- Automated email notifications on order status changes
- Advanced forecasting with algorithms
- Multi-warehouse support
- Supplier management portal
- Barcode scanning via camera
- PDF invoice generation
- Bulk import functionality (CSV)
- Custom report builder
- Activity logs & audit trails
- Two-factor authentication
- API for mobile app
- Integration with shipping providers

---

## Success Criteria

- 5-6 team members can log in and manage inventory seamlessly
- Generate monthly reports within 2-3 seconds
- Real-time data visible across all active user sessions
- Zero downtime with cPanel auto-backups
- Clean, maintainable code structured for easy .NET migration
- All pages load within 2 seconds
- Mobile-responsive design for tablet & mobile access
- Secure against common vulnerabilities (SQL injection, XSS, CSRF)

---

## Development & Deployment Workflow

### Local Development
```bash
# Requirements
- PHP 7.4+ local environment (XAMPP, Laragon, or Docker)
- MySQL 5.7+ or MariaDB
- Composer (for dependency management)
- Git for version control

# Setup
1. Clone repository
2. Copy config/.env.example to config/.env
3. Update database credentials in .env
4. Run: composer install
5. Import migrations/001_initial_schema.sql into MySQL
6. Start local PHP server: php -S localhost:8000 -t public/
7. Access: http://localhost:8000
```

### cPanel Deployment
```bash
# Prerequisites
- cPanel hosting with PHP 7.4+ and MySQL
- SSH access (or File Manager)
- Domain/subdomain configured

# Deployment Steps
1. Via cPanel File Manager or FTP:
   - Upload all files to public_html/ or subdirectory
   - Ensure correct permissions (755 for folders, 644 for files)

2. Via SSH:
   - Clone repository: git clone <repo_url>
   - Run: composer install
   - Set permissions: chmod -R 755 .

3. Create MySQL Database:
   - Use cPanel MySQL Database Wizard
   - Create user with password
   - Grant all privileges

4. Configure .env
   - Update database credentials in config/database.php
   - Change APP_DEBUG to false

5. Import Database Schema
   - Use phpMyAdmin in cPanel
   - Import migrations/001_initial_schema.sql

6. Set Up Automated Backups
   - Use cPanel Backup Wizard
   - Schedule daily/weekly backups

7. Enable SSL
   - Use cPanel AutoSSL or Let's Encrypt

8. Test Application
   - Access via browser
   - Test login, CRUD operations, reports
```

---

## Timeline Suggestion

- **Week 1-2:** 
  - Database setup & schema creation
  - Authentication system (login/register)
  - Project structure & basic routing
  - Product management (CRUD + variants)

- **Week 2-3:** 
  - Product filtering & search
  - Order management (creation & status)
  - Order details view & editing

- **Week 3-4:** 
  - Expense tracking module
  - Category management
  - Expense filtering & search

- **Week 4-5:** 
  - Dashboard with key metrics
  - Charts & visualizations (Chart.js)
  - Period comparisons
  - Basic forecasting

- **Week 5-6:** 
  - CSV export functionality
  - Email notifications (optional)
  - Testing & bug fixes
  - UI refinement & responsiveness
  - cPanel deployment & optimization
  - Documentation

---

## Important Notes for Migration to .NET Backend

- Keep all business logic in Service classes (easy to migrate to .NET later)
- Avoid PHP-specific functions; use OOP and design patterns
- Document all database queries and relationships clearly
- Use consistent API response structure (JSON) that can map to .NET controllers
- Keep models simple and focused (SRP - Single Responsibility Principle)
- Use prepared statements exclusively (already in place with PDO)
- Maintain clear separation between views and business logic
- Document authentication flow for easy implementation in .NET
- Keep all validation logic in reusable functions/classes

---

## Security Considerations

- **SQL Injection:** Use PDO prepared statements exclusively
- **XSS:** Sanitize all user input; use htmlspecialchars() in views
- **CSRF:** Implement token-based CSRF protection
- **Password Security:** Use password_hash() and password_verify()
- **Session Security:** Set secure session cookies over HTTPS
- **File Upload:** Validate file types, scan for malware, store outside web root
- **Authentication:** Implement password reset with token expiration
- **Data Backup:** Automated daily backups via cPanel
- **SSL/TLS:** Enable HTTPS for all connections

---

## Performance Optimization

- **Database:**
  - Create indexes on frequently queried columns (status, dates, categories)
  - Use database queries efficiently (avoid N+1 queries)
  - Archive old data quarterly

- **Frontend:**
  - Minify CSS & JavaScript
  - Compress images
  - Use CDN for third-party libraries (Bootstrap, Chart.js)
  - Lazy load images in product listings

- **Caching:**
  - Implement simple PHP-based query caching for reports
  - Cache static assets with proper HTTP headers
  - Use browser caching

- **Code:**
  - Profile slow queries with MySQL EXPLAIN
  - Use pagination for large data sets
  - Implement lazy loading for charts on dashboard

---

## Testing Strategy

- Unit tests for model methods & service classes (PHPUnit)
- Integration tests for database operations
- Manual testing for user workflows
- Cross-browser testing (Chrome, Firefox, Safari, Edge)
- Mobile responsiveness testing
- Load testing (simulate 5-6 concurrent users)
- Security testing (vulnerability scanning)

---

## Dependency Management (composer.json)

```json
{
  "require": {
    "php": ">=7.4",
    "phpoffice/phpspreadsheet": "^1.20",
    "phpmailer/phpmailer": "^6.5"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.5"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

---

## Backup & Disaster Recovery

- **Daily backups** via cPanel (automatic)
- **Database backups** - export monthly to external storage
- **Version control** - all code on Git repository
- **Documentation** - maintain setup and migration guides
- **Recovery plan** - document restore procedures

---

## Key Metrics to Track

- **Revenue:** Daily, weekly, monthly totals
- **Orders:** Pending count, average processing time, delivered count
- **Inventory:** Low stock items, overstock items, total SKUs
- **Expenses:** Monthly breakdown by category
- **Profit:** Gross profit, net profit margins by product
- **Performance:** Page load times, error rates

---

## Phase Implementation Order

**Phase 1 (Critical - Weeks 1-4):**
1. Authentication & user management
2. Product inventory (CRUD + variants)
3. Order management (CRUD + status)
4. Expense logging
5. Basic dashboard

**Phase 2 (Important - Weeks 4-5):**
6. Advanced analytics & charts
7. Period comparisons
8. CSV export

**Phase 3 (Polish - Week 6+):**
9. Email notifications
10. Forecasting refinement
11. Performance optimization
12. Security hardening

---

**Ready to start? Upload this brief to your Claude Code session or save it locally!**

```bash
# If using Claude Code with this file:
claude code < inventory-brief-php.md

# Or paste the content directly into Claude Code
```

This PHP + MySQL version is production-ready for cPanel hosting and provides a solid, secure foundation for your inventory management system! 🚀

---

## Recommended cPanel Hosting Requirements

- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher (MariaDB 10.3+)
- **Disk Space:** Minimum 5GB (for code, backups, uploads)
- **Monthly Traffic:** Unlimited or 100GB+ (light internal use)
- **SSL:** Support for Let's Encrypt (free)
- **Backups:** Automated daily backups
- **Email:** For password reset notifications

Popular cPanel hosting providers: Bluehost, HostGator, SiteGround, DreamHost, Kinsta (managed)
