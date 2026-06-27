# KIMS Database Migration Runner (PowerShell)
# This script runs all migrations in the correct order

$DBUser = $env:DB_USER -or "root"
$DBPassword = $env:DB_PASSWORD
$DBName = $env:DB_NAME -or "kims_db"
$DBHost = $env:DB_HOST -or "localhost"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "KIMS Database Migration Runner" -ForegroundColor Cyan
Write-Host "With Migration Tracking" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Database: $DBName"
Write-Host "Host: $DBHost"
Write-Host "User: $DBUser"
Write-Host ""
Write-Host "Note: Each migration is tracked in the 'migrations' table"
Write-Host "      Run 'php check-migrations.php' to view status"
Write-Host ""

# Function to run a migration
function Run-Migration {
    param([string]$File)

    Write-Host "Running migration: $File" -ForegroundColor Yellow

    $sqlContent = Get-Content -Path $File -Raw

    if ($DBPassword) {
        $sqlContent | mysql -h $DBHost -u $DBUser -p$DBPassword $DBName
    } else {
        $sqlContent | mysql -h $DBHost -u $DBUser $DBName
    }

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Migration completed: $File" -ForegroundColor Green
    } else {
        Write-Host "✗ Migration failed: $File" -ForegroundColor Red
        exit 1
    }
    Write-Host ""
}

Write-Host "Starting migrations..." -ForegroundColor Green
Write-Host ""

# Run migrations in order
Run-Migration "migrations/001_initial_schema.sql"
Run-Migration "migrations/002_all_updates.sql"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "All migrations completed!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
