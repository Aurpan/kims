<?php
// Check Migration Status
// View which migrations have been run and their status

require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Extract database name from DSN
    preg_match('/dbname=([^;]+)/', DB_DSN, $matches);
    $db_name = $matches[1] ?? 'inventory_mgmt';

    echo "================================\n";
    echo "Migration Status Report\n";
    echo "================================\n";
    echo "Database: " . $db_name . "\n\n";

    // Check if migrations table exists
    $result = $pdo->query(
        "SELECT COUNT(*) as count FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'migrations'"
    );
    $exists = $result->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if (!$exists) {
        echo "❌ No migrations table found!\n";
        echo "The migrations table hasn't been created yet.\n\n";
        echo "To initialize the database:\n";
        echo "  php migrate.php\n\n";
        exit(0);
    }

    // Get all migrations grouped by batch
    $result = $pdo->query(
        "SELECT migration, batch, executed_at, status, error_message
         FROM migrations
         ORDER BY batch DESC, id ASC"
    );
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "No migrations have been run yet.\n\n";
        echo "To run migrations:\n";
        echo "  php migrate.php\n\n";
        exit(0);
    }

    // Group by batch
    $batches = [];
    foreach ($rows as $row) {
        $batch = $row['batch'];
        if (!isset($batches[$batch])) {
            $batches[$batch] = [];
        }
        $batches[$batch][] = $row;
    }

    // Display by batch (newest first)
    $batchNumbers = array_keys($batches);
    rsort($batchNumbers);

    foreach ($batchNumbers as $batch) {
        $migrations = $batches[$batch];
        echo "Batch $batch:\n";
        echo "─────────────────────────────────────────────────────\n";

        foreach ($migrations as $mig) {
            $status = $mig['status'] === 'completed' ? '✓' : '✗';
            $time = date('Y-m-d H:i:s', strtotime($mig['executed_at']));

            echo "$status {$mig['migration']}\n";
            echo "  Executed: $time\n";

            if ($mig['status'] === 'failed' && $mig['error_message']) {
                echo "  Error: " . substr($mig['error_message'], 0, 100) . "...\n";
            }
        }

        echo "\n";
    }

    // Summary statistics
    $total = count($rows);
    $completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed'));
    $failed = $total - $completed;

    echo "================================\n";
    echo "Summary\n";
    echo "================================\n";
    echo "Total Migrations: $total\n";
    echo "Completed: $completed\n";
    echo "Failed: $failed\n";
    echo "Latest Batch: " . max($batchNumbers) . "\n\n";

    if ($failed > 0) {
        echo "⚠ Some migrations failed! Review errors above.\n\n";
    } else {
        echo "✓ All migrations completed successfully!\n\n";
    }

    echo "Commands:\n";
    echo "  php migrate.php          - Run pending migrations\n";
    echo "  php check-migrations.php - View this status report\n\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
