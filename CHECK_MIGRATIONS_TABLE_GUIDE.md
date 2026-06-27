# How to Check if Migrations Table Exists

There are several ways to verify if the migrations table has been created locally.

---

## Method 1: PHP Script (Easiest) ✅

Run the quick check script:

```bash
php check-migrations-table.php
```

**Output if table EXISTS:**
```
✓ Migrations table EXISTS

Table Structure:
─────────────────────────────────────
• id: int(11) (NOT NULL)
• migration: varchar(255) (NOT NULL)
• batch: int(11) (NOT NULL)
• executed_at: timestamp (NOT NULL)
• status: enum('completed','failed') (nullable)
• error_message: text (nullable)

Records:
─────────────────────────────────────
Total migrations recorded: 2

Migration History:
✓ 001_initial_schema.sql (Batch 1) - 2026-06-28 03:47:15
✓ 002_all_updates.sql (Batch 1) - 2026-06-28 03:47:15

✓ Database is ready! Migrations table is set up.
```

**Output if table DOES NOT EXIST:**
```
✗ Migrations table DOES NOT EXIST

The migrations table hasn't been created yet.
This happens when:
  • Database hasn't been initialized
  • migrate.php hasn't been run yet

To create it and run migrations:
  php migrate.php
```

---

## Method 2: Using check-migrations.php

```bash
php check-migrations.php
```

**If table exists:** Shows full migration history

**If table doesn't exist:** Shows helpful message
```
No migrations table found!
The migrations table hasn't been created yet.

To initialize the database:
  php migrate.php
```

---

## Method 3: Direct MySQL Query

### Via Command Line

```bash
mysql -u root -p inventory_mgmt -e "SHOW TABLES LIKE 'migrations';"
```

**If table exists:**
```
+---------------------------+
| Tables_in_inventory_mgmt  |
+---------------------------+
| migrations                |
+---------------------------+
```

**If table doesn't exist:**
```
Empty set
```

### Check Table Details

```bash
mysql -u root -p inventory_mgmt -e "DESCRIBE migrations;"
```

**Output:**
```
+---------------+----------------------+------+-----+---------+----------------+
| Field         | Type                 | Null | Key | Default | Extra          |
+---------------+----------------------+------+-----+---------+----------------+
| id            | int(11)              | NO   | PRI | NULL    | auto_increment |
| migration     | varchar(255)         | NO   | UNI | NULL    |                |
| batch         | int(11)              | NO   | MUL | NULL    |                |
| executed_at   | timestamp            | NO   |     | CURRENT | TIMESTAMP      |
| status        | enum('completed'...) | YES  |     | NULL    |                |
| error_message | text                 | YES  |     | NULL    |                |
+---------------+----------------------+------+-----+---------+----------------+
```

### Count Migration Records

```bash
mysql -u root -p inventory_mgmt -e "SELECT COUNT(*) as total_migrations FROM migrations;"
```

**Output:**
```
+-------------------+
| total_migrations  |
+-------------------+
| 2                 |
+-------------------+
```

---

## Method 4: phpMyAdmin (If Available Locally)

1. **Open phpMyAdmin**
   - Usually: `http://localhost/phpmyadmin`

2. **Select your database**
   - Click: `inventory_mgmt` (or your database name)

3. **Look for "migrations" table**
   - In the left sidebar under Tables
   - Or scroll through the Tables list

4. **Check table structure**
   - Click on `migrations` table
   - Tab: **Structure** shows columns
   - Tab: **Data** shows migration records

---

## Method 5: Simple PHP Check

Create a quick test file `test-migrations.php`:

```php
<?php
require 'config/database.php';

$pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);

$result = $pdo->query(
    "SELECT COUNT(*) as count FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'migrations'"
);

$exists = $result->fetch(PDO::FETCH_ASSOC)['count'] > 0;

if ($exists) {
    echo "✓ Migrations table exists\n";
} else {
    echo "✗ Migrations table does NOT exist\n";
}
```

Run it:
```bash
php test-migrations.php
```

---

## Method 6: View All Table Names

### Via bash/MySQL

```bash
mysql -u root -p inventory_mgmt -e "SHOW TABLES;"
```

**Output:**
```
+----------------------------+
| Tables_in_inventory_mgmt   |
+----------------------------+
| expenses                   |
| migrations                 | ← This one!
| order_items                |
| orders                     |
| password_reset_tokens      |
| products                   |
| product_variants           |
| stock_adjustments          |
| users                      |
+----------------------------+
```

---

## Quick Reference

| Method | Command | Ease |
|--------|---------|------|
| **PHP Script** | `php check-migrations-table.php` | ⭐⭐⭐⭐⭐ |
| **Migration Check** | `php check-migrations.php` | ⭐⭐⭐⭐⭐ |
| **MySQL CLI** | `mysql -u root -p DB -e "SHOW TABLES LIKE 'migrations';"` | ⭐⭐⭐ |
| **phpMyAdmin** | Browser GUI | ⭐⭐⭐⭐ |
| **Simple PHP** | `php test-migrations.php` | ⭐⭐⭐⭐ |

---

## Troubleshooting

### Scenario 1: Table Doesn't Exist

**Signs:**
- Script says: "✗ Migrations table DOES NOT EXIST"
- MySQL returns: "Empty set"
- phpMyAdmin shows no migrations table

**Solution:**
```bash
php migrate.php
```

This will:
1. Create the migrations table
2. Run all pending migrations
3. Record them in the tracking table

### Scenario 2: Table Exists but Empty

**Signs:**
- Table exists but shows: "Total migrations recorded: 0"
- Or: "Table exists but no migrations recorded yet"

**Meaning:** Database is created but migrations haven't run yet

**Solution:**
```bash
php migrate.php
```

### Scenario 3: Connection Error

**Error:**
```
❌ Database Connection Error!
Error: SQLSTATE[HY000]: General error: 1030 Got error...
```

**Causes:**
- Database doesn't exist
- Database credentials wrong
- Database user doesn't have permissions

**Solution:**
1. Check `config/.env` for correct credentials
2. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
3. Verify user permissions: `mysql -u root -p -e "SHOW GRANTS FOR 'user'@'localhost';"`

---

## Step-by-Step Check Process

### 1. Quick Status Check

```bash
php check-migrations-table.php
```

**Tells you:**
- ✓ Does migrations table exist?
- ✓ How many migrations are recorded?
- ✓ What migrations have run?

### 2. Full Status Report

```bash
php check-migrations.php
```

**Tells you:**
- ✓ Complete migration history
- ✓ By batch number
- ✓ With timestamps
- ✓ Any errors

### 3. Database Verification

```bash
mysql -u root -p inventory_mgmt -e "SHOW TABLES;"
```

**Tells you:**
- ✓ All tables in database
- ✓ Confirms migrations table exists

---

## What the Migrations Table Contains

If it exists, it tracks:

```
Column              | Contains
────────────────────┼────────────────────────────
id                  | Auto-incrementing ID
migration           | Filename (e.g., 001_initial_schema.sql)
batch               | Batch number when it ran (1, 2, 3, etc.)
executed_at         | Timestamp (2026-06-28 03:47:15)
status              | 'completed' or 'failed'
error_message       | Error details if status is 'failed'
```

---

## Common Answers

### Q: I just installed KIMS, how do I know if migrations ran?

**A:** Run:
```bash
php check-migrations-table.php
```

### Q: How do I know if the database is initialized?

**A:** If migrations table exists and has records, the database is initialized.

### Q: What should I see after running migrate.php?

**A:** Run:
```bash
php check-migrations.php
```

You should see all migrations marked as ✓ completed.

### Q: Why does the table not exist?

**A:** It doesn't exist until `php migrate.php` is run for the first time.

### Q: Can I safely delete the migrations table?

**A:** Not recommended. It tracks what's been applied. If deleted:
1. `php migrate.php` will recreate it
2. Migrations will run again
3. May cause errors if already applied

---

## Summary

**To check if migrations table exists locally:**

**Best method:**
```bash
php check-migrations-table.php
```

**Full report:**
```bash
php check-migrations.php
```

**Via MySQL:**
```bash
mysql -u root -p inventory_mgmt -e "SHOW TABLES LIKE 'migrations';"
```

**The table is created and populated when you run:**
```bash
php migrate.php
```

That's it! You now know all the ways to check the migrations table! ✅
