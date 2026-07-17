# Jersey Store Inventory Management System

A comprehensive PHP + MySQL web application for managing jersey store inventory, orders, and expenses with advanced analytics and reporting.

## Project Overview

Built for internal use by small teams (5-6 users), this system provides:
- **Product Inventory Management** - Track jerseys with variants (size, color, SKU)
- **Order Management** - Create and track orders through their lifecycle
- **Expense Tracking** - Categorized expense logging (COGS, Operational, Shipping, Marketing, Other)
- **Advanced Analytics** - Revenue trends, top-selling products, expense breakdowns, forecasting
- **Responsive Design** - Works on desktop, tablet, and mobile devices

## Technology Stack

- **PHP 7.4+** - Backend language
- **MySQL 5.7+** - Database
- **Bootstrap 5** - Responsive CSS framework
- **Chart.js** - Data visualization
- **Apache** - Web server (requires mod_rewrite)

## Directory Structure

```
inventory-management/
├── public/                    # Web root (publicly accessible)
│   ├── index.php             # Main router/entry point
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── images/               # Product images
├── src/                       # Application code
│   ├── Core/                 # Core classes
│   │   ├── Database.php      # PDO wrapper
│   │   ├── Router.php        # URL router
│   │   └── Auth.php          # Authentication
│   ├── Models/               # Database models
│   │   ├── Model.php         # Base model class
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Expense.php
│   │   └── StockAdjustment.php
│   ├── Controllers/          # Business logic
│   │   ├── Controller.php    # Base controller
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── ExpenseController.php
│   │   └── ReportController.php
│   ├── Services/             # Business logic services
│   └── Views/                # HTML templates
│       ├── layouts/
│       ├── auth/
│       ├── products/
│       ├── orders/
│       ├── expenses/
│       ├── reports/
│       └── dashboard/
├── config/                    # Configuration files
│   ├── config.php            # App constants
│   ├── database.php          # DB config
│   └── .env.example          # Environment example
├── migrations/                # Database schema
│   └── 001_initial_schema.sql
├── uploads/                   # User uploads (images, receipts)
│   ├── products/
│   └── receipts/
├── .htaccess                 # URL rewriting rules
├── composer.json             # Dependencies
├── .gitignore
└── README.md
```

## Setup Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher (MariaDB 10.3+)
- Apache with mod_rewrite enabled
- Composer (optional, for dependency management)

### Local Development Setup

1. **Clone/Download the project**
   ```bash
   git clone <repo_url>
   cd inventory-management
   ```

2. **Install dependencies** (optional)
   ```bash
   composer install
   ```

3. **Configure database**
   - Copy `config/.env.example` to `config/.env`
   - Update database credentials in `config/.env` and `config/database.php`
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=inventory_mgmt
   ```

4. **Create MySQL database and import schema**
   ```bash
   mysql -u root -p < migrations/001_initial_schema.sql
   ```

   Or via phpMyAdmin:
   - Create database: `inventory_mgmt`
   - Import file: `migrations/001_initial_schema.sql`

5. **Set directory permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 uploads/
   ```

6. **Start local PHP server**
   ```bash
   php -S localhost:8000 -t public/
   ```

7. **Access the application**
   - Open browser: `http://localhost:8000`
   - Default credentials:
     - Email: `admin@jerseystore.com`
     - Password: `admin123`

## cPanel Deployment

See [`deploymentSteps.md`](deploymentSteps.md) for the full, tested procedure
(this project is live at `https://kimsbd.online`).

- **First-time setup / a new environment**: [`deployment/DEPLOYMENT.md`](deployment/DEPLOYMENT.md)
  (manual File Manager zip upload — DB creation, migrations, permissions,
  HTTPS, lockdown checks).
- **Routine code updates** (the normal case once deployed): cPanel Git™
  Version Control — see the "Quick reference — routine code deploy" section
  at the top of [`deploymentSteps.md`](deploymentSteps.md). In short: push to
  GitHub, then in cPanel Git™ Version Control click **Update from Remote**
  → **Deploy HEAD Commit**.

## Database Schema

### Core Tables
- **users** - Application users
- **products** - Jersey products
- **product_variants** - Product sizes/colors/SKUs
- **orders** - Customer orders
- **order_items** - Line items in orders
- **expenses** - Expense tracking with categories
- **stock_adjustments** - Stock change audit trail
- **password_reset_tokens** - Password reset tokens

See `migrations/001_initial_schema.sql` for full schema with indexes.

## Features

### Authentication
- Login/Register with email and password
- Password hashing with bcrypt
- Session management with timeout
- CSRF token protection

### Product Management
- Create/Edit/Delete products
- Manage product variants (size, color, SKU)
- Track individual variant stock levels
- Reorder point alerts
- Category-based filtering
- Product search

### Order Management
- Create orders with multiple line items
- Order status workflow (Pending → Processing → Shipped → In Transit → Delivered → Returned)
- Search and filter orders
- Order history and details
- Tracking number management

### Expense Tracking
- Log expenses with 5 categories
- Monthly/quarterly summaries
- Category breakdown analysis
- Expense filtering by date range

### Reporting & Analytics
- Revenue trends (daily/weekly/monthly)
- Top-selling products
- Inventory status (low stock, overstock)
- Expense breakdown by category
- Period-over-period comparisons
- 30-day sales forecasting
- CSV export functionality

## Security Features

- **SQL Injection Prevention** - PDO prepared statements
- **XSS Protection** - Input sanitization and output escaping
- **CSRF Protection** - Token-based validation
- **Password Security** - bcrypt hashing with cost factor 12
- **Session Security** - Secure cookies and timeouts
- **File Upload Validation** - Type checking and size limits
- **HTTPS Support** - Ready for SSL/TLS

## Performance

- Database indexing on frequently queried columns
- Pagination for large datasets
- Query optimization (avoiding N+1 queries)
- Static asset caching
- CDN-based third-party libraries

## Development Standards

- **Architecture** - MVC pattern for clean separation
- **Code Style** - PSR-4 autoloading with namespaces
- **Database** - OOP models with reusable base class
- **Security** - Prepared statements, input validation
- **Extensibility** - Service layer for business logic
- **Maintainability** - Clear naming and documentation

## Future Enhancements

- Role-based access control (admin, manager, staff)
- Automated email notifications
- Advanced forecasting algorithms
- Multi-warehouse support
- Barcode scanning
- PDF invoice generation
- Bulk import functionality (CSV)
- Two-factor authentication
- API for mobile app
- Shipping provider integration

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `config/database.php`
- Ensure database `inventory_mgmt` exists

### 404 Not Found
- Verify `.htaccess` is in project root
- Check Apache has mod_rewrite enabled
- Ensure URL is correct

### Session Issues
- Check `uploads/` directory has 777 permissions
- Verify PHP session path is writable
- Clear browser cookies

### File Upload Issues
- Check `uploads/` directory exists and has 777 permissions
- Verify file size doesn't exceed `MAX_UPLOAD_SIZE`
- Ensure file type is in `ALLOWED_UPLOAD_TYPES`

## Support & Documentation

- See `inventory-brief-php.md` for complete project specification
- Check individual controller files for TODO comments indicating incomplete features
- Review model classes for available query methods
