<?php
// Application Configuration

// Load config/.env into $_ENV if present (no external dependency)
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

define('APP_NAME', 'Kitzoholic Inventory Management');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'true') === 'true'); // Set to false in production
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_NAME', 'inventory_session');

// Pagination
define('ITEMS_PER_PAGE', 20);

// File Upload
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_UPLOAD_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', '../uploads/');

// Chart Configuration
define('CHART_LIBRARY', 'chart.js'); // or 'highcharts'

// Email Configuration (for password reset)
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Inventory System');
define('MAIL_FROM_EMAIL', $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@jerseystore.com');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? 'your-email@gmail.com');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? 'your-app-password');

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
