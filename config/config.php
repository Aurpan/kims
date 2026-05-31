<?php
// Application Configuration

define('APP_NAME', 'Jersey Store Inventory Management');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production
define('APP_URL', 'http://localhost:8000');

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
define('MAIL_FROM_NAME', 'Inventory System');
define('MAIL_FROM_EMAIL', 'noreply@jerseystore.com');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
