<?php
// Quick Check: Does the migrations table exist?

require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "================================\n";
    echo "Migration Table Status Check\n";
    echo "================================\n\n";

    // Extract database name from DSN
    preg_match('/dbname=([^;]+)/', DB_DSN, $matches);
    $db_name = $matches[1] ?? 'inventory_mgmt';

    echo "Database: $db_name\n\n";

    // Check if migrations table exists
    $result = $pdo->query(
        "SELECT COUNT(*) as count FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'migrations'"
    );
    $exists = $result->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if ($exists) {
        echo "✓ Migrations table EXISTS\n\n";

        // Get table structure
        echo "Table Structure:\n";
        echo "─────────────────────────────────────\n";
        $result = $pdo->query("DESCRIBE migrations");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $col) {
            $nullable = $col['Null'] === 'NO' ? 'NOT NULL' : 'nullable';
            echo "• {$col['Field']}: {$col['Type']} ($nullable)\n";
        }

        // Count records
        echo "\nRecords:\n";
        echo "─────────────────────────────────────\n";
        $result = $pdo->query("SELECT COUNT(*) as count FROM migrations");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total migrations recorded: $count\n\n";

        if ($count > 0) {
            echo "Migration History:\n";
            echo "─────────────────────────────────────\n";
            $result = $pdo->query(
                "SELECT migration, batch, executed_at, status
                 FROM migrations
                 ORDER BY batch DESC, id ASC"
            );
            $records = $result->fetchAll(PDO::FETCH_ASSOC);

            foreach ($records as $rec) {
                $status = $rec['status'] === 'completed' ? '✓' : '✗';
                echo "$status {$rec['migration']} (Batch {$rec['batch']}) - {$rec['executed_at']}\n";
            }
        } else {
            echo "⚠ Table exists but no migrations recorded yet.\n";
        }

        echo "\n✓ Database is ready! Migrations table is set up.\n";

    } else {
        echo "✗ Migrations table DOES NOT EXIST\n\n";
        echo "The migrations table hasn't been created yet.\n";
        echo "This happens when:\n";
        echo "  • Database hasn't been initialized\n";
        echo "  • migrate.php hasn't been run yet\n\n";
        echo "To create it and run migrations:\n";
        echo "  php migrate.php\n";
    }

    echo "\n";

} catch (PDOException $e) {
    echo "❌ Database Connection Error!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nCheck your database credentials in config/.env\n";
    exit(1);
}
?>
