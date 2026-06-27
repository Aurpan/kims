@echo off
REM KIMS FTP Deployment Preparation Script (Windows)
REM Creates a clean deployment package ready for FTP upload to cPanel

setlocal enabledelayedexpansion

echo.
echo ================================
echo KIMS FTP Deployment Preparation
echo ================================
echo.

REM Get deployment folder name or use default
set DEPLOY_DIR=%1
if "%DEPLOY_DIR%"=="" set DEPLOY_DIR=kims-deployment

echo Creating deployment directory: %DEPLOY_DIR%
mkdir "%DEPLOY_DIR%" 2>nul

echo.
echo Copying project files...

REM Copy directories
set FILES_TO_COPY=public src config migrations
for %%I in (%FILES_TO_COPY%) do (
    if exist "%%I" (
        echo   + Copying %%I
        xcopy /E /I /Y "%%I" "%DEPLOY_DIR%\%%I" >nul
    ) else (
        echo   - Warning: %%I not found (skipping)
    )
)

REM Copy individual files
set FILES=composer.json composer.lock migrate.php check-migrations.php check-migrations-table.php router.php
for %%F in (%FILES%) do (
    if exist "%%F" (
        echo   + Copying %%F
        copy /Y "%%F" "%DEPLOY_DIR%\" >nul
    )
)

echo.
echo Setting up environment file...

REM Create .env template
if exist "config\.env.example" (
    copy /Y "config\.env.example" "%DEPLOY_DIR%\config\.env.template" >nul
    echo   + Created config\.env.template
) else (
    (
        echo DB_HOST=localhost
        echo DB_NAME=yourdomain_kims
        echo DB_USER=yourdomain_kims
        echo DB_PASSWORD=your_password_here
    ) > "%DEPLOY_DIR%\config\.env.template"
    echo   + Created config\.env.template
)

echo.
echo Creating deployment documentation...

REM Copy deployment guides
if exist "CPANEL_DEPLOYMENT_GUIDE.md" (
    copy /Y "CPANEL_DEPLOYMENT_GUIDE.md" "%DEPLOY_DIR%\" >nul
    echo   + Added CPANEL_DEPLOYMENT_GUIDE.md
)

if exist "MIGRATION_DEPLOYMENT_STEPS.md" (
    copy /Y "MIGRATION_DEPLOYMENT_STEPS.md" "%DEPLOY_DIR%\" >nul
    echo   + Added MIGRATION_DEPLOYMENT_STEPS.md
)

REM Create README
(
echo # KIMS Deployment Package
echo.
echo ## Quick Start
echo.
echo 1. **Upload Files via FTP**
echo    - Use FileZilla or similar FTP client
echo    - Upload contents of this folder to `/public_html/kims/` on cPanel
echo.
echo 2. **Create Database in cPanel**
echo    - Go to MySQL Databases
echo    - Create database and user
echo    - Grant full permissions
echo.
echo 3. **Configure Environment**
echo    - Rename `config/.env.template` to `config/.env`
echo    - Edit with your database credentials
echo.
echo 4. **Run Migrations via SSH**
echo    ```bash
echo    ssh your_user@your_domain.com
echo    cd public_html/kims
echo    php migrate.php
echo    ```
echo.
echo 5. **Test Login**
echo    - Visit: https://your-domain.com/kims/public/
echo    - Email: admin@jerseystore.com
echo    - Password: admin123
echo    - **Change password immediately!**
echo.
echo ## Detailed Documentation
echo.
echo - See `CPANEL_DEPLOYMENT_GUIDE.md` for complete setup instructions
echo - See `MIGRATION_DEPLOYMENT_STEPS.md` for migration steps
echo.
echo ## File Structure
echo.
echo ```
echo kims/
echo ├── public/              ^<-- Web root
echo ├── src/                 ^<-- Application code
echo ├── config/              ^<-- Configuration
echo ├── migrations/          ^<-- Database migrations
echo ├── migrate.php          ^<-- Migration runner
echo └── composer.json        ^<-- PHP dependencies
echo ```
) > "%DEPLOY_DIR%\README_DEPLOYMENT.md"
echo   + Created README_DEPLOYMENT.md

REM Create Checklist
(
echo # cPanel Deployment Checklist
echo.
echo ## Pre-Upload
echo - [ ] All changes committed to git
echo - [ ] Latest code pulled from GitHub
echo - [ ] Composer dependencies included
echo.
echo ## cPanel Setup
echo - [ ] Database created in MySQL Databases
echo - [ ] Database user created
echo - [ ] User assigned to database with ALL privileges
echo - [ ] Database credentials noted
echo.
echo ## FTP Upload
echo - [ ] FTP credentials obtained
echo - [ ] FTP client installed (FileZilla, Transmit, etc.)
echo - [ ] Connected to hosting via FTP
echo - [ ] All files uploaded to `/public_html/kims/`
echo.
echo ## Server Configuration
echo - [ ] Created `config/.env` with database credentials
echo - [ ] Set file permissions (755 for dirs, 644 for files^)
echo - [ ] Updated `public/.htaccess` for URL rewriting
echo.
echo ## Database Setup
echo - [ ] Logged in via SSH
echo - [ ] Navigated to project: `cd public_html/kims`
echo - [ ] Ran migrations: `php migrate.php`
echo - [ ] Verified success: `php check-migrations.php`
echo.
echo ## Verification
echo - [ ] Accessed application: https://your-domain.com/
echo - [ ] Logged in with admin credentials
echo - [ ] Changed admin password
echo.
echo **Deployment Date:** ___________
echo **Status:** Complete / In Progress / Needs Review
) > "%DEPLOY_DIR%\DEPLOYMENT_CHECKLIST.md"
echo   + Created DEPLOYMENT_CHECKLIST.md

echo.
echo Cleaning up unnecessary files...

REM Remove .env examples
if exist "%DEPLOY_DIR%\config\.env.example" del "%DEPLOY_DIR%\config\.env.example"
echo   + Removed .env.example

echo.
echo ==================================
echo Deployment package ready!
echo ==================================
echo.
echo Location: %cd%\%DEPLOY_DIR%
echo.
for /f %%A in ('dir /s /b %DEPLOY_DIR% 2^>nul ^| find /c /v ""') do echo Files: %%A
echo.
echo Next Steps:
echo   1. Open FTP client (FileZilla, WinSCP, etc.)
echo   2. Connect to your cPanel hosting
echo   3. Upload entire "%DEPLOY_DIR%" folder to /public_html/kims/
echo   4. Follow README_DEPLOYMENT.md
echo.
echo Documentation:
echo   - README_DEPLOYMENT.md
echo   - CPANEL_DEPLOYMENT_GUIDE.md
echo   - MIGRATION_DEPLOYMENT_STEPS.md
echo   - DEPLOYMENT_CHECKLIST.md
echo.
pause
