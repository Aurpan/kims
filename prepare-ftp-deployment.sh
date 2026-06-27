#!/bin/bash

# KIMS FTP Deployment Preparation Script
# Creates a clean deployment package ready for FTP upload to cPanel

set -e

echo "================================"
echo "KIMS FTP Deployment Preparation"
echo "================================"
echo ""

# Get deployment folder name from user or use default
DEPLOY_DIR="${1:-.}/kims-deployment"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create deployment directory
echo "📁 Creating deployment directory: $DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

# Files and directories to COPY
declare -a COPY_ITEMS=(
    "public"
    "src"
    "config"
    "migrations"
    "composer.json"
    "composer.lock"
    "migrate.php"
    "check-migrations.php"
    "check-migrations-table.php"
    "router.php"
)

# Files to EXCLUDE
declare -a EXCLUDE_ITEMS=(
    ".git"
    ".gitignore"
    "node_modules"
    "tests"
    ".DS_Store"
    "*.md"
    ".env"
    "config/.env.example"
    ".vscode"
    ".idea"
)

echo ""
echo "📋 Copying project files..."

# Copy items
for item in "${COPY_ITEMS[@]}"; do
    if [ -e "$item" ]; then
        echo "  ✓ Copying $item"
        cp -r "$item" "$DEPLOY_DIR/"
    else
        echo "  ✗ Warning: $item not found (skipping)"
    fi
done

echo ""
echo "🔐 Setting up .env file..."

# Copy .env.example as template
if [ -f "config/.env.example" ]; then
    cp "config/.env.example" "$DEPLOY_DIR/config/.env.template"
    echo "  ✓ Created config/.env.template (edit on server)"
else
    # Create template if it doesn't exist
    cat > "$DEPLOY_DIR/config/.env.template" << 'EOF'
DB_HOST=localhost
DB_NAME=yourdomain_kims
DB_USER=yourdomain_kims
DB_PASSWORD=your_password_here
EOF
    echo "  ✓ Created config/.env.template"
fi

echo ""
echo "📝 Creating deployment documentation..."

# Copy deployment guides
if [ -f "CPANEL_DEPLOYMENT_GUIDE.md" ]; then
    cp "CPANEL_DEPLOYMENT_GUIDE.md" "$DEPLOY_DIR/"
    echo "  ✓ Added CPANEL_DEPLOYMENT_GUIDE.md"
fi

if [ -f "MIGRATION_DEPLOYMENT_STEPS.md" ]; then
    cp "MIGRATION_DEPLOYMENT_STEPS.md" "$DEPLOY_DIR/"
    echo "  ✓ Added MIGRATION_DEPLOYMENT_STEPS.md"
fi

# Create quick start guide
cat > "$DEPLOY_DIR/README_DEPLOYMENT.md" << 'EOF'
# KIMS Deployment Package

## Quick Start

1. **Upload Files via FTP**
   - Use FileZilla or similar FTP client
   - Upload contents of this folder to `/public_html/kims/` on cPanel

2. **Create Database in cPanel**
   - Go to MySQL Databases
   - Create database and user
   - Grant full permissions

3. **Configure Environment**
   - Rename `config/.env.template` to `config/.env`
   - Edit with your database credentials

4. **Run Migrations via SSH**
   ```bash
   ssh your_user@your_domain.com
   cd public_html/kims
   php migrate.php
   ```

5. **Test Login**
   - Visit: https://your-domain.com/kims/public/
   - Email: admin@jerseystore.com
   - Password: admin123
   - **Change password immediately!**

## Detailed Documentation

- See `CPANEL_DEPLOYMENT_GUIDE.md` for complete setup instructions
- See `MIGRATION_DEPLOYMENT_STEPS.md` for migration steps

## File Structure

```
kims/
├── public/              ← Web root (document root)
├── src/                 ← Application code
├── config/              ← Configuration
├── migrations/          ← Database migrations
├── migrate.php          ← Migration runner
├── check-migrations.php ← Migration status checker
└── composer.json        ← PHP dependencies
```

## Next Steps

1. Upload all files via FTP
2. Create `config/.env` with your database credentials
3. Run migrations: `php migrate.php`
4. Access application
5. Change admin password
EOF

echo "  ✓ Created README_DEPLOYMENT.md"

echo ""
echo "✨ Creating deployment checklist..."

# Create deployment checklist
cat > "$DEPLOY_DIR/DEPLOYMENT_CHECKLIST.md" << 'EOF'
# cPanel Deployment Checklist

## Pre-Upload
- [ ] All changes committed to git
- [ ] Latest code pulled from GitHub
- [ ] Composer dependencies included
- [ ] No `.env` file in package

## cPanel Setup
- [ ] Database created in MySQL Databases
- [ ] Database user created
- [ ] User assigned to database with ALL privileges
- [ ] Database credentials noted

## FTP Upload
- [ ] FTP credentials obtained
- [ ] FTP client installed (FileZilla, etc.)
- [ ] Connected to hosting via FTP
- [ ] All files uploaded to `/public_html/kims/`
- [ ] Verified all directories present:
  - [ ] public/
  - [ ] src/
  - [ ] config/
  - [ ] migrations/

## Server Configuration
- [ ] Created `config/.env` with database credentials
- [ ] Set file permissions (755 for dirs, 644 for files)
- [ ] Verified `.env` file is NOT in document root
- [ ] Updated `public/.htaccess` for URL rewriting

## Database Setup
- [ ] SSH access enabled (ask hosting if needed)
- [ ] Logged in via SSH
- [ ] Navigated to project: `cd public_html/kims`
- [ ] Ran migrations: `php migrate.php`
- [ ] Verified success: `php check-migrations.php`

## Verification
- [ ] Accessed application: https://your-domain.com/
- [ ] Logged in with admin credentials
- [ ] Changed admin password to secure password
- [ ] Tested creating a product
- [ ] Tested creating an order
- [ ] Tested viewing reports

## Security
- [ ] Deleted any temporary migration files
- [ ] HTTPS/SSL enabled on domain
- [ ] Database backups configured
- [ ] File permissions correct
- [ ] `.env` protected (not accessible via web)

## Documentation
- [ ] Saved this checklist
- [ ] Documented database credentials securely
- [ ] Noted deployment date
- [ ] Shared access info with team

## Final Steps
- [ ] Notify team of deployment
- [ ] Monitor error logs for issues
- [ ] Schedule regular backups
- [ ] Plan for future updates

---

**Deployment Date:** ___________
**Deployed By:** ___________
**Status:** ✓ Complete / ⚠️ In Progress / ✗ Needs Review

EOF

echo "  ✓ Created DEPLOYMENT_CHECKLIST.md"

echo ""
echo "🗜️  Removing unnecessary files..."

# Remove markdown files from deployment (except deployment guides)
find "$DEPLOY_DIR" -maxdepth 1 -name "*.md" -type f -delete 2>/dev/null || true

# Remove .env example
rm -f "$DEPLOY_DIR/config/.env.example" 2>/dev/null || true

# Remove git files
rm -rf "$DEPLOY_DIR/.git" 2>/dev/null || true
rm -f "$DEPLOY_DIR/.gitignore" 2>/dev/null || true

echo "  ✓ Cleaned up unnecessary files"

echo ""
echo "📊 Deployment Package Summary"
echo "================================"

# Calculate size
TOTAL_SIZE=$(du -sh "$DEPLOY_DIR" | cut -f1)
FILE_COUNT=$(find "$DEPLOY_DIR" -type f | wc -l)
DIR_COUNT=$(find "$DEPLOY_DIR" -type d | wc -l)

echo "Location: $(pwd)/$DEPLOY_DIR"
echo "Total Size: $TOTAL_SIZE"
echo "Files: $FILE_COUNT"
echo "Directories: $DIR_COUNT"

echo ""
echo "📋 Contents:"
echo ""

# List main directories
echo "Directories:"
ls -d "$DEPLOY_DIR"/*/ 2>/dev/null | sed "s|$DEPLOY_DIR/||g" | sed 's|/$||' | sed 's/^/  ├─ /'

echo ""
echo "Files:"
ls -1 "$DEPLOY_DIR" | grep -v "^[^.]" | grep -v "^config$\|^public$\|^src$\|^migrations$" | sed 's/^/  ├─ /'

echo ""
echo "✅ Deployment package ready!"
echo ""
echo "📦 Next Steps:"
echo "  1. Transfer folder to your computer"
echo "  2. Open FTP client (FileZilla)"
echo "  3. Upload to /public_html/kims/ on cPanel"
echo "  4. Follow README_DEPLOYMENT.md"
echo ""
echo "📖 Documentation:"
echo "  - $DEPLOY_DIR/README_DEPLOYMENT.md"
echo "  - $DEPLOY_DIR/CPANEL_DEPLOYMENT_GUIDE.md"
echo "  - $DEPLOY_DIR/MIGRATION_DEPLOYMENT_STEPS.md"
echo "  - $DEPLOY_DIR/DEPLOYMENT_CHECKLIST.md"
echo ""

