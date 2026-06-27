# How the Server Tracks Migrations

## Current System (No Tracking)

Currently, KIMS migrations **DO NOT have a tracking system**. Instead, they rely on SQL safeguards:

```sql
CREATE TABLE IF NOT EXISTS users (...)
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method ENUM(...)
ALTER TABLE products DROP COLUMN IF EXISTS color
```

### How It Works:

1. **CREATE TABLE IF NOT EXISTS**
   - Won't create table if it already exists
   - Safe to run multiple times

2. **ADD COLUMN IF NOT EXISTS**
   - Won't add column if it already exists
   - Prevents "Duplicate column name" errors

3. **DROP COLUMN IF EXISTS**
   - Won't drop column if it doesn't exist
   - Prevents "Unknown column" errors

### ⚠️ Problems with This Approach:

1. **No history tracking** - You don't know which migrations ran
2. **Can't rollback** - No way to undo migrations
3. **Silent failures** - Errors might be hidden
4. **Difficult debugging** - Can't trace what changed when
5. **Team coordination** - Multiple developers can't coordinate
6. **Production safety** - Can't verify migration state

---

## Better Approach: Migrations Tracking Table

### Create a Migrations Table

Add this to track which migrations have run:

```sql
CREATE TABLE IF NOT EXISTS migrations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  migration VARCHAR(255) UNIQUE NOT NULL,
  batch INT NOT NULL,
  executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','completed','failed') DEFAULT 'completed',
  error_message TEXT NULL,
  INDEX idx_migration (migration),
  INDEX idx_batch (batch),
  INDEX idx_executed_at (executed_at)
);
```

---

## Implementation: Updated Migration Runner

Here's an improved migration system with tracking:

### Step 1: Create Enhanced migrate.php

```php
<?php
// Enhanced migration runner with tracking

require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "================================\n";
    echo "KIMS Database Migration Runner\n";
    echo "================================\n\n";

    // Create migrations tracking table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
          id INT PRIMARY KEY AUTO_INCREMENT,
          migration VARCHAR(255) UNIQUE NOT NULL,
          batch INT NOT NULL,
          executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status ENUM('completed','failed') DEFAULT 'completed',
          error_message TEXT NULL,
          INDEX idx_migration (migration),
          INDEX idx_batch (batch)
        )
    ");

    // List of migrations to run (in order)
    $migrations = [
        'migrations/001_initial_schema.sql',
        'migrations/002_all_updates.sql',
    ];

    // Get current batch number
    $result = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $currentBatch = ($row['max_batch'] ?? 0) + 1;

    $successCount = 0;
    $skipCount = 0;
    $failureCount = 0;

    echo "Starting migrations (Batch: $currentBatch)...\n\n";

    foreach ($migrations as $migration) {
        if (!file_exists($migration)) {
            echo "⚠ Skipping (not found): $migration\n";
            continue;
        }

        $migrationName = basename($migration);

        // Check if already run
        $stmt = $pdo->prepare("SELECT * FROM migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo "⏭ Skipping (already run): $migrationName\n";
            $skipCount++;
            continue;
        }

        echo "Running: $migrationName... ";

        try {
            $sql = file_get_contents($migration);
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($s) => !empty($s) && !str_starts_with($s, '--')
            );

            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            // Record successful migration
            $stmt = $pdo->prepare(
                "INSERT INTO migrations (migration, batch, status) 
                 VALUES (?, ?, 'completed')"
            );
            $stmt->execute([$migrationName, $currentBatch]);

            echo "✓\n";
            $successCount++;

        } catch (PDOException $e) {
            echo "✗\n";
            echo "  Error: " . $e->getMessage() . "\n\n";

            // Record failed migration
            $stmt = $pdo->prepare(
                "INSERT INTO migrations (migration, batch, status, error_message) 
                 VALUES (?, ?, 'failed', ?)"
            );
            $stmt->execute([$migrationName, $currentBatch, $e->getMessage()]);

            $failureCount++;
        }
    }

    echo "\n================================\n";
    echo "Migration Summary\n";
    echo "================================\n";
    echo "Batch Number: $currentBatch\n";
    echo "Successful: $successCount\n";
    echo "Skipped: $skipCount\n";
    echo "Failed: $failureCount\n";

    // Show migration history
    echo "\nMigration History:\n";
    $result = $pdo->query(
        "SELECT migration, batch, executed_at, status 
         FROM migrations 
         ORDER BY batch DESC, id DESC 
         LIMIT 10"
    );
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $status = $row['status'] === 'completed' ? '✓' : '✗';
        echo "  $status {$row['migration']} (batch {$row['batch']})\n";
    }

    echo "\n✓ All migrations processed!\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
```

### Step 2: Check Migration Status

Create a new file: `check-migrations.php`

```php
<?php
require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Migration Status Report\n";
    echo "=======================\n\n";

    // Check if migrations table exists
    $result = $pdo->query(
        "SELECT COUNT(*) as count FROM information_schema.tables 
         WHERE table_schema = DATABASE() AND table_name = 'migrations'"
    );
    $exists = $result->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if (!$exists) {
        echo "❌ No migrations table found!\n";
        echo "Run php migrate.php to initialize.\n";
        exit;
    }

    // Show all migrations
    $result = $pdo->query(
        "SELECT migration, batch, executed_at, status 
         FROM migrations 
         ORDER BY batch DESC, id ASC"
    );
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "No migrations have been run yet.\n";
        exit;
    }

    $batches = [];
    foreach ($rows as $row) {
        $batch = $row['batch'];
        if (!isset($batches[$batch])) {
            $batches[$batch] = [];
        }
        $batches[$batch][] = $row;
    }

    foreach ($batches as $batch => $migrations) {
        echo "Batch $batch:\n";
        foreach ($migrations as $mig) {
            $status = $mig['status'] === 'completed' ? '✓' : '✗';
            echo "  $status {$mig['migration']} ({$mig['executed_at']})\n";
        }
        echo "\n";
    }

    $total = count($rows);
    $completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed'));
    echo "Summary: $completed/$total migrations completed\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
```

---

## Comparison: Old vs New System

### Old System (Current KIMS)
```
✗ No tracking table
✗ Can't verify which migrations ran
✗ Can't detect failed migrations
✗ No history
✗ No rollback capability
✓ Simple - just SQL IF EXISTS
✓ Works for small projects
```

### New System (Recommended)
```
✓ Tracks every migration
✓ Knows which migrations ran
✓ Detects and records failures
✓ Full history with timestamps
✓ Batch grouping for coordinated changes
✗ Requires tracking table
✗ Slightly more complex
✓ Professional-grade
✓ Scales for teams
```

---

## Query Examples

### Check if a specific migration ran:
```sql
SELECT * FROM migrations WHERE migration = '001_initial_schema.sql';
```

### See all migrations in a batch:
```sql
SELECT * FROM migrations WHERE batch = 1 ORDER BY id;
```

### See latest migrations:
```sql
SELECT * FROM migrations ORDER BY executed_at DESC LIMIT 5;
```

### Find failed migrations:
```sql
SELECT * FROM migrations WHERE status = 'failed';
```

### Count by status:
```sql
SELECT status, COUNT(*) as count FROM migrations GROUP BY status;
```

---

## Deployment Scenarios

### Scenario 1: First Deployment
```
migrations table created
batch 1 executed
All migrations tracked and recorded
```

### Scenario 2: Second Deployment (same server)
```
batch 2 starts
Only NEW migrations run
Already-run migrations are skipped
No duplicates
```

### Scenario 3: Multiple Servers
```
Server A: batch 1 ✓
Server B: batch 1 ✓ (they ran independently)
Each knows exactly what's been applied
```

---

## Recommendation

### For Current KIMS Setup:

**Use the enhanced migration system** if you:
- ✓ Plan to deploy to multiple servers
- ✓ Want to track what's been applied
- ✓ Need professional-grade deployment
- ✓ Have a team working on the project

**Stay with current IF EXISTS system** if you:
- ✓ Only deploying to one server
- ✓ Want simplicity
- ✓ Have minimal team coordination needs
- ✓ Don't need historical tracking

---

## Summary Table

| Feature | Current | Enhanced |
|---------|---------|----------|
| Tracks runs | ✗ | ✓ |
| Prevents duplicates | ✓ (SQL) | ✓ (DB) |
| Shows history | ✗ | ✓ |
| Detects failures | ✗ | ✓ |
| Multi-server safe | ⚠ | ✓ |
| Complexity | Low | Medium |
| Professional | No | Yes |

---

## Next Steps

1. **For now:** Use current system with IF EXISTS (it works!)
2. **When ready:** Implement enhanced tracking system
3. **Eventually:** Add rollback capability for production safety

This gives you a clear migration history and prevents accidental re-runs!
