<?php
// Enhanced KIMS Database Migration Runner with Tracking
// Tracks which migrations have been run and prevents duplicates

require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "================================\n";
    echo "KIMS Migration Runner\n";
    echo "================================\n\n";

    // Extract database name from DSN
    preg_match('/dbname=([^;]+)/', DB_DSN, $matches);
    $db_name = $matches[1] ?? 'inventory_mgmt';

    echo "Database: " . $db_name . "\n";
    echo "Status: Connected ✓\n\n";

    // ============================================
    // Create migrations tracking table
    // ============================================
    echo "Setting up migration tracking...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
          id INT PRIMARY KEY AUTO_INCREMENT,
          migration VARCHAR(255) UNIQUE NOT NULL,
          batch INT NOT NULL,
          executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status ENUM('completed','failed') DEFAULT 'completed',
          error_message TEXT NULL,
          INDEX idx_migration (migration),
          INDEX idx_batch (batch),
          INDEX idx_executed_at (executed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Migrations tracking table ready\n\n";

    // ============================================
    // List of migrations to run (in order)
    // ============================================
    $migrations = [
        'migrations/001_initial_schema.sql',
        'migrations/002_all_updates.sql',
    ];

    // ============================================
    // Get current batch number
    // ============================================
    $result = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $currentBatch = ($row['max_batch'] ?? 0) + 1;

    echo "Batch Number: $currentBatch\n";
    echo "Starting migrations...\n\n";

    $successCount = 0;
    $skipCount = 0;
    $failureCount = 0;

    // ============================================
    // Run each migration
    // ============================================
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
            $status = $existing['status'] === 'completed' ? '✓' : '✗';
            echo "⏭ Skipping (batch {$existing['batch']}): $migrationName $status\n";
            $skipCount++;
            continue;
        }

        echo "Running: $migrationName... ";

        try {
            $sql = file_get_contents($migration);

            // Split by semicolon and filter out comments
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($s) {
                    return !empty($s) && !str_starts_with(trim($s), '--');
                }
            );

            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $pdo->exec($statement);
                }
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
            echo "  Error: " . $e->getMessage() . "\n";

            // Record failed migration
            $stmt = $pdo->prepare(
                "INSERT INTO migrations (migration, batch, status, error_message)
                 VALUES (?, ?, 'failed', ?)"
            );
            $stmt->execute([$migrationName, $currentBatch, $e->getMessage()]);

            $failureCount++;
        }
    }

    // ============================================
    // Summary
    // ============================================
    echo "\n================================\n";
    echo "Migration Summary\n";
    echo "================================\n";
    echo "Batch Number: $currentBatch\n";
    echo "Successful: $successCount\n";
    echo "Skipped: $skipCount\n";
    echo "Failed: $failureCount\n";

    // ============================================
    // Show recent migration history
    // ============================================
    echo "\nRecent Migration History:\n";
    $result = $pdo->query(
        "SELECT migration, batch, executed_at, status
         FROM migrations
         ORDER BY batch DESC, id DESC
         LIMIT 20"
    );
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        echo "-----------------------------------\n";
        foreach ($rows as $row) {
            $status = $row['status'] === 'completed' ? '✓' : '✗';
            $time = date('Y-m-d H:i', strtotime($row['executed_at']));
            echo "$status Batch {$row['batch']} | $time | {$row['migration']}\n";
        }
        echo "-----------------------------------\n";
    }

    // ============================================
    // Final status
    // ============================================
    echo "\n================================\n";
    if ($failureCount === 0) {
        echo "✓ All migrations processed successfully!\n";
    } else {
        echo "⚠ Some migrations had errors. Check details above.\n";
    }
    echo "================================\n\n";

    // Show how to check status
    echo "To check migration status:\n";
    echo "  php check-migrations.php\n\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
