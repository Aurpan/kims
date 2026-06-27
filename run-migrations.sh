#!/bin/bash

# KIMS Database Migration Runner
# This script runs all migrations in the correct order

set -e

DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_NAME="${DB_NAME:-kims_db}"
DB_HOST="${DB_HOST:-localhost}"

echo "================================"
echo "KIMS Database Migration Runner"
echo "With Migration Tracking"
echo "================================"
echo ""
echo "Database: $DB_NAME"
echo "Host: $DB_HOST"
echo "User: $DB_USER"
echo ""
echo "Note: Each migration is tracked in the 'migrations' table"
echo "      Run 'php check-migrations.php' to view status"
echo ""

# Function to run a migration
run_migration() {
    local file=$1
    echo "Running migration: $file"
    if [ -z "$DB_PASSWORD" ]; then
        mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < "$file"
    else
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$file"
    fi
    echo "✓ Migration completed: $file"
    echo ""
}

# Run migrations in order
echo "Starting migrations..."
echo ""

run_migration "migrations/001_initial_schema.sql"
run_migration "migrations/002_all_updates.sql"

echo "================================"
echo "All migrations completed!"
echo "================================"
