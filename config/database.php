<?php
// Database Configuration
// Update these values with your cPanel credentials

$db_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'database' => $_ENV['DB_NAME'] ?? 'inventory_mgmt',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
];

// Build DSN for PDO
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $db_config['host'],
    $db_config['port'],
    $db_config['database'],
    $db_config['charset']
);

define('DB_DSN', $dsn);
define('DB_USER', $db_config['user']);
define('DB_PASSWORD', $db_config['password']);

// PDO Options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
