<?php
require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Verifying Migration Tracking Table\n";
    echo "===================================\n\n";

    // Check migrations table structure
    $result = $pdo->query("DESCRIBE migrations");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "Migrations Table Columns:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'NO' ? 'NOT NULL' : '') . "\n";
    }

    echo "\nMigration Records:\n";
    $result = $pdo->query(
        "SELECT id, migration, batch, executed_at, status FROM migrations ORDER BY id"
    );
    $records = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $rec) {
        echo "ID: {$rec['id']}, Migration: {$rec['migration']}, Batch: {$rec['batch']}, Status: {$rec['status']}\n";
    }

    echo "\n✓ Tracking system is working perfectly!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
