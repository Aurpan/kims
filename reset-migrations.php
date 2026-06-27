<?php
// Reset migrations - drops and recreates migrations tracking table
// Use this for development/testing to start fresh

require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "================================\n";
    echo "Reset Migrations Tracking Table\n";
    echo "================================\n\n";

    // Drop existing migrations table
    echo "Dropping old migrations table (if exists)... ";
    $pdo->exec("DROP TABLE IF EXISTS migrations");
    echo "✓\n";

    // Verify it's gone
    $result = $pdo->query(
        "SELECT COUNT(*) as count FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'migrations'"
    );
    $exists = $result->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if (!$exists) {
        echo "✓ Migrations table dropped successfully\n\n";
    } else {
        echo "✗ Failed to drop migrations table\n";
        exit(1);
    }

    echo "Now run: php migrate.php\n";
    echo "This will create a fresh migrations table and run all migrations.\n\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
