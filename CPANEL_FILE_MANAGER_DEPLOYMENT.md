# KIMS Deployment via cPanel File Manager

Step-by-step guide to deploy KIMS using cPanel's built-in File Manager (no FTP client needed).

---

## ✅ Prerequisites

- cPanel login credentials
- All project files ready locally (latest from GitHub)
- Database already created in cPanel (see CPANEL_DEPLOYMENT_GUIDE.md Step 2)
- Database user created with ALL privileges

---

## 📋 Step 1: Prepare Local Files

### On Your Computer

```bash
# Navigate to project directory
cd path/to/kims

# Ensure all changes are committed
git status

# Pull latest code
git pull origin main
```

### Create deployment folder (Optional but recommended)

```bash
# Create folder with latest code
mkdir kims-deploy
cp -r public src config migrations *.php composer.* kims-deploy/
```

**Exclude these from upload:**
- .git/
- .gitignore
- node_modules/
- tests/
- *.md files (except deployment docs)
- .env (will create on server)

---

## 🌐 Step 2: Log into cPanel

1. **Open browser** and go to:
   ```
   https://your-domain.com:2083
   ```
   Or:
   ```
   https://cpanel-ip:2083
   ```

2. **Enter credentials:**
   - Username: Your cPanel username
   - Password: Your cPanel password

3. **Click "Log In"**

---

## 📂 Step 3: Open File Manager

1. In cPanel dashboard, search for **"File Manager"**
2. Click **File Manager** (or go to **Files** > **File Manager**)
3. Choose **"Web Root"** option
4. Click **"Go"** (or double-click Web Root folder)

You should see:
```
public_html/
```

---

## 🗂️ Step 4: Create KIMS Directory

1. In File Manager, right-click in empty space
2. Select **"Create New Folder"**
3. Name: **`kims`**
4. Click **"Create New Folder"** button

Result:
```
public_html/
└── kims/  (newly created)
```

---

## 📤 Step 5: Upload Files Using File Manager

### Method A: Drag & Drop (Easiest)

1. **Open two windows:**
   - Window 1: cPanel File Manager (in the `kims` folder)
   - Window 2: Your local `kims-deploy` folder (or `public_html/kims` on Windows)

2. **Drag files from local folder into cPanel window:**
   - Drag `public` folder
   - Drag `src` folder
   - Drag `config` folder
   - Drag `migrations` folder
   - Drag `*.php` files (migrate.php, router.php, etc.)
   - Drag `composer.json`, `composer.lock`

3. **Monitor progress** - File Manager shows upload status

### Method B: Using Upload Button

1. **Inside `kims` folder in File Manager**
2. Click **"Upload"** button (top toolbar)
3. Select files from your computer:
   - First upload folder: `public`
   - Then: `src`
   - Then: `config`
   - Then: `migrations`
   - Finally: All PHP files

4. **Wait for each upload to complete** before uploading next item

### Method C: Upload ZIP File (Fastest)

1. **Locally, create a ZIP file:**
   - Select all files in `kims-deploy`
   - Right-click → **"Send to"** → **"Compressed folder"**
   - Name: `kims-deploy.zip`

2. **In cPanel File Manager, click "Upload"**
3. Select `kims-deploy.zip`
4. Wait for upload to complete

5. **Extract the ZIP:**
   - Right-click `kims-deploy.zip` in File Manager
   - Select **"Extract"**
   - Confirm extraction
   - Wait for extraction to complete

6. **Delete the ZIP file:**
   - Right-click `kims-deploy.zip`
   - Select **"Delete"**
   - Confirm

---

## 🔐 Step 6: Create config/.env File

### Via File Manager

1. **Navigate to:** `public_html/kims/config/`
2. **Right-click** in empty space
3. Select **"Create New File"**
4. Name: **`.env`** (with the dot!)
5. Click **"Create New File"**

You should see `.env` file created.

### Edit the .env File

1. **Right-click** the `.env` file
2. Select **"Edit"**
3. Add your database credentials:

```
DB_HOST=localhost
DB_NAME=yourdomain_kims
DB_USER=yourdomain_kims
DB_PASSWORD=your_password_from_cpanel
```

**Note:** Replace:
- `yourdomain_kims` with your actual database name
- `yourdomain_kims` (user) with your database user
- `your_password_from_cpanel` with the password you set

4. Click **"Save Changes"** (or **"Save File"**)
5. Close the editor

---

## 🔑 Step 7: Verify File Permissions

### Check .env Permissions

1. **Right-click** `config/.env` file
2. Select **"Change Permissions"** (or **"Permissions"**)
3. Set to **`644`** (read-only):
   - Owner: Read ✓, Write ✓
   - Group: Read ✓, Write ✗
   - Others: Read ✓, Write ✗
4. Click **"Change"**

### Check Directory Permissions

1. **Right-click** `public/uploads` folder
2. Select **"Change Permissions"**
3. Set to **`755`** (readable, writable, executable):
   - Owner: Read ✓, Write ✓, Execute ✓
   - Group: Read ✓, Write ✗, Execute ✓
   - Others: Read ✓, Write ✗, Execute ✓
4. Click **"Change"**

---

## 🔧 Step 8: Create/Update .htaccess File

### For public/.htaccess

1. **Navigate to:** `public_html/kims/public/`

2. **Check if `.htaccess` exists:**
   - If yes, skip to "Edit" below
   - If no, create it:
     - Right-click → **"Create New File"**
     - Name: `.htaccess`
     - Click **"Create New File"**

3. **Edit the file:**
   - Right-click `.htaccess`
   - Select **"Edit"**

4. **Add this content:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
</IfModule>

Options -Indexes

<Files ".env">
    Order Deny,Allow
    Deny from all
</Files>
```

5. Click **"Save Changes"**

**Note:** If you installed KIMS in subdirectory `/kims/`, change:
```apache
RewriteBase /
```
to:
```apache
RewriteBase /kims/
```

---

## 🗄️ Step 9: Run Database Migrations

### Via cPanel Terminal (Easiest)

1. **In cPanel**, search for **"Terminal"**
2. Click **"Terminal"** (opens web terminal)
3. Run these commands:

```bash
# Navigate to project
cd public_html/kims

# Run migrations
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

### Verify Success

```bash
php check-migrations.php
```

Should show:
```
✓ Migrations table EXISTS
Total Migrations: 2
✓ 001_initial_schema.sql
✓ 002_all_updates.sql
```

---

## ✅ Step 10: Verify Installation

### Test in Browser

1. **Visit your domain:**
   ```
   https://your-domain.com/kims/
   ```
   Or if in root:
   ```
   https://your-domain.com/
   ```

2. **You should see:**
   - Login page with form fields
   - Navigation menu
   - No errors

3. **Login with:**
   - Email: `admin@jerseystore.com`
   - Password: `admin123`

4. **After login:**
   - You should see dashboard
   - Click around to verify pages load

### Change Admin Password Immediately

1. **After successful login**
2. Click on **Profile** or **Settings** (top right)
3. Find **"Change Password"**
4. Enter new secure password
5. Confirm password
6. Click **"Save"** or **"Update"**

---

## 📊 Verify Database Content

### Via phpMyAdmin (in cPanel)

1. **In cPanel**, search for **"phpMyAdmin"**
2. Click **phpMyAdmin** (opens in new tab)
3. Select your database from left sidebar

4. **Check tables exist:**
   - `users` - should have 1 admin user
   - `products` - should be empty (you'll add products)
   - `orders` - should be empty
   - `migrations` - should have 2 records

5. **Check users table:**
   - Click on `users` table
   - Should see `admin@jerseystore.com`

---

## 🐛 Troubleshooting

### Problem: "404 on all routes"

**Cause:** .htaccess not working

**Solution:**
1. Verify `.htaccess` exists in `public/` folder
2. Verify mod_rewrite is enabled (should be on cPanel)
3. Check RewriteBase is correct (see Step 8)
4. Try minimal .htaccess:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
</IfModule>
```

### Problem: "Database connection failed"

**Cause:** Wrong credentials in `.env`

**Solution:**
1. Open `.env` in File Manager
2. Verify values match cPanel:
   - Database name
   - Database user
   - Database password
3. Edit and save
4. Reload page and try again

### Problem: "Class not found" errors

**Cause:** Missing files or vendor folder

**Solution:**
1. Verify all folders uploaded:
   - `public/` ✓
   - `src/` ✓
   - `config/` ✓
   - `migrations/` ✓
2. Verify `composer.json` and `composer.lock` exist
3. Upload `vendor/` folder if you have it locally
   - Or run `composer install` via Terminal

### Problem: "Permission denied" errors

**Cause:** Wrong file permissions

**Solution:**
1. Right-click `public/uploads` folder
2. Change Permissions to **755**
3. Right-click `config/` folder
4. Change Permissions to **755**

### Problem: File upload times out

**Cause:** Files too large or slow connection

**Solution:**
1. Use ZIP method (Step 5, Method C)
2. Upload in smaller batches
3. Or contact hosting provider to increase upload limits

---

## 📋 File Manager Deployment Checklist

**Before Upload:**
- [ ] All files prepared locally
- [ ] Latest code pulled from GitHub
- [ ] Database created in cPanel
- [ ] Database user created with privileges

**Upload Process:**
- [ ] Logged into cPanel
- [ ] Created `kims` folder in `public_html/`
- [ ] Uploaded all files (or ZIP)
- [ ] Verified folders present: public, src, config, migrations
- [ ] Created `.env` file in config/
- [ ] Set permissions: 644 for .env, 755 for uploads

**Configuration:**
- [ ] Updated `.env` with database credentials
- [ ] Created/updated `.htaccess` in public/
- [ ] Verified RewriteBase is correct
- [ ] Set correct file permissions

**Migration:**
- [ ] Opened cPanel Terminal
- [ ] Ran: `php migrate.php`
- [ ] Verified: `php check-migrations.php`
- [ ] Confirmed 2 migrations completed

**Testing:**
- [ ] Visited domain in browser
- [ ] Login page appears
- [ ] Logged in with admin credentials
- [ ] Changed admin password
- [ ] Tested dashboard and features
- [ ] Verified database has data

---

## 💡 File Manager Tips

### Useful Shortcuts

| Action | How |
|--------|-----|
| Create folder | Right-click → "Create New Folder" |
| Create file | Right-click → "Create New File" |
| Edit file | Right-click file → "Edit" |
| Change permissions | Right-click → "Change Permissions" |
| Delete file | Right-click → "Delete" |
| Rename file | Right-click → "Rename" |
| Move file | Cut (right-click) → navigate → Paste |
| Copy file | Right-click → "Copy" → navigate → "Paste" |
| Extract ZIP | Right-click ZIP → "Extract" |

### View Hidden Files

If you don't see `.env` or `.htaccess` files:

1. In File Manager top-right, find **"Settings"** icon
2. Check **"Show Hidden Files"** or **"Show Dotfiles"**
3. Click to apply

Now you should see:
- `.env`
- `.htaccess`
- `.git/` (if not deleted)

### Refresh File List

If files don't appear after upload:

1. Click **"Refresh"** button in File Manager
2. Or press `F5` in browser
3. Or navigate away and back

---

## 📱 Mobile Friendly

cPanel File Manager works on phones/tablets too:

1. Use portrait mode for easier navigation
2. Long-tap files for context menu
3. Can edit files from phone
4. Slower than desktop but possible

---

## 🔒 Security Checklist

After deployment:

- [ ] Changed default admin password
- [ ] `.env` file not accessible from web (permissions 644)
- [ ] `.htaccess` has security headers
- [ ] Directory listing disabled (`Options -Indexes`)
- [ ] Hidden files protected (`.htaccess` blocks them)
- [ ] Backups configured in cPanel
- [ ] HTTPS/SSL enabled (ask cPanel to enable AutoSSL)

---

## 📞 If You Get Stuck

1. **Check error logs:**
   - cPanel → Error Logs (shows any PHP errors)

2. **Open cPanel Terminal:**
   - Run commands to diagnose issues
   - Check file permissions
   - Verify database connection

3. **Use phpMyAdmin:**
   - cPanel → phpMyAdmin
   - Verify database structure
   - Check if migrations created tables

4. **Ask hosting provider:**
   - Is mod_rewrite enabled?
   - What PHP version?
   - Can they check error logs?

---

## 🎉 Success!

Once you see:
- ✅ Login page loads
- ✅ Can log in with admin credentials
- ✅ Dashboard appears
- ✅ Can navigate to other pages
- ✅ Database contains admin user

**Your KIMS installation is complete!**

---

## Next Steps

1. **Create a backup** in cPanel
2. **Invite team members** to use the application
3. **Start adding products** and orders
4. **Monitor application** for any errors
5. **Plan for regular backups** and updates

---

**Estimated Time:** 30-45 minutes  
**Difficulty:** Easy (no technical knowledge needed)  
**Method:** cPanel File Manager (built-in, no tools required)

