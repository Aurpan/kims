<?php
require 'config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Order Items Table Structure:\n";
    echo "============================\n\n";

    $result = $pdo->query("DESCRIBE order_items");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
    }

    echo "\n\nChecking for patches_extra:\n";
    $has_patches = false;
    $has_namekit = false;
    $has_kit_name = false;
    $has_kit_number = false;

    foreach ($columns as $col) {
        if ($col['Field'] === 'patches_extra') $has_patches = true;
        if ($col['Field'] === 'namekit_extra') $has_namekit = true;
        if ($col['Field'] === 'kit_name') $has_kit_name = true;
        if ($col['Field'] === 'kit_number') $has_kit_number = true;
    }

    echo "patches_extra: " . ($has_patches ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "namekit_extra: " . ($has_namekit ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "kit_name: " . ($has_kit_name ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "kit_number: " . ($has_kit_number ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "stock_deducted: " . (in_array('stock_deducted', array_column($columns, 'Field')) ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "is_return: " . (in_array('is_return', array_column($columns, 'Field')) ? "✓ EXISTS" : "✗ MISSING") . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
