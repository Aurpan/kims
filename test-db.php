<?php
/**
 * Database Connection Test Script
 * Run from command line: php test-db.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Jersey Store Inventory Management - Database Test\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'inventory_mgmt',
    'port' => 3306
];

echo "[1/4] Checking PHP PDO Extension...\n";
if (!extension_loaded('pdo_mysql')) {
    echo "✗ FAILED: PDO MySQL extension not loaded\n";
    exit(1);
}
echo "✓ PDO MySQL extension loaded\n\n";

echo "[2/4] Testing Database Connection...\n";
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['database']
    );

    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✓ Connected to MySQL\n";
    echo "  Host: {$config['host']}\n";
    echo "  Database: {$config['database']}\n";
    echo "  User: {$config['user']}\n\n";

} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n\n";
    echo "Setup Instructions:\n";
    echo "1. Create MySQL database: inventory_mgmt\n";
    echo "2. Import schema: migrations/001_initial_schema.sql\n";
    echo "3. Update credentials in test-db.php if needed\n\n";
    exit(1);
}

echo "[3/4] Checking Database Tables...\n";
try {
    $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$config['database']}' ORDER BY TABLE_NAME")
        ->fetchAll();

    if (empty($tables)) {
        echo "✗ No tables found in database\n";
        echo "  Run: mysql < migrations/001_initial_schema.sql\n\n";
        exit(1);
    }

    echo "✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $row) {
        echo "  • " . $row['TABLE_NAME'] . "\n";
    }
    echo "\n";

} catch (PDOException $e) {
    echo "✗ Error checking tables: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "[4/4] Testing Table Data & Queries...\n\n";

try {
    // Check users table
    echo "  Users:\n";
    $users = $pdo->query("SELECT id, email, name, is_active FROM users")->fetchAll();
    if (count($users) > 0) {
        echo "    ✓ Total users: " . count($users) . "\n";
        foreach ($users as $user) {
            $status = $user['is_active'] ? '(active)' : '(inactive)';
            echo "      • {$user['name']} ({$user['email']}) $status\n";
        }
    } else {
        echo "    ⚠ No users found\n";
    }

    // Check products table
    echo "\n  Products:\n";
    $products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch();
    echo "    • Total products: " . $products['count'] . "\n";

    // Check variants table
    echo "\n  Product Variants:\n";
    $variants = $pdo->query("SELECT COUNT(*) as count FROM product_variants")->fetch();
    echo "    • Total variants: " . $variants['count'] . "\n";

    // Check orders table
    echo "\n  Orders:\n";
    $orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch();
    echo "    • Total orders: " . $orders['count'] . "\n";

    // Check order items table
    echo "\n  Order Items:\n";
    $items = $pdo->query("SELECT COUNT(*) as count FROM order_items")->fetch();
    echo "    • Total items: " . $items['count'] . "\n";

    // Check expenses table
    echo "\n  Expenses:\n";
    $expenses = $pdo->query("SELECT COUNT(*) as count FROM expenses")->fetch();
    echo "    • Total expenses: " . $expenses['count'] . "\n";

    // Check stock adjustments table
    echo "\n  Stock Adjustments:\n";
    $adjustments = $pdo->query("SELECT COUNT(*) as count FROM stock_adjustments")->fetch();
    echo "    • Total adjustments: " . $adjustments['count'] . "\n";

    // Database version
    echo "\n  Database Info:\n";
    $version = $pdo->query("SELECT VERSION() as version")->fetch();
    echo "    • MySQL Version: " . $version['version'] . "\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✓ All Tests Passed!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Next Steps:\n";
echo "1. Start the application: php -S localhost:8000 -t public/\n";
echo "2. Open browser: http://localhost:8000/auth/login\n";
echo "3. Login with: admin@jerseystore.com / admin123\n\n";

echo "Configuration Files:\n";
echo "• config/config.php - Application settings\n";
echo "• config/database.php - Database credentials\n";
echo "• config/.env.example - Environment variables template\n\n";

?>
