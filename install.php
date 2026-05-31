<?php
/**
 * Database Installation Script
 * Run this script in a browser after updating database credentials
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Installation</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; max-width: 600px; }
        .status-item { padding: 15px; margin-bottom: 10px; border-radius: 5px; display: flex; align-items: center; }
        .status-item i { margin-right: 15px; font-size: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Database Installation</h1>";

$success = true;
$status = [];

// Step 1: Check database credentials
echo "<h5 class='mt-4'>Step 1: Database Connection</h5>";

$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

// Try to connect
try {
    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s',
        $db_config['host'],
        $db_config['port'],
        $db_config['charset']
    );

    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "<div class='status-item success'>
        ✓ Connected to MySQL Server
        <br><small>Host: {$db_config['host']}:{$db_config['port']}</small>
    </div>";

    // Step 2: Create database
    echo "<h5 class='mt-4'>Step 2: Create Database</h5>";

    try {
        $pdo->exec('CREATE DATABASE IF NOT EXISTS inventory_mgmt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        echo "<div class='status-item success'>✓ Database created/exists: inventory_mgmt</div>";

        // Step 3: Select database and import schema
        echo "<h5 class='mt-4'>Step 3: Import Schema</h5>";

        $pdo->exec('USE inventory_mgmt');

        // Read schema file
        $schema_file = __DIR__ . '/migrations/001_initial_schema.sql';

        if (!file_exists($schema_file)) {
            throw new Exception("Schema file not found: $schema_file");
        }

        $schema = file_get_contents($schema_file);

        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));

        $table_count = 0;
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                // Skip USE statements and comments
                if (strpos(trim($statement), 'USE') === 0 || strpos(trim($statement), '--') === 0) {
                    continue;
                }

                try {
                    $pdo->exec($statement);
                    if (stripos($statement, 'CREATE TABLE') === 0) {
                        $table_count++;
                    }
                } catch (PDOException $e) {
                    // Ignore "already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }

        echo "<div class='status-item success'>✓ Schema imported successfully</div>";

        // Step 4: Verify tables
        echo "<h5 class='mt-4'>Step 4: Verify Tables</h5>";

        $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'inventory_mgmt'")
            ->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            echo "<div class='status-item success'>✓ Found " . count($tables) . " tables:</div>";
            echo "<div class='ms-4 mb-3'>";
            foreach ($tables as $table) {
                echo "<small>• $table</small><br>";
            }
            echo "</div>";
        } else {
            throw new Exception('No tables found in database');
        }

        // Step 5: Verify admin user
        echo "<h5 class='mt-4'>Step 5: Verify Admin User</h5>";

        $admin = $pdo->query("SELECT email, name FROM users WHERE email = 'admin@jerseystore.com'")
            ->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            echo "<div class='status-item success'>✓ Admin user exists</div>";
            echo "<div class='ms-4 mb-3'>";
            echo "<strong>Email:</strong> " . htmlspecialchars($admin['email']) . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($admin['name']) . "<br>";
            echo "<small class='text-muted'>Password: admin123</small>";
            echo "</div>";
        } else {
            echo "<div class='status-item warning'>⚠ Admin user not found</div>";
        }

        // Step 6: Test connection with app credentials
        echo "<h5 class='mt-4'>Step 6: Test Application Connection</h5>";

        try {
            require_once __DIR__ . '/config/database.php';

            // Use the credentials from config
            $test_pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, PDO_OPTIONS);
            $result = $test_pdo->query("SELECT COUNT(*) as count FROM users")->fetch();

            echo "<div class='status-item success'>✓ Application can connect to database</div>";
            echo "<div class='ms-4 mb-3'>";
            echo "Total users: " . $result['count'] . "<br>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='status-item error'>✗ Application connection failed</div>";
            echo "<div class='ms-4 mb-3'>";
            echo "<small>" . htmlspecialchars($e->getMessage()) . "</small><br>";
            echo "<small class='text-muted'>Update config/database.php with correct credentials</small>";
            echo "</div>";
            $success = false;
        }

    } catch (PDOException $e) {
        echo "<div class='status-item error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $success = false;
    }

} catch (PDOException $e) {
    echo "<div class='status-item error'>✗ Database Connection Failed</div>";
    echo "<div class='ms-4 mb-3'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Configuration:</strong><br>";
    echo "Host: {$db_config['host']}<br>";
    echo "Port: {$db_config['port']}<br>";
    echo "User: {$db_config['user']}<br>";
    echo "Password: " . (empty($db_config['password']) ? '(empty)' : '(set)') . "<br><br>";
    echo "<small class='text-muted'>Update the credentials in this script or in config/database.php</small>";
    echo "</div>";
    $success = false;
}

// Final status
echo "<h5 class='mt-5'>";
if ($success) {
    echo "<div class='alert alert-success'>✓ Installation Successful!</div>";
    echo "<p>Your database is ready. You can now:</p>";
    echo "<ol>";
    echo "<li>Test the application by accessing <a href='/auth/login'>/auth/login</a></li>";
    echo "<li>Login with credentials:<br><strong>Email:</strong> admin@jerseystore.com<br><strong>Password:</strong> admin123</li>";
    echo "<li>Change the admin password immediately in production</li>";
    echo "</ol>";
} else {
    echo "<div class='alert alert-danger'>✗ Installation Failed</div>";
    echo "<p>Please fix the errors above and run this script again.</p>";
}
echo "</h5>";

echo "
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
