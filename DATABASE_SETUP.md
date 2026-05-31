# Database Setup Guide

This guide covers how to set up the MySQL database for the Jersey Store Inventory Management System.

## Prerequisites

- MySQL 5.7+ or MariaDB 10.3+
- PHP 7.4+ with PDO MySQL extension
- Access to MySQL command line, phpMyAdmin, or cPanel

---

## Option 1: Using PHP Installation Script (Recommended)

### Step 1: Update Database Credentials
Edit `config/database.php` and update with your MySQL credentials:

```php
$db_config = [
    'host' => 'localhost',      // Your MySQL host
    'user' => 'root',           // Your MySQL username
    'password' => '',           // Your MySQL password
    'database' => 'inventory_mgmt',
    'port' => 3306
];
```

### Step 2: Run Installation via Web Browser

1. Start PHP development server:
   ```bash
   php -S localhost:8000 -t public/
   ```

2. Open browser and visit:
   ```
   http://localhost:8000/install.php
   ```

3. Follow the on-screen steps:
   - ✓ Database connection test
   - ✓ Database creation
   - ✓ Schema import
   - ✓ Table verification
   - ✓ Admin user verification
   - ✓ Application connection test

4. After successful installation, delete `install.php`:
   ```bash
   rm install.php
   ```

---

## Option 2: Using MySQL Command Line

### Step 1: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE inventory_mgmt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 2: Import Schema

```bash
mysql -u root -p inventory_mgmt < migrations/001_initial_schema.sql
```

### Step 3: Verify Installation

```bash
mysql -u root -p inventory_mgmt

SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'inventory_mgmt';
SELECT * FROM users WHERE email = 'admin@jerseystore.com';
EXIT;
```

---

## Option 3: Using phpMyAdmin

### Step 1: Create Database
1. Login to phpMyAdmin
2. Click "New" in sidebar
3. Enter database name: `inventory_mgmt`
4. Select Charset: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 2: Import Schema
1. Select the newly created `inventory_mgmt` database
2. Click "Import" tab
3. Click "Choose File"
4. Select `migrations/001_initial_schema.sql`
5. Click "Import"

### Step 3: Verify Tables
1. Expand `inventory_mgmt` database in sidebar
2. Verify these tables exist:
   - users
   - products
   - product_variants
   - orders
   - order_items
   - expenses
   - stock_adjustments
   - password_reset_tokens

---

## Option 4: Using cPanel (Web Hosting)

### Step 1: Create Database
1. Login to cPanel
2. Go to "Databases" → "MySQL Databases"
3. Create new database: `yourusername_inventory`
4. Create new user with password
5. Add user to database with all privileges

### Step 2: Import Schema
1. Go to "Databases" → "phpMyAdmin"
2. Select your new database
3. Click "Import" tab
4. Upload `migrations/001_initial_schema.sql`
5. Click "Import"

### Step 3: Update Configuration
Edit `config/database.php`:

```php
$db_config = [
    'host' => 'localhost',
    'user' => 'yourusername_invuser',      // cPanel username + underscore + custom name
    'password' => 'your_password',         // Password you set
    'database' => 'yourusername_inventory', // cPanel username + underscore + database name
    'port' => 3306
];
```

---

## Testing Database Setup

### Option A: Using PHP Test Script

```bash
php test-db.php
```

This will:
- ✓ Check PDO MySQL extension
- ✓ Test database connection
- ✓ Verify all tables exist
- ✓ Count records in each table
- ✓ Display database information

### Option B: Using Web Browser

1. Start development server:
   ```bash
   php -S localhost:8000 -t public/
   ```

2. Access the application:
   ```
   http://localhost:8000/auth/login
   ```

3. Try logging in with:
   - **Email:** `admin@jerseystore.com`
   - **Password:** `admin123`

### Option C: Direct MySQL Query

```bash
mysql -u root -p inventory_mgmt

-- Check table count
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'inventory_mgmt';

-- Check admin user
SELECT id, email, name, is_active FROM users;

-- Check for any errors
SHOW TABLE STATUS\G
```

---

## Database Schema Overview

### 8 Core Tables

| Table | Purpose | Records |
|-------|---------|---------|
| **users** | System users and authentication | 1 (admin) |
| **products** | Jersey products | 0 (to be added) |
| **product_variants** | Product sizes, colors, SKUs | 0 (to be added) |
| **orders** | Customer orders | 0 (to be added) |
| **order_items** | Line items in orders | 0 (to be added) |
| **expenses** | Expense tracking | 0 (to be added) |
| **stock_adjustments** | Stock change audit trail | 0 (to be added) |
| **password_reset_tokens** | Password reset tokens | 0 (auto) |

### Key Features
- ✓ Proper indexing for fast queries
- ✓ Foreign key relationships
- ✓ Timestamp tracking (created_at, updated_at)
- ✓ Audit trails for stock adjustments
- ✓ Enum types for status fields
- ✓ Default admin user included

---

## Default Credentials

After importing the database schema, a default admin user is created:

```
Email:    admin@jerseystore.com
Password: admin123
Active:   Yes
```

⚠️ **IMPORTANT:** Change this password immediately in production!

To change password in MySQL:

```bash
mysql -u root -p inventory_mgmt

UPDATE users 
SET password_hash = '$2y$12$YOUR_BCRYPT_HASH' 
WHERE email = 'admin@jerseystore.com';
```

To generate a bcrypt hash in PHP:

```php
<?php
echo password_hash('your_new_password', PASSWORD_BCRYPT, ['cost' => 12]);
?>
```

---

## Troubleshooting

### Error: "Can't connect to MySQL server"

**Solution:**
1. Verify MySQL is running
2. Check host/port/credentials
3. Ensure database user has proper permissions

### Error: "Access denied for user 'root'@'localhost'"

**Solution:**
```bash
# Try with password prompt
mysql -u root -p

# Or use empty password if set
mysql -u root
```

### Error: "Unknown database 'inventory_mgmt'"

**Solution:**
1. Database not created yet
2. Run: `CREATE DATABASE inventory_mgmt;`
3. Then import the schema

### Error: "Syntax error in SQL statement"

**Solution:**
1. Ensure schema file is UTF-8 encoded
2. Verify MySQL version (5.7+)
3. Try importing via phpMyAdmin instead

### Error: "PDO connection failed" in application

**Solution:**
1. Update credentials in `config/database.php`
2. Verify database exists and tables are created
3. Check user permissions in MySQL
4. Test with: `php test-db.php`

---

## Post-Installation Checklist

- [ ] Database created: `inventory_mgmt`
- [ ] All 8 tables created
- [ ] Default admin user exists
- [ ] `config/database.php` updated with correct credentials
- [ ] Application can connect to database
- [ ] Login works with `admin@jerseystore.com`
- [ ] Admin password changed (production only)
- [ ] `install.php` deleted (if used)
- [ ] `test-db.php` deleted (if used)

---

## Next Steps

After database setup is complete:

1. **Update Admin Password** (Production):
   ```php
   // In PHP
   password_hash('your_new_password', PASSWORD_BCRYPT, ['cost' => 12])
   ```

2. **Add Sample Data** (Optional):
   - Create sample products and variants
   - Create sample orders
   - Log sample expenses
   - Test reports and analytics

3. **Configure Backups** (Important):
   - Set up daily MySQL backups
   - Use cPanel Backup Wizard if available
   - Export database monthly

4. **Enable HTTPS** (Production):
   - Install SSL certificate
   - Use cPanel AutoSSL or Let's Encrypt
   - Update `config/config.php` to use HTTPS

5. **Set File Permissions**:
   ```bash
   chmod -R 755 .
   chmod -R 777 uploads/
   ```

---

## SQL Commands Reference

### View Database Structure
```sql
-- List all tables
SHOW TABLES;

-- Show table structure
DESCRIBE users;
DESCRIBE products;

-- List indexes
SHOW INDEX FROM products;

-- Show table creation SQL
SHOW CREATE TABLE users\G
```

### Backup and Restore
```bash
# Backup database
mysqldump -u root -p inventory_mgmt > backup.sql

# Restore from backup
mysql -u root -p inventory_mgmt < backup.sql
```

### Check Database Size
```sql
SELECT 
  table_schema,
  ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.tables
GROUP BY table_schema;
```

---

## Support

For issues with database setup:
1. Check the troubleshooting section above
2. Verify all prerequisites are met
3. Review `test-db.php` output for specific errors
4. Check MySQL error logs
5. Consult MySQL documentation for your version

---

**Database setup is complete! Ready to start developing.** 🚀
