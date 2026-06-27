# KIMS Deployment - Quick Reference

Ultra-fast checklist and command reference for cPanel FTP deployment.

---

## 📋 One-Page Deployment Flow

```
1. PREPARE FILES (5 min)
   └─ Run: prepare-ftp-deployment.bat (Windows) or .sh (Mac/Linux)
   └─ Creates clean deployment package

2. CPANEL SETUP (10 min)
   └─ Create database
   └─ Create database user
   └─ Assign user to database with ALL permissions

3. FTP UPLOAD (10-30 min)
   └─ Upload kims-deployment/ to /public_html/kims/

4. CONFIGURE SERVER (5 min)
   └─ Create config/.env with database credentials
   └─ Set file permissions (755 dirs, 644 files)
   └─ Update public/.htaccess

5. MIGRATE DATABASE (2 min)
   └─ SSH: php migrate.php
   └─ Verify: php check-migrations.php

6. TEST & VERIFY (5 min)
   └─ Visit https://your-domain.com
   └─ Login with admin@jerseystore.com / admin123
   └─ Change admin password
   └─ Test creating a product

TOTAL TIME: ~45 minutes
```

---

## 🚀 Abbreviated Command Reference

### Preparation (Local Machine)

```bash
# Windows
prepare-ftp-deployment.bat

# Mac/Linux
bash prepare-ftp-deployment.sh
```

### SSH/Terminal (On Server)

```bash
# Login
ssh username@domain.com

# Navigate to project
cd public_html/kims

# Run migrations
php migrate.php

# Check status
php check-migrations.php

# Exit
exit
```

### Database Credentials (In config/.env)

```
DB_HOST=localhost
DB_NAME=yourdomain_kims
DB_USER=yourdomain_kims
DB_PASSWORD=your_strong_password
```

### File Permissions

```bash
# Directories: readable and executable
chmod 755 public uploads config migrations

# Files: readable
chmod 644 .htaccess *.php

# Make sure .env is not accessible from web
chmod 644 config/.env
```

---

## ✅ Pre-Deployment Checklist

**Local Machine:**
- [ ] Git branch: `main`
- [ ] All changes committed
- [ ] No uncommitted files: `git status`

**cPanel (Before Upload):**
- [ ] Database created
- [ ] Database user created
- [ ] User assigned to database
- [ ] Credentials saved (Host, Name, User, Pass)

**FTP (Before Upload):**
- [ ] FTP client ready (FileZilla, WinSCP, Transmit)
- [ ] FTP credentials obtained
- [ ] Connected to server

---

## 📦 Folder Structure After Upload

```
public_html/kims/          ← Project root
├── public/                ← Web-accessible directory
│   ├── .htaccess         (URL rewriting)
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/          (for user uploads)
├── src/                   ← Application code
├── config/
│   ├── .env              (create on server!)
│   ├── config.php
│   └── database.php
├── migrations/
│   ├── 001_initial_schema.sql
│   └── 002_all_updates.sql
├── migrate.php            (migration runner)
├── check-migrations.php   (status checker)
└── composer.json
```

---

## 🔐 Security Quick Checklist

- [ ] `.env` file exists in `config/` (NOT in `public/`)
- [ ] `.env` is not accessible via web (file permissions 644)
- [ ] `public/.htaccess` has security headers
- [ ] Directory listing disabled
- [ ] Hidden files blocked
- [ ] PHP disabled in `uploads/` directory
- [ ] HTTPS/SSL enabled
- [ ] Admin password changed from default
- [ ] Database backups configured

---

## 🐛 Quick Troubleshooting

| Error | Solution |
|-------|----------|
| **404 on all routes** | Check `public/.htaccess` exists and `mod_rewrite` enabled |
| **Database connection failed** | Verify credentials in `config/.env` match cPanel |
| **Class not found** | Upload `vendor/` folder or run `composer install` |
| **Permission denied** | `chmod 755 directory_name` |
| **Timeout during migration** | Increase PHP `max_execution_time` to 300 in cPanel |
| **Table already exists** | Safe to ignore - migrations are idempotent |
| **Can't create uploads** | `chmod 755 public/uploads/` |

---

## 🔍 Verification Steps

### After Upload, Before Migration

```bash
# SSH in and verify
ls -la public/                # Should see .htaccess, index.php
ls -la config/                # Should see .env.template (will rename to .env)
ls -la migrations/            # Should see SQL files
php migrate.php               # Should work without error
```

### After Migration

```bash
php check-migrations.php      # Should show 2 migrations completed
mysql -u user -p database -e "SELECT * FROM migrations;"
mysql -u user -p database -e "SELECT * FROM users;"  # Should see admin
```

### After First Login

1. Visit: `https://your-domain.com/`
2. Login: `admin@jerseystore.com` / `admin123`
3. Change password immediately
4. Create a test product
5. Create a test order
6. Check `/dashboard`

---

## 📱 Domain Configuration

### If KIMS in subdirectory: `/kims/`

**Update `public/.htaccess`:**
```apache
RewriteBase /kims/
```

**Access at:**
```
https://your-domain.com/kims/
https://your-domain.com/kims/public/
https://your-domain.com/kims/dashboard
```

### If KIMS in root document root

**RewriteBase:**
```apache
RewriteBase /
```

**Access at:**
```
https://your-domain.com/
https://your-domain.com/dashboard
```

---

## 📞 What to Ask Hosting Provider

If things don't work:

1. **"Is mod_rewrite enabled?"**
   - Need this for URL rewriting
   - If not, ask to enable it

2. **"What's the PHP version?"**
   - KIMS needs 7.4 or higher
   - Can be set in cPanel

3. **"Can I increase max_execution_time to 300?"**
   - For migration timeouts
   - Set in cPanel → Select PHP Version

4. **"Can you check error logs?"**
   - Ask for `/error_log` or cPanel error logs
   - Helps diagnose issues

5. **"Are required PHP extensions installed?"**
   - mysqli or pdo_mysql (database)
   - json (required)
   - openssl (for HTTPS)

---

## 🎯 Success Indicators

After deployment, you should see:

- ✅ Dashboard loads at `/dashboard`
- ✅ Can login with admin credentials
- ✅ Can create products and orders
- ✅ Reports load without errors
- ✅ File uploads work to `public/uploads/`
- ✅ No 404 errors on CSS/JS files
- ✅ HTTPS works (if configured)

---

## 📊 Time Breakdown

| Task | Time |
|------|------|
| Prepare files | 5 min |
| cPanel setup | 10 min |
| FTP upload | 10-30 min |
| Server config | 5 min |
| Migration | 1 min |
| Testing | 5 min |
| **TOTAL** | **~45 min** |

---

## 📚 Detailed Guides

For more information, see:

- `CPANEL_DEPLOYMENT_GUIDE.md` - Complete step-by-step setup
- `MIGRATION_DEPLOYMENT_STEPS.md` - Migration details and troubleshooting
- `HTACCESS_TEMPLATE.md` - .htaccess configuration reference
- `DEPLOYMENT_CHECKLIST.md` - Full deployment checklist

---

## 🆘 Stuck? Try This

1. **Check error logs**
   ```bash
   tail -f error_log  # SSH
   ```

2. **Test database connection**
   ```bash
   php -r "mysqli_connect('localhost','user','pass','db');" 
   ```

3. **Verify file structure**
   ```bash
   ls -R public_html/kims/
   ```

4. **Check file permissions**
   ```bash
   ls -la public_html/kims/
   ```

5. **Run migrations in verbose mode**
   ```bash
   php migrate.php
   ```

6. **Contact hosting support with:**
   - Error message (copy-paste from error log)
   - What you're trying to do
   - Server configuration (PHP version, MySQL version, etc.)

---

## 🎓 Learning Resources

- **cPanel Docs:** https://documentation.cpanel.net/
- **Apache .htaccess:** https://httpd.apache.org/docs/current/howto/htaccess.html
- **MySQL:** https://dev.mysql.com/doc/
- **PHP:** https://www.php.net/manual/

---

## ✨ Pro Tips

1. **Test locally first** - Never deploy untested code
2. **Keep backups** - Set up automatic backups in cPanel
3. **Monitor logs** - Check error logs regularly for issues
4. **Update passwords** - Change default admin password immediately
5. **Enable HTTPS** - Use AutoSSL in cPanel for free SSL
6. **Plan updates** - Schedule deployments during low-traffic hours

---

## 🔄 Update Procedure

When pushing new code:

```bash
# Option 1: Full FTP upload (simple)
# - Download latest from GitHub
# - Upload entire folder (preserves .env)

# Option 2: Git pull (faster)
ssh username@domain.com
cd public_html/kims
git pull origin main
composer install --optimize-autoloader --no-dev
php migrate.php  # If new migrations added
```

---

## 📝 Notes Section

```
Deployment Date: _______________
Domain: _______________
cPanel User: _______________
Database Name: _______________
Database User: _______________
FTP Host: _______________
SSH Host: _______________

Admin Email: admin@jerseystore.com
New Admin Password: _______________

Issues Encountered:
_________________________________
_________________________________

Resolution:
_________________________________
_________________________________
```

---

**Quick Links:**
- cPanel Login: `https://your-domain.com:2083`
- Admin URL: `https://your-domain.com/`
- phpMyAdmin: `https://your-domain.com/phpmyadmin`

---

**Last Updated:** 2026-06-28  
**Version:** 1.0  
**Status:** Ready for Deployment ✅

