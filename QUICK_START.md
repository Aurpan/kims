# Quick Start Guide

Get the Jersey Store Inventory Management System up and running in minutes.

## 1️⃣ Import Database

### Option A: Using PHP (Web Browser)

```bash
# Start PHP server
php -S localhost:8000 -t public/

# Then open in browser:
# http://localhost:8000/install.php

# Follow the on-screen prompts to:
# ✓ Create database
# ✓ Import schema
# ✓ Verify setup
```

### Option B: Using MySQL Command Line

```bash
# Connect to MySQL
mysql -u root -p

# Run in MySQL prompt
mysql> CREATE DATABASE inventory_mgmt CHARACTER SET utf8mb4;
mysql> EXIT;

# Import schema
mysql -u root -p inventory_mgmt < migrations/001_initial_schema.sql
```

### Option C: Using phpMyAdmin

1. Create database: `inventory_mgmt`
2. Import file: `migrations/001_initial_schema.sql`
3. Done!

---

## 2️⃣ Configure Database

Edit `config/database.php`:

```php
$db_config = [
    'host' => 'localhost',        // Your MySQL host
    'user' => 'root',             // Your username
    'password' => '',             // Your password
    'database' => 'inventory_mgmt',
    'port' => 3306
];
```

---

## 3️⃣ Test Connection

```bash
# Run test script
php test-db.php

# You should see:
# ✓ PDO MySQL extension loaded
# ✓ Connected to MySQL
# ✓ Found 8 tables
# ✓ All Tests Passed!
```

---

## 4️⃣ Start Development Server

```bash
php -S localhost:8000 -t public/
```

---

## 5️⃣ Access Application

**Open in Browser:**
```
http://localhost:8000/auth/login
```

**Login Credentials:**
- Email: `admin@jerseystore.com`
- Password: `admin123`

---

## ✅ You're Done!

The application is now ready. The dashboard shows:
- ✓ Total revenue
- ✓ Pending orders
- ✓ Low stock items
- ✓ Product count
- ✓ Recent orders
- ✓ Top selling products

---

## 📁 Project Files Overview

```
KIMS/
├── public/index.php           ← Entry point
├── config/database.php        ← DB credentials
├── config/config.php          ← App settings
├── src/
│   ├── Core/                  ← Database, Router, Auth
│   ├── Models/                ← Data models
│   ├── Controllers/           ← Business logic
│   └── Views/                 ← HTML templates
├── migrations/
│   └── 001_initial_schema.sql ← Database schema
├── install.php                ← Database installer
├── test-db.php                ← Connection tester
└── .htaccess                  ← URL rewriting
```

---

## 🔑 Default Features

After login, you can:
- ✓ Add/manage products and variants
- ✓ Create and track orders
- ✓ Log expenses by category
- ✓ View dashboard analytics
- ✓ Generate reports
- ✓ Export data to CSV

---

## ⚙️ Configuration Tips

### Change Admin Password

In MySQL:
```sql
UPDATE users 
SET password_hash = '$2y$12$YOUR_BCRYPT_HASH' 
WHERE email = 'admin@jerseystore.com';
```

### Enable Debug Mode

Edit `config/config.php`:
```php
define('APP_DEBUG', true);  // For development
define('APP_DEBUG', false); // For production
```

### Configure Email

Edit `config/config.php` for password reset emails:
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
```

---

## 🔒 Security Checklist

Before production:
- [ ] Change admin password
- [ ] Update database credentials
- [ ] Set APP_DEBUG to false
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions (755/777)
- [ ] Configure automated backups
- [ ] Review security settings
- [ ] Hide admin panels/files

---

## 📊 Database Schema

### 8 Tables Created:
- users (with default admin)
- products
- product_variants
- orders
- order_items
- expenses
- stock_adjustments
- password_reset_tokens

**Total Tables:** 8  
**Total Indexes:** 15+  
**Default Records:** 1 (admin user)

---

## 🆘 Troubleshooting

### Application won't load
1. Check PHP is installed: `php -v`
2. Check MySQL is running
3. Run: `php test-db.php`
4. Verify credentials in `config/database.php`

### Login fails
1. Database connection working? Run `test-db.php`
2. Admin user exists? Check MySQL: `SELECT * FROM users;`
3. Password correct? Default is `admin123`

### Database not found
```bash
mysql -u root -p
mysql> SHOW DATABASES;
mysql> CREATE DATABASE inventory_mgmt;
```

### Permission denied errors
```bash
chmod -R 755 .
chmod -R 777 uploads/
```

---

## 📚 Learn More

- **Full Setup Guide:** `DATABASE_SETUP.md`
- **Project Overview:** `README.md`
- **Project Specification:** `inventory-brief-php.md`
- **Implementation Status:** `SETUP_COMPLETE.md`

---

## 🚀 Next Steps

### Phase 1: Core Features
1. Implement product management
2. Build order management
3. Add expense tracking
4. Create basic dashboard

### Phase 2: Advanced Features
5. Build analytics & reports
6. Add chart visualizations
7. Implement forecasting
8. Add export functionality

### Phase 3: Polish
9. Email notifications
10. Performance optimization
11. Security hardening
12. Deployment preparation

---

## 💡 Tips

- Use `test-db.php` to verify database anytime
- Use `install.php` to reinstall database
- Check `config/config.php` for all settings
- Models have ready-to-use query methods
- Controllers have validation built-in
- Views use Bootstrap 5 for responsive design

---

**Happy coding! 🎉**

For issues, check the troubleshooting section or review the full DATABASE_SETUP.md guide.
