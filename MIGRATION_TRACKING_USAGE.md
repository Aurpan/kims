# Enhanced Migration Tracking - Usage Guide

## Overview

The KIMS database now has professional-grade migration tracking. Every migration is recorded in a `migrations` table with:
- ✓ Migration filename
- ✓ Batch number
- ✓ Execution timestamp
- ✓ Success/Failure status
- ✓ Error messages (if failed)

---

## Quick Start

### 1. Run Migrations (First Time)

```bash
php migrate.php
```

**Output:**
```
================================
KIMS Migration Runner
With Migration Tracking
================================

Database: inventory_mgmt
Status: Connected ✓

Setting up migration tracking...
✓ Migrations tracking table ready

Batch Number: 1
Starting migrations...

Running: 001_initial_schema.sql... ✓
Running: 002_all_updates.sql... ✓

================================
Migration Summary
================================
Batch Number: 1
Successful: 2
Skipped: 0
Failed: 0

Recent Migration History:
-----------------------------------
✓ Batch 1 | 2026-06-28 10:00 | 002_all_updates.sql
✓ Batch 1 | 2026-06-28 09:59 | 001_initial_schema.sql
-----------------------------------

================================
✓ All migrations processed successfully!
================================

To check migration status:
  php check-migrations.php
```

### 2. Check Migration Status

```bash
php check-migrations.php
```

**Output:**
```
================================
Migration Status Report
================================
Database: inventory_mgmt

Batch 1:
─────────────────────────────────────────────────────
✓ 001_initial_schema.sql
  Executed: 2026-06-28 09:59:42
✓ 002_all_updates.sql
  Executed: 2026-06-28 10:00:15

================================
Summary
================================
Total Migrations: 2
Completed: 2
Failed: 0
Latest Batch: 1

✓ All migrations completed successfully!

Commands:
  php migrate.php          - Run pending migrations
  php check-migrations.php - View this status report
```

---

## Scenarios

### Scenario 1: Running Migrations Again

When you run `php migrate.php` a second time:

```
Running: 001_initial_schema.sql... ⏭ (already run in batch 1)
Running: 002_all_updates.sql... ⏭ (already run in batch 1)

Batch Number: 2
Successful: 0
Skipped: 2
Failed: 0
```

**Key point:** Already-run migrations are skipped. No duplicates!

### Scenario 2: Adding New Migrations

If you add a new migration file `migrations/003_new_feature.sql`:

1. Run `php migrate.php`
2. New migration runs in a new batch (Batch 2)
3. Old migrations are skipped (they're in Batch 1)

```
Running: 001_initial_schema.sql... ⏭ (batch 1)
Running: 002_all_updates.sql... ⏭ (batch 1)
Running: 003_new_feature.sql... ✓ (batch 2 - NEW!)

Batch Number: 2
Successful: 1
Skipped: 2
Failed: 0
```

### Scenario 3: Multiple Servers

```
Server A:
  Batch 1: Migrations 1 & 2 ✓
  Check status: php check-migrations.php

Server B:
  Batch 1: Migrations 1 & 2 ✓
  Check status: php check-migrations.php

Both servers track independently
No conflicts - each knows exactly what ran
```

---

## Database Queries

### View All Migrations

```sql
SELECT * FROM migrations ORDER BY batch DESC, id DESC;
```

### Check Latest Batch

```sql
SELECT MAX(batch) as latest_batch FROM migrations;
```

### Find Failed Migrations

```sql
SELECT * FROM migrations WHERE status = 'failed';
```

### See Migrations in Specific Batch

```sql
SELECT * FROM migrations WHERE batch = 1;
```

### Get Migration History (Last 10)

```sql
SELECT migration, batch, executed_at, status
FROM migrations
ORDER BY executed_at DESC
LIMIT 10;
```

### Count by Status

```sql
SELECT status, COUNT(*) as count FROM migrations GROUP BY status;
```

---

## Migration Tracking Table Structure

```sql
CREATE TABLE migrations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  migration VARCHAR(255) UNIQUE NOT NULL,    -- Filename
  batch INT NOT NULL,                        -- Batch number
  executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('completed','failed'),         -- Success/Failure
  error_message TEXT NULL,                   -- Error details
  INDEX idx_migration (migration),
  INDEX idx_batch (batch),
  INDEX idx_executed_at (executed_at)
);
```

---

## Troubleshooting

### Issue: "Duplicate entry for migration"

**Cause:** Migration already exists in tracking table

**Solution:** Check status with `php check-migrations.php` - the migration probably already ran

### Issue: Migration shows status 'failed'

**Cause:** Error occurred during migration

**Solution:** Check error_message field:
```sql
SELECT migration, error_message FROM migrations WHERE status = 'failed';
```

### Issue: Missing migrations table

**Cause:** Database hasn't been initialized

**Solution:** Run `php migrate.php` to create tracking table and run migrations

---

## cPanel/Hosting Deployment

### Method 1: Browser

1. Upload `migrate.php` to domain root
2. Visit `https://yourdomain.com/migrate.php`
3. View tracking at `https://yourdomain.com/check-migrations.php`

### Method 2: SSH

```bash
php migrate.php
php check-migrations.php
```

### Method 3: cPanel Terminal

```bash
cd public_html
php migrate.php
php check-migrations.php
```

---

## Best Practices

### ✓ DO

- ✓ Run `php migrate.php` every deployment
- ✓ Check status with `php check-migrations.php`
- ✓ Keep migration files in `migrations/` folder
- ✓ Always backup database before migrations
- ✓ Test migrations on staging first

### ✗ DON'T

- ✗ Manually run SQL migration files
- ✗ Delete migration files after running
- ✗ Run migrations multiple times manually
- ✗ Modify tracking table directly
- ✗ Rename migration files

---

## Migration Files to Keep

Keep these in your repository:
```
✓ migrations/001_initial_schema.sql
✓ migrations/002_all_updates.sql
✓ migrate.php
✓ check-migrations.php
```

You can safely delete (these are consolidated):
```
✗ migrations/002_feature_updates.sql
✗ migrations/002_order_delivery_timestamps.sql
✗ migrations/003_sourcing_price.sql
✗ migrations/003_order_soft_delete.sql
✗ migrations/004_remove_color_reorder_default.sql
✗ migrations/004_exchange_orders.sql
✗ migrations/005_order_stock_issue.sql
✗ migrations/006_delivery_status_package_ready.sql
✗ migrations/007_drop_status_column.sql
✗ migrations/008_waiting_for_print_status.sql
```

---

## Commands Summary

| Command | Purpose |
|---------|---------|
| `php migrate.php` | Run pending migrations, create tracking table |
| `php check-migrations.php` | View migration status and history |
| `php migrate.php > migration.log` | Log migration output to file |

---

## Example Workflow

### Initial Setup (Day 1)

```bash
# 1. Run migrations
php migrate.php

# Output shows 2 migrations completed in Batch 1

# 2. Check status
php check-migrations.php

# Shows: Batch 1 with both migrations ✓
```

### Second Deployment (Week 1)

```bash
# 1. Run migrations
php migrate.php

# Output shows both migrations skipped (already run)

# 2. Check status
php check-migrations.php

# Shows: Batch 1 unchanged
```

### Third Deployment with New Migration (Week 2)

```bash
# 1. Add new migration file: 003_new_feature.sql

# 2. Run migrations
php migrate.php

# Output shows:
# - Migrations 1 & 2 skipped (Batch 1)
# - Migration 3 runs (Batch 2) ✓

# 3. Check status
php check-migrations.php

# Shows:
# Batch 2: 003_new_feature.sql ✓
# Batch 1: 001 & 002 ✓
```

---

## Benefits

✓ **Safety** - Know exactly what ran and when  
✓ **Reliability** - Prevent duplicate runs  
✓ **Traceability** - Full migration history  
✓ **Teams** - Coordinate across developers  
✓ **Production** - Professional-grade tracking  
✓ **Debugging** - See errors immediately  

---

That's it! Your database migrations are now professionally tracked! 🎉
