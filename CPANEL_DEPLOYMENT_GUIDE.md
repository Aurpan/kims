# KIMS cPanel FTP Deployment Guide

Complete guide for deploying KIMS to cPanel hosting via FTP.

---

## Overview

**Deployment Method:** FTP  
**Target Environment:** cPanel Hosting (Shared Hosting)  
**Database:** MySQL (via cPanel)  
**PHP Version:** 7.4+ (configure in cPanel)

---

## Pre-Deployment Checklist

- [ ] Git repository cloned locally
- [ ] All changes committed to `main` branch
- [ ] FTP credentials obtained from cPanel
- [ ] Database created in cPanel
- [ ] Database user created with full permissions
- [ ] `.env` file prepared locally
- [ ] `public/` directory identified as document root

---

## Step 1: Prepare Files for FTP

### 1a. Create FTP-Ready Directory Structure

Create a local deployment folder:

```bash
mkdir kims-deployment
cd kims-deployment
```

### 1b. Copy Project Files

Copy the entire KIMS project to the deployment folder, **excluding:**

```
.git/
.gitignore
node_modules/
tests/
*.md (except critical docs)
.env (will be created on server)
config/.env.example
```

**What TO include:**

```
kims-deployment/
├── public/              ← Document root
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── uploads/
│   └── .htaccess
├── src/                 ← Application code
├── config/
│   ├── config.php
│   ├── database.php
│   └── .env.example
├── migrations/          ← SQL files
├── migrate.php          ← Migration runner
├── composer.json
├── composer.lock
└── router.php           ← URL rewriting for local dev
```

---

## Step 2: cPanel Setup

### 2a. Create Database

1. Log in to **cPanel**
2. Go to **MySQL Databases**
3. Click **Create New Database**
4. Enter database name: `yourdomain_kims` or `kims_production`
5. Click **Create Database**

**Note the:**
- Database Name: `yourdomain_kims`
- Host: `localhost` (usually)

### 2b. Create Database User

1. In **MySQL Databases**, scroll to **MySQL Users**
2. Click **Create New User**
3. Enter username: `yourdomain_kims` (or `kims_user`)
4. Enter strong password
5. Click **Create User**

### 2c. Assign User to Database

1. Scroll to **Add User to Database**
2. Select the user from dropdown
3. Select the database from dropdown
4. Click **Add**
5. **Check ALL privileges** in the next dialog
6. Click **Make Changes**

**Write down:**
- Username
- Password
- Database name
- Host (usually `localhost`)

---

## Step 3: Configure Environment

### 3a. Create `.env` File on Server

Via FTP or cPanel File Manager:

1. Navigate to `config/` directory
2. Create new file: `.env`
3. Add your database credentials:

```
DB_HOST=localhost
DB_NAME=yourdomain_kims
DB_USER=yourdomain_kims
DB_PASSWORD=your_strong_password_here
```

**Security:** Make sure `.env` is NOT in the document root (it's in `config/`).

### 3b. Verify File Permissions

Via cPanel **File Manager**:

```
config/.env                 644 (read-only)
config/                     755 (public_kims user readable)
public/uploads/             755 (writable for uploads)
public/                     755
src/                        755
migrations/                 755
```

**Via SSH (if available):**

```bash
chmod 644 config/.env
chmod 755 config/
chmod 755 public/uploads/
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

---

## Step 4: Configure .htaccess

### 4a. Public/.htaccess (URL Rewriting)

Create or update `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Remove index.php from URLs
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Deny from all
</Files>

<Files "config.php">
    Deny from all
</Files>
```

### 4b. Root .htaccess (Security)

Create `.htaccess` in root (if not already there):

```apache
# Prevent direct access to non-public directories
<FilesMatch "^(.env|config|src|migrations|composer|migrate)">
    Deny from all
</FilesMatch>

# Disable directory listing
Options -Indexes
```

---

## Step 5: Install Dependencies via SSH

If your cPanel has SSH access enabled:

```bash
ssh your_cpanel_user@your_domain.com

cd public_html/kims

composer install --optimize-autoloader --no-dev
```

**If SSH is NOT available:**

1. Install dependencies locally:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. FTP upload the `vendor/` folder to server

---

## Step 6: Run Database Migrations

### Via SSH (Recommended)

```bash
ssh your_cpanel_user@your_domain.com

cd public_html/kims

php migrate.php
```

**Expected output:**

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
```

### Via Web Browser (If SSH Unavailable)

1. Create temporary web migration runner

**File: `public/run-migrations.php`**

```php
<?php
// TEMPORARY: Delete after migrations complete!
// Security: Only run once, then delete this file

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <html>
    <body>
        <h1>Run Migrations</h1>
        <p><strong>WARNING:</strong> This page should be deleted after migrations run!</p>
        <form method="POST">
            <button type="submit">Run Migrations Now</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

require '../migrate.php';
?>
```

2. Upload file to `public/` directory

3. Visit: `https://your-domain.com/run-migrations.php`

4. Click button to run migrations

5. **DELETE `public/run-migrations.php` immediately after!**

---

## Step 7: Verify Installation

### Check Database

```bash
php check-migrations.php
```

Should output:
```
Migrations table EXISTS
Total migrations recorded: 2
✓ 001_initial_schema.sql
✓ 002_all_updates.sql
```

### Check Web Access

Visit: `https://your-domain.com/`

Should see login page with form fields

### Check Default Credentials

Login with:
- **Email:** `admin@jerseystore.com`
- **Password:** `admin123`

---

## FTP Deployment Steps (Complete)

### Using FileZilla or Similar FTP Client

1. **Connect to Server**
   - Host: `ftp.your-domain.com` or IP from cPanel
   - Username: cPanel username
   - Password: cPanel password
   - Port: 21

2. **Navigate to Document Root**
   - Usually: `/public_html/` or `/www/`

3. **Upload Files**
   ```
   Upload from local kims-deployment/ to /public_html/kims/
   ```

4. **Create `config/.env`** (can't create via FTP easily)
   - Use cPanel File Manager
   - Or create locally and upload

5. **Verify Uploads**
   - Check all directories present
   - Check `public/index.php` exists
   - Check `migrate.php` exists

---

## Complete FTP Directory Structure

After FTP deployment:

```
public_html/
└── kims/
    ├── public/
    │   ├── .htaccess
    │   ├── index.php
    │   ├── css/
    │   ├── js/
    │   ├── images/
    │   ├── uploads/
    │   └── vendor/
    ├── src/
    │   ├── Controllers/
    │   ├── Models/
    │   ├── Views/
    │   └── Core/
    ├── config/
    │   ├── .env           ← Created on server
    │   ├── config.php
    │   └── database.php
    ├── migrations/
    │   ├── 001_initial_schema.sql
    │   └── 002_all_updates.sql
    ├── migrate.php
    ├── check-migrations.php
    ├── check-migrations-table.php
    ├── router.php
    ├── composer.json
    └── composer.lock
```

---

## Post-Deployment

### 1. Test Login

```
URL: https://your-domain.com/kims/public/
Email: admin@jerseystore.com
Password: admin123
```

### 2. Change Admin Password

1. Log in with default credentials
2. Navigate to **Settings** or **Profile**
3. Change password immediately

### 3. Delete Migration Runner (If Used)

If you created `public/run-migrations.php`, delete it:
- Via FTP
- Via cPanel File Manager

### 4. Verify Database Connection

Check that orders, products, users appear in database

### 5. Test Key Features

- [ ] Create a new product
- [ ] Create a new order
- [ ] Run a report
- [ ] Check file uploads work

---

## Troubleshooting

### Issue: "404 Not Found" on all pages except index

**Cause:** .htaccess not working or mod_rewrite disabled

**Solution:**
1. Check if `public/.htaccess` exists
2. Contact hosting and ask to enable `mod_rewrite`
3. Alternative: Add `?url=` parameter manually (not ideal)

### Issue: "Access Denied" to uploads directory

**Cause:** Wrong permissions

**Solution:**
```bash
chmod 755 public/uploads/
```

### Issue: Database connection error

**Cause:** Wrong credentials in `.env`

**Solution:**
1. Verify database name in cPanel
2. Verify username in cPanel
3. Verify password (try resetting it)
4. Verify host (usually `localhost`)

### Issue: "Class not found" errors

**Cause:** Composer dependencies not installed

**Solution:**
1. Run `composer install` locally
2. Upload `vendor/` folder via FTP
3. Or run `composer install` via SSH

### Issue: "No such file or directory: migrations/001_..."

**Cause:** Migrations folder not uploaded

**Solution:**
1. Verify `migrations/` directory on server
2. FTP upload missing files
3. Run `php migrate.php` again

---

## Security Checklist

- [ ] `.env` file NOT in `public_html/` (should be in parent)
- [ ] `.htaccess` blocks access to `.env`, `config/`, `src/`
- [ ] Changed default admin password
- [ ] Disabled directory listing (`Options -Indexes`)
- [ ] HTTPS enabled (SSL certificate)
- [ ] Database backups configured in cPanel
- [ ] File permissions set correctly (755 dirs, 644 files)
- [ ] `vendor/` directory has proper permissions

---

## Updating Code on Live Server

When you push new code:

### Option 1: Full FTP Upload (Simple)

1. Download latest from GitHub
2. FTP upload entire folder (overwrites existing)
3. Preserve `config/.env` (don't overwrite!)

### Option 2: Git on Server (Better)

If SSH available:

```bash
ssh your_cpanel_user@your_domain.com
cd public_html/kims
git pull origin main
composer install --optimize-autoloader --no-dev
php migrate.php  # If new migrations added
```

---

## Performance Optimization

### Enable Gzip Compression

Add to `public/.htaccess`:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### Browser Caching

Add to `public/.htaccess`:

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Backup Strategy

### Automated Backups (cPanel)

1. **cPanel** → **Backup**
2. Set automatic daily/weekly backups
3. Download monthly backups locally

### Manual Database Backup

```bash
mysqldump -u yourdomain_kims -p yourdomain_kims > backup.sql
```

---

## Support & Reference

- **cPanel Docs:** https://documentation.cpanel.net/
- **PHP Mail Issues:** If contact forms don't work, check cPanel mail settings
- **SSL Certificate:** Enable via AutoSSL in cPanel
- **Database Access:** Via cPanel → phpMyAdmin

---

## Summary

| Task | Method | Time |
|------|--------|------|
| **Database Setup** | cPanel UI | 5 min |
| **Upload Files** | FTP | 10-30 min |
| **Configure .env** | File Manager | 2 min |
| **Run Migrations** | SSH or Web | 1 min |
| **Test Installation** | Browser | 5 min |
| **TOTAL** | | ~25-45 min |

---

## Quick Reference Commands

```bash
# SSH Login
ssh username@domain.com

# Run migrations
php migrate.php

# Check migration status
php check-migrations.php

# View database
mysql -u username -p database_name

# Backup database
mysqldump -u username -p database_name > backup.sql

# Set file permissions
chmod 755 public/uploads/
chmod 644 config/.env
```

---

**Deployment Date:** ___________  
**Admin Password Changed:** ___________  
**Backups Configured:** ___________

