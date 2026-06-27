# .htaccess Configuration for cPanel Deployment

Complete .htaccess files for KIMS deployment on cPanel with Apache.

---

## File 1: `public/.htaccess` (Document Root)

**Location:** `public/.htaccess`

**Purpose:** URL rewriting and security for the web-accessible directory

```apache
# Enable mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Remove trailing slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.+)/$ /$1 [L,R=301]
    
    # Route all requests through index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
</IfModule>

# Prevent access to hidden files/directories
<Files ".*">
    Order Deny,Allow
    Deny From All
</Files>

# Prevent direct access to sensitive files
<Files ".env">
    Order Deny,Allow
    Deny from all
</Files>

<FilesMatch "\.php$">
    Allow from all
</FilesMatch>

# Disable directory listing
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

# Enable GZIP compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xml+rss
</IfModule>

# Set Cache-Control headers
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    
    # CSS
    ExpiresByType text/css "access plus 1 month"
    
    # JavaScript
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # Default
    ExpiresDefault "access plus 2 days"
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS Protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable PHP execution in upload directory
<Directory "uploads">
    php_flag engine off
</Directory>

# Set correct MIME types
<IfModule mod_mime.c>
    AddType text/html .html .htm
    AddType text/css .css
    AddType application/javascript .js
    AddType application/json .json
    AddType application/xml .xml
    AddType image/svg+xml .svg
    AddType font/woff .woff
    AddType font/woff2 .woff2
</IfModule>
```

---

## File 2: `.htaccess` (Project Root)

**Location:** `.htaccess` (in root, outside public/)

**Purpose:** Prevent direct access to sensitive directories

```apache
# Disable directory listing
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

# Block access to sensitive files and directories
<FilesMatch "^\.">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Protect sensitive files
<FilesMatch "\.(env|sql|sqlite)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Block access to sensitive directories
<DirectoryMatch "^/(config|src|migrations|tests|node_modules|vendor)">
    Order Deny,Allow
    Deny from all
</DirectoryMatch>

# Allow access to public directory only
<Directory "public">
    Order Allow,Deny
    Allow from all
</Directory>
```

---

## Minimal Configuration (If Above Doesn't Work)

If you get 500 errors with the full config, use this minimal version:

**File:** `public/.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
</IfModule>

Options -Indexes
```

---

## Troubleshooting .htaccess

### Issue: 500 Internal Server Error

**Cause:** One of the directives is not supported

**Solution:**
1. Start with minimal config above
2. Add sections one at a time
3. Test after each addition
4. Find which one causes the error
5. Comment out that section

### Issue: "mod_rewrite is not available"

**Cause:** Apache doesn't have mod_rewrite enabled

**Solution:**
Contact your hosting provider. Ask them to enable `mod_rewrite` module.

### Issue: URLs still have "index.php" in them

**Cause:** RewriteBase is wrong

**Solution:** Change `RewriteBase /` to `RewriteBase /kims/public/` if KIMS is in a subdirectory.

### Issue: CSS/JS files don't load

**Cause:** Rewrite rule is too aggressive

**Solution:** Verify these lines exist:
```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
```

### Issue: "htaccess: Invalid command"

**Cause:** Typo or unsupported directive

**Solution:**
1. Check for spelling errors
2. Check Apache version (ask hosting provider)
3. Remove unsupported directives
4. Start with minimal config

---

## Testing .htaccess

### Test 1: Files Load Correctly

Visit these URLs - they should work:

```
https://your-domain.com/
https://your-domain.com/dashboard
https://your-domain.com/products
https://your-domain.com/orders
```

**If not working:**
- Check `RewriteBase`
- Verify `mod_rewrite` is enabled
- Check file permissions (755 for dirs, 644 for files)

### Test 2: CSS/JS Load

In browser DevTools, check Network tab:

- CSS files should return 200 (green)
- JS files should return 200 (green)
- No 404 errors

**If getting 404:**
- Check CSS/JS paths in HTML
- Check file permissions
- Check .htaccess rewrite rules

### Test 3: Security Headers

In browser DevTools Console, check:

```javascript
// Open DevTools → Console
// You should see these in Response Headers:
// X-Frame-Options: SAMEORIGIN
// X-Content-Type-Options: nosniff
// X-XSS-Protection: 1; mode=block
```

### Test 4: Uploads Work

Try uploading a product image:

- Should save to `public/uploads/`
- Should be accessible via web
- Should NOT be executable as PHP

---

## Configuration by Hosting Type

### Shared Hosting (Most Common)

Use **Public/.htaccess** configuration above.

The root .htaccess might be restricted - if you get errors, remove it.

### VPS / Dedicated Server

Use both configurations:
- **Root `.htaccess`** for security
- **Public/.htaccess** for routing

### cPanel with EasyApache

Everything should work. If issues:

1. Check PHP handler (DSO vs FCGId vs FPM)
2. Check Apache version
3. Contact hosting support

---

## Security Best Practices

### 1. Protect .env File

```apache
<Files ".env">
    Order Deny,Allow
    Deny from all
</Files>
```

### 2. Prevent Script Execution in Uploads

```apache
<Directory "public/uploads">
    php_flag engine off
    AddType text/plain .php .php3 .php4 .php5 .phtml
</Directory>
```

### 3. Enable Security Headers

```apache
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

### 4. Disable Directory Listing

```apache
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>
```

### 5. Block Hidden Files

```apache
<FilesMatch "^\.">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

---

## Creating .htaccess Files

### Via cPanel File Manager

1. Log in to cPanel
2. Open **File Manager**
3. Navigate to `public/` directory
4. Right-click → **Create New File**
5. Name: `.htaccess`
6. Click **Create**
7. Right-click file → **Edit**
8. Paste content from above
9. Click **Save**

### Via SSH

```bash
# Navigate to public directory
cd public

# Create file
nano .htaccess

# Paste content (right-click in terminal)
# Press Ctrl+X
# Press Y
# Press Enter
```

### Via FTP

1. Create `.htaccess` locally
2. Open FTP client
3. Navigate to `public/` on server
4. Upload `.htaccess` file

---

## Common RewriteBase Values

Choose based on where you install KIMS:

```
Document Root: /public_html/
Project Location: /public_html/kims/
RewriteBase: /kims/
```

```
Document Root: /public_html/kims/public/
RewriteBase: /
```

```
Document Root: /public_html/
Project Location: /public_html/
Public in: /public_html/public/
RewriteBase: /public/
```

If unsure, ask your hosting provider where the document root is.

---

## Verification Checklist

After setting up .htaccess:

- [ ] Visit homepage - loads without `/index.php`
- [ ] Visit `/dashboard` - works without `/index.php`
- [ ] CSS/JS files load (200 status)
- [ ] Images load correctly
- [ ] Login page appears
- [ ] Forms submit successfully
- [ ] No 500 errors in error logs
- [ ] Security headers visible in DevTools

---

## Getting Help

If .htaccess isn't working:

1. Check cPanel error logs
2. Contact hosting support with error message
3. Ask if:
   - mod_rewrite is enabled?
   - What Apache version?
   - Can they check error logs?
   - Can they enable required modules?

---

## Quick Reference

| Task | Command |
|------|---------|
| View file | `cat .htaccess` |
| Edit file | `nano .htaccess` |
| Delete file | `rm .htaccess` |
| Check permissions | `ls -la .htaccess` |
| Fix permissions | `chmod 644 .htaccess` |

---

## Summary

1. **Create `public/.htaccess`** with full configuration
2. **Create root `.htaccess`** for security (optional)
3. **Test in browser** - all URLs should work
4. **Check error logs** if issues occur
5. **Ask hosting provider** if specific modules disabled

**File Priority:**
1. `public/.htaccess` - REQUIRED
2. Root `.htaccess` - OPTIONAL (for extra security)

