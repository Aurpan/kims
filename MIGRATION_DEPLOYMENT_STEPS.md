# Running Migrations on cPanel Hosting

Quick step-by-step guide to run KIMS database migrations after deployment.

---

## Prerequisites

Before running migrations, ensure:

- ✅ Database created in cPanel
- ✅ Database user created with ALL privileges
- ✅ `config/.env` file created with correct credentials
- ✅ All PHP files uploaded via FTP
- ✅ `migrate.php` file uploaded to root directory
- ✅ `migrations/` folder with SQL files uploaded

---

## Method 1: Via SSH (Recommended - Fastest)

### Step 1: SSH Login

```bash
ssh your_cpanel_username@your-domain.com
```

You'll be prompted for your cPanel password.

### Step 2: Navigate to Project Directory

```bash
cd public_html/kims
```

Or wherever you uploaded KIMS:

```bash
cd path/to/your/kims
```

### Step 3: Run Migrations

```bash
php migrate.php
```

### Step 4: Verify Success

You should see:

```
================================
Running Migrations
================================

[Batch 1]
✓ 001_initial_schema.sql - Success
✓ 002_all_updates.sql - Success

================================
Total: 2 migrations completed
Database is ready!
================================
```

### Step 5: Check Migration Status

```bash
php check-migrations.php
```

**Expected output:**

```
================================
Migrations Tracking System
================================

Migrations table EXISTS ✓

Total Migrations: 2

[Batch 1] - 2026-06-28 XX:XX:XX
✓ 001_initial_schema.sql
✓ 002_all_updates.sql

All migrations completed successfully!
```

---

## Method 2: Via cPanel Terminal (If SSH Not Available)

If SSH is not available on your hosting:

### Step 1: Open Terminal in cPanel

1. Log in to **cPanel**
2. Search for **Terminal** or **SSH & Shell Access**
3. Click **Terminal**

### Step 2: Run Migrations

```bash
php migrate.php
```

### Step 3: Verify

```bash
php check-migrations.php
```

---

## Method 3: Via Web Browser (Last Resort)

If neither SSH nor Terminal is available:

### Step 1: Create Migration Script

Create `public/run-migrations.php`:

```php
<?php
// TEMPORARY: Delete after use!

if ($_POST['action'] ?? null === 'migrate') {
    require '../migrate.php';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Run Migrations</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; }
        .warning { color: red; background: #fee; padding: 10px; border-radius: 5px; }
        button { padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Database Migrations</h1>
    
    <div class="warning">
        <strong>⚠️ WARNING:</strong> Delete this file immediately after migrations complete!
    </div>
    
    <p>Click the button below to run all pending migrations:</p>
    
    <form method="POST">
        <input type="hidden" name="action" value="migrate">
        <button type="submit">▶ Run Migrations Now</button>
    </form>
    
    <p><small>This will create tables and add columns to your database.</small></p>
</body>
</html>
```

### Step 2: Upload File

Upload `public/run-migrations.php` via FTP to `public/` directory.

### Step 3: Run via Browser

1. Visit: `https://your-domain.com/public/run-migrations.php`
2. Click **Run Migrations Now** button
3. Wait for completion message

### Step 4: Delete File

**IMMEDIATELY delete `public/run-migrations.php`** after migrations complete:

- Via FTP
- Via cPanel File Manager
- Via command line: `rm public/run-migrations.php`

---

## Verify Migrations Completed

### Method A: SSH/Terminal

```bash
php check-migrations.php
```

### Method B: Web Browser

Create `public/check-migrations.php` temporarily:

```php
<?php
require '../check-migrations.php';
?>
```

Visit: `https://your-domain.com/public/check-migrations.php`

Delete after use!

### Method C: phpMyAdmin (cPanel)

1. Log in to **cPanel**
2. Go to **phpMyAdmin**
3. Select your database
4. Click **Tables** tab
5. Should see these tables:
   - `users`
   - `products`
   - `product_variants`
   - `orders`
   - `order_items`
   - `expenses`
   - `stock_adjustments`
   - `password_reset_tokens`
   - `migrations` ← Migration tracker table

---

## Check Database Contents After Migration

### Via phpMyAdmin

1. Log in to cPanel
2. Open **phpMyAdmin**
3. Select database
4. Click **SQL** tab
5. Run test query:

```sql
SELECT COUNT(*) as total_users FROM users;
```

Should show at least 1 user (admin@jerseystore.com)

### Via SSH

```bash
mysql -u your_db_user -p your_database_name -e "SELECT email FROM users;"
```

Should show:
```
admin@jerseystore.com
```

---

## Test Login After Migration

### Step 1: Access Application

Visit: `https://your-domain.com/kims/public/`

Or if installed in root:

Visit: `https://your-domain.com/`

### Step 2: Login with Default Credentials

- **Email:** `admin@jerseystore.com`
- **Password:** `admin123`

### Step 3: Change Password Immediately

After successful login:

1. Click on your profile/settings
2. Change password to something secure
3. Log out and log back in with new password

---

## Troubleshooting

### Problem: "Command not found: php"

**Cause:** PHP not in PATH

**Solution:**

```bash
/usr/bin/php migrate.php
```

Or:

```bash
which php
# Then use full path
/usr/local/bin/php migrate.php
```

### Problem: "Cannot connect to database"

**Cause:** Wrong credentials in `.env`

**Solution:**

1. Check `config/.env` file:
   ```bash
   cat config/.env
   ```

2. Verify values match cPanel database info

3. Update if needed:
   ```bash
   nano config/.env
   ```

4. Press `Ctrl+X`, then `Y`, then `Enter` to save

5. Run migrations again:
   ```bash
   php migrate.php
   ```

### Problem: "Permission denied"

**Cause:** File permissions too restrictive

**Solution:**

```bash
chmod 755 migrate.php
chmod 755 public/
chmod 755 config/
php migrate.php
```

### Problem: "Table already exists"

**Cause:** Migrations already ran, running again

**Solution:** This is safe! Migrations use `IF NOT EXISTS`, so running twice is fine. Check status:

```bash
php check-migrations.php
```

If all show ✓ completed, you're good!

### Problem: "Timeout" error

**Cause:** Migrations taking too long

**Solution:**

1. Increase PHP timeout in cPanel:
   - Go to **Select PHP Version**
   - Find `max_execution_time`
   - Set to 300 or higher
   - Save

2. Try running migrations again

### Problem: "mysql: command not found"

**Cause:** MySQL CLI not available

**Solution:** Use phpMyAdmin in cPanel instead, or ask hosting provider to enable MySQL CLI.

---

## Migration Files Reference

Two main migration files will run:

### 1. `001_initial_schema.sql`

Creates initial database structure:

```
Tables created:
✓ users
✓ products
✓ product_variants
✓ orders
✓ order_items
✓ expenses
✓ stock_adjustments
✓ password_reset_tokens
✓ migrations (tracking)
```

### 2. `002_all_updates.sql`

Adds features and columns:

```
Changes made:
✓ Add payment_method, payment_status to orders
✓ Add delivery_status with multiple values
✓ Add patches_extra, kit_name, kit_number to order_items
✓ Add is_deleted (soft delete support)
✓ Add exchange order support
✓ Add sourcing_price to products
✓ Add stock_deducted tracking
✓ Update delivery_status ENUM values
```

---

## Complete Workflow

```
1. Database created in cPanel ✓
2. Database user created ✓
3. Files uploaded via FTP ✓
4. config/.env created ✓
5. SSH/Terminal open ✓
6. Navigate to project: cd public_html/kims ✓
7. Run migrations: php migrate.php ✓
8. Verify: php check-migrations.php ✓
9. Test login: Visit https://your-domain.com ✓
10. Change admin password ✓
11. Delete temporary files (if created) ✓
12. Deployment complete! ✓
```

---

## Quick Commands Cheat Sheet

```bash
# Navigate to project
cd public_html/kims

# Run migrations
php migrate.php

# Check migration status
php check-migrations.php

# Check migrations table exists
php check-migrations-table.php

# View database list
mysql -u your_user -p -e "SHOW DATABASES;"

# View tables in database
mysql -u your_user -p your_database -e "SHOW TABLES;"

# View users in database
mysql -u your_user -p your_database -e "SELECT email FROM users;"

# View migration records
mysql -u your_user -p your_database -e "SELECT * FROM migrations;"

# Exit MySQL/SSH
exit
```

---

## Performance Notes

- **Migration time:** 5-30 seconds typically
- **If taking >1 min:** Check hosting provider's database server load
- **If timeouts:** Increase PHP `max_execution_time` to 300+
- **Multiple runs:** Safe to run multiple times (idempotent)

---

## Security Reminders

⚠️ **Important:**

- [ ] Delete any temporary migration runner files after use
- [ ] Don't share cPanel credentials with anyone
- [ ] Change default admin password immediately
- [ ] Keep `.env` file out of version control
- [ ] Enable HTTPS/SSL on your domain
- [ ] Regular backups in cPanel

---

## Getting Help

If migrations fail:

1. **Check the error message carefully** - it tells you what went wrong
2. **Run `php check-migrations.php`** - see what completed
3. **Check database exists** - via cPanel → MySQL Databases
4. **Verify credentials in `.env`** - must match cPanel database info
5. **Check file permissions** - should be 755 for directories, 644 for files
6. **Ask your hosting provider** if:
   - mod_rewrite not enabled
   - PHP version too old
   - MySQL not available
   - Extensions missing

---

**Date Deployed:** _____________  
**Migrations Completed:** ✓ Yes / ❌ No  
**Admin Password Changed:** ✓ Yes / ❌ No  
**Temporary Files Deleted:** ✓ Yes / ❌ No

