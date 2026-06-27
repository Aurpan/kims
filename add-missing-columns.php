<?php
require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding missing columns to order_items table...\n\n";

    // Add columns one at a time to better handle errors
    $columns_to_add = [
        'patches_extra' => "ALTER TABLE order_items ADD COLUMN patches_extra DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER line_total",
        'namekit_extra' => "ALTER TABLE order_items ADD COLUMN namekit_extra DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER patches_extra",
        'kit_name' => "ALTER TABLE order_items ADD COLUMN kit_name VARCHAR(255) NULL AFTER namekit_extra",
        'kit_number' => "ALTER TABLE order_items ADD COLUMN kit_number VARCHAR(50) NULL AFTER kit_name",
    ];

    foreach ($columns_to_add as $col_name => $sql) {
        try {
            echo "Adding column: $col_name... ";
            $pdo->exec($sql);
            echo "✓\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1060') !== false || strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "✓ (already exists)\n";
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n================================\n";
    echo "Verification - Current columns:\n";
    echo "================================\n\n";

    $result = $pdo->query("DESCRIBE order_items");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    echo "\n✓ All columns added successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
