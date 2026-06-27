# Managing Migrations in cPanel Hosting

This guide explains how to run database migrations on shared hosting environments like cPanel.

## Overview

Migrations are SQL scripts that set up and update your database schema. In KIMS, migrations are located in the `migrations/` folder and are numbered (001, 002, etc.) to ensure they run in the correct order.

---

## Method 1: Using PHP Script (Recommended)

This is the easiest method for cPanel hosting.

### Step 1: Upload the migrate.php file

The `migrate.php` file is already in your project root. Upload it to your hosting account via FTP or cPanel File Manager.

```
/public_html/migrate.php
```

### Step 2: Run migrations via browser

1. Go to: `https://yourdomain.com/migrate.php`
2. The script will display migration results
3. You should see "All migrations completed!" at the end

### Step 3: Verify migrations completed

Check the output for:
- ✓ Green checkmarks = Successfully completed
- ⚠ Yellow warnings = Already applied (safe to ignore)
- ✗ Red errors = Issues that need fixing

---

## Method 2: Using cPanel Terminal/SSH

If you have SSH access in cPanel:

### Step 1: Connect via SSH

```bash
ssh username@yourdomain.com
```

### Step 2: Navigate to project directory

```bash
cd public_html/kims
```

### Step 3: Run migrations

```bash
php migrate.php
```

### Step 4: Check results

The output will show migration status similar to the browser method.

---

## Method 3: Using cPanel MySQL Tools (Manual)

For more control, run migrations directly:

### Step 1: Access MySQL in cPanel

1. Log in to cPanel
2. Click **phpMyAdmin**
3. Select your database

### Step 2: Import migrations one at a time

For each migration file (in order):

1. Click **Import** tab
2. Click **Choose File**
3. Select migration file (e.g., `001_initial_schema.sql`)
4. Click **Import**
5. Check for errors

**Order to import:**
```
001_initial_schema.sql
002_feature_updates.sql
002_order_delivery_timestamps.sql
002_order_items_extras.sql
003_sourcing_price.sql
003_order_soft_delete.sql
004_remove_color_reorder_default.sql
004_exchange_orders.sql
005_order_stock_issue.sql
006_delivery_status_package_ready.sql
007_drop_status_column.sql
008_waiting_for_print_status.sql
```

---

## Method 4: Using cPanel Terminal (Command Line)

### Step 1: Access cPanel Terminal

1. Log in to cPanel
2. Click **Terminal** (under Advanced section)

### Step 2: Navigate and run migrations

```bash
cd public_html/kims
php migrate.php
```

---

## What to Do Before Running Migrations

### 1. Backup your database

**Via cPanel:**
1. Go to **phpMyAdmin**
2. Select your database
3. Click **Export**
4. Save the SQL file

**Via SSH:**
```bash
mysqldump -u username -p database_name > backup.sql
```

### 2. Test locally first (if possible)

Run migrations locally before deploying:
```bash
php migrate.php
```

### 3. Check database credentials

Ensure `config/.env` has correct settings:
```
DB_HOST=localhost
DB_USER=cpanelusername_dbuser
DB_PASSWORD=your_password
DB_NAME=cpanelusername_database
```

---

## Troubleshooting Common Issues

### Issue: "Access Denied" or Connection Error

**Solution:**
- Verify database credentials in `config/.env`
- Check that database user has all privileges
- In cPanel > MySQL Databases, check user permissions

### Issue: "Unknown column" error

**Solution:**
- Check if previous migrations were already run
- Run `php migrate.php` to run all migrations in order

### Issue: "Table already exists"

**Solution:**
- This is normal if `001_initial_schema.sql` was already imported
- The `CREATE TABLE IF NOT EXISTS` prevents errors
- Safe to continue

### Issue: Can't access migrate.php via browser

**Solution:**
1. Verify file is uploaded to `public_html/`
2. Check file permissions (should be 644)
3. Try accessing directly: `https://yourdomain.com/migrate.php`
4. Check if URL rewriting is enabled (may need to disable in `.htaccess` temporarily)

### Issue: "503 Service Unavailable"

**Solution:**
- Memory limit issue
- Try running via SSH instead of browser
- Or run migrations individually instead of all at once

---

## Recommended: Create a Run-Once Script

For added safety, create a script that only runs migrations once:

**File: `init-db.php`**
```php
<?php
// Check if migrations already ran
$flagFile = 'migrations/.init-complete';

if (file_exists($flagFile)) {
    echo "Migrations already completed. Database is initialized.\n";
    exit(0);
}

// Run migrations
require 'migrate.php';

// Create flag file
if (!file_exists('migrations')) mkdir('migrations', 0755, true);
file_put_contents($flagFile, date('Y-m-d H:i:s'));

echo "✓ Database initialization complete!\n";
```

Access: `https://yourdomain.com/init-db.php`

---

## After Running Migrations

### 1. Test your application

Navigate to your site and verify:
- ✓ Database connection works
- ✓ Users can log in
- ✓ Creating orders works
- ✓ Reports load correctly

### 2. Delete migration files (optional security)

After running migrations successfully, you can delete migration files to prevent accidental re-imports:

```bash
rm migrations/*.sql
```

**Note:** Keep backups first!

### 3. Delete helper scripts (optional)

Delete `migrate.php` and `init-db.php` after migrations complete:

```bash
rm migrate.php init-db.php check-table.php
```

---

## Database Schema Checklist

After migrations, verify these tables exist in phpMyAdmin:

```
✓ users
✓ products
✓ product_variants
✓ orders
✓ order_items
✓ expenses
✓ stock_adjustments
✓ password_reset_tokens
```

Verify `order_items` table has these columns:
- id
- order_id
- product_id
- variant_id
- quantity
- unit_price
- line_total
- patches_extra ← New column
- namekit_extra ← New column
- kit_name ← New column
- kit_number ← New column
- is_return
- stock_deducted
- created_at

---

## Quick Reference

| Method | Ease | Security | Recommended |
|--------|------|----------|-------------|
| Browser (migrate.php) | ⭐⭐⭐⭐⭐ | Medium | ✓ Yes |
| SSH Command | ⭐⭐⭐⭐ | High | ✓ For advanced users |
| phpMyAdmin Import | ⭐⭐⭐ | Medium | ✓ Manual control |
| cPanel Terminal | ⭐⭐⭐ | High | ✓ If SSH unavailable |

---

## Summary

**For most cPanel users:**

1. Upload `migrate.php` to your domain's public_html
2. Visit `https://yourdomain.com/migrate.php` in your browser
3. Wait for completion
4. Delete `migrate.php` after successful migration
5. Test your application

That's it! Your database is now set up.
