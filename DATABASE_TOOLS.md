# Database Setup Tools

You have multiple tools available to set up the database for the Jersey Store Inventory Management System.

---

## 🛠️ Available Tools

### 1. **install.php** - Web-Based Installer (⭐ Recommended)

**What it does:**
- Guides you through database setup with a web interface
- Creates database if it doesn't exist
- Imports the entire schema automatically
- Verifies all tables and data
- Tests application connection
- Shows step-by-step progress with color-coded status

**How to use:**
```bash
# 1. Start PHP server
php -S localhost:8000 -t public/

# 2. Open in browser
http://localhost:8000/install.php

# 3. Follow on-screen instructions
# 4. Delete install.php when done (optional)
```

**Best for:**
- First-time setup
- Non-technical users
- Shared hosting/cPanel
- Quick verification of setup

---

### 2. **test-db.php** - Command-Line Tester

**What it does:**
- Tests MySQL connection with your credentials
- Verifies all 8 tables exist
- Counts records in each table
- Shows MySQL version and database info
- Provides clear pass/fail status

**How to use:**
```bash
# Run from command line
php test-db.php
```

**Output example:**
```
═══════════════════════════════════════════════════════════════
  Jersey Store Inventory Management - Database Test
═══════════════════════════════════════════════════════════════

[1/4] Checking PHP PDO Extension...
✓ PDO MySQL extension loaded

[2/4] Testing Database Connection...
✓ Connected to MySQL
  Host: localhost
  Database: inventory_mgmt
  User: root

[3/4] Checking Database Tables...
✓ Found 8 tables:
  • expenses
  • order_items
  • orders
  • password_reset_tokens
  • product_variants
  • products
  • stock_adjustments
  • users

[4/4] Testing Table Data & Queries...
  Users: 1
  Products: 0
  Variants: 0
  Orders: 0
  ...

✓ All Tests Passed!
```

**Best for:**
- Verifying database is properly set up
- Troubleshooting connection issues
- Checking after manual import
- Automated testing/CI pipelines

---

### 3. **001_initial_schema.sql** - Raw SQL Schema

**What it is:**
- Complete MySQL database schema file
- Contains all 8 table definitions
- Includes indexes, foreign keys, defaults
- Has default admin user

**How to use:**

#### Via MySQL CLI:
```bash
mysql -u root -p inventory_mgmt < migrations/001_initial_schema.sql
```

#### Via phpMyAdmin:
1. Create database: `inventory_mgmt`
2. Go to Import tab
3. Select file: `migrations/001_initial_schema.sql`
4. Click Import

#### Via cPanel:
1. Go to Databases → phpMyAdmin
2. Create database
3. Click Import → Choose file
4. Click Import

**Best for:**
- Manual database setup
- Server administration
- Backup and restore
- Understanding database structure

---

## 📋 Quick Reference

### Database Setup Flow

```
┌─────────────────────────────────────┐
│ Option A: install.php (Web Browser) │
│ ✓ Easiest for beginners             │
│ ✓ Visual feedback                   │
│ ✓ Auto-creates everything           │
└─────────────────────────────────────┘
           ↓
      Database Created
           ↓
┌─────────────────────────────────────┐
│ Option B: MySQL CLI Commands        │
│ ✓ Manual control                    │
│ ✓ Server-level access               │
│ ✓ Scriptable                        │
└─────────────────────────────────────┘
           ↓
      Database Created
           ↓
┌─────────────────────────────────────┐
│ Option C: phpMyAdmin Upload         │
│ ✓ Visual interface                  │
│ ✓ Works on shared hosting           │
│ ✓ No command line needed            │
└─────────────────────────────────────┘
           ↓
      Database Created
           ↓
     ┌─────────────────┐
     │ Run: test-db.php│
     │ Verify Setup    │
     └─────────────────┘
           ↓
        ✓ Success!
```

---

## 🔧 Setup Methods Comparison

| Feature | install.php | CLI Commands | phpMyAdmin | cPanel |
|---------|------------|--------------|-----------|--------|
| **Ease of Use** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Requires CLI** | No | Yes | No | No |
| **Visual Feedback** | Yes | Limited | Yes | Yes |
| **Auto-creates DB** | Yes | No* | No* | No* |
| **Error Messages** | Clear | Raw | Standard | Limited |
| **Best For** | First-time | Server admin | Shared host | Web host |
| **Testing** | Built-in | Separate step | Separate step | Separate step |

*These tools don't create database - you must do it first

---

## ✅ Verification Checklist

After using any setup method, verify with:

```bash
php test-db.php
```

You should see:
- ✓ PDO MySQL extension loaded
- ✓ Connected to MySQL
- ✓ Found 8 tables
- ✓ Admin user exists
- ✓ All Tests Passed!

---

## 📝 Required Database Credentials

Before running any tool, you need:

```
Host:     localhost (or your server)
Port:     3306 (default)
User:     root (or your username)
Password: (your password)
Database: inventory_mgmt (created automatically or manually)
```

Update these in:
- `config/database.php` - for application
- `install.php` - for web installer (if needed)
- `test-db.php` - for CLI tester (if needed)

---

## 🚨 Troubleshooting

### "Can't connect to MySQL server"
```bash
# Check MySQL is running
mysql --version

# Test connection manually
mysql -u root -p -h localhost
```

### "Database doesn't exist"
```bash
# Create it manually
mysql -u root -p
mysql> CREATE DATABASE inventory_mgmt;
mysql> EXIT;

# Then import schema
mysql -u root -p inventory_mgmt < migrations/001_initial_schema.sql
```

### "Permission denied"
```bash
# Check user permissions
mysql -u root -p
mysql> GRANT ALL PRIVILEGES ON inventory_mgmt.* TO 'root'@'localhost';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;
```

### "Table already exists"
- This is OK - the import script handles it
- Or use: `DROP DATABASE inventory_mgmt;` before reimporting

---

## 🎯 Recommended Setup Path

### For Development (Local Machine)

```bash
# 1. Ensure MySQL is running
# 2. Start PHP server
php -S localhost:8000 -t public/

# 3. Open installer in browser
# http://localhost:8000/install.php

# 4. Follow on-screen instructions

# 5. Verify with test script
php test-db.php

# 6. Login to application
# http://localhost:8000/auth/login
```

### For Production (Web Hosting)

```bash
# 1. Use cPanel to create database
# 2. Use phpMyAdmin to import schema
# 3. Update config/database.php with credentials
# 4. Run test-db.php via browser or SSH
# 5. Delete install.php and test-db.php
# 6. Access application via domain
```

### For Server Administration

```bash
# 1. SSH into server
# 2. Create database
mysql -u root -p -e "CREATE DATABASE inventory_mgmt;"

# 3. Import schema
mysql -u root -p inventory_mgmt < migrations/001_initial_schema.sql

# 4. Verify
mysql -u root -p inventory_mgmt -e "SHOW TABLES;"

# 5. Set permissions
mysql -u root -p -e "GRANT ALL ON inventory_mgmt.* TO 'appuser'@'localhost';"
```

---

## 📚 Related Documentation

- **Full Setup Guide:** `DATABASE_SETUP.md`
- **Quick Start:** `QUICK_START.md`
- **Project Overview:** `README.md`
- **Troubleshooting:** See `DATABASE_SETUP.md` Troubleshooting section

---

## 🔐 Security Notes

### Default Admin User
```
Email: admin@jerseystore.com
Password: admin123 (⚠️ Change immediately in production)
```

### To Change Admin Password

In MySQL:
```sql
-- Generate bcrypt hash first using PHP
-- In PHP terminal: echo password_hash('newpassword', PASSWORD_BCRYPT);

UPDATE users 
SET password_hash = '$2y$12$YOUR_BCRYPT_HASH'
WHERE email = 'admin@jerseystore.com';
```

### Secure Your Database

```bash
# Set proper file permissions
chmod -R 755 .
chmod -R 777 uploads/

# Move config outside web root (if possible)
# Enable SSL/HTTPS
# Use strong database password
# Regular backups
```

---

## 🤝 Support

If you encounter issues:

1. **Run the test script:**
   ```bash
   php test-db.php
   ```

2. **Check the output** for specific error messages

3. **Review DATABASE_SETUP.md** Troubleshooting section

4. **Verify credentials** in config/database.php

5. **Check MySQL logs** for server-side errors

---

**Choose the setup method that works best for your environment.** All three paths (install.php, CLI, phpMyAdmin) lead to the same result - a fully functional database! 🚀
