<?php
// Application Entry Point
session_start();

// Load configuration
require_once '../config/config.php';

define('PUBLIC_PATH', __DIR__);

// Load core classes with autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize database connection
use App\Core\Database;
use App\Core\Router;
use App\Core\Auth;

$db = Database::getInstance();

// Check session timeout if logged in
if (Auth::isLoggedIn()) {
    Auth::checkSessionTimeout();
}

// Initialize router
$router = new Router();

// Define routes
// Auth routes
$router->get('auth/login', 'AuthController@login');
$router->post('auth/login', 'AuthController@handleLogin');
$router->get('auth/register', 'AuthController@register');
$router->post('auth/register', 'AuthController@handleRegister');
$router->get('auth/logout', 'AuthController@logout');
$router->get('auth/forgot-password', 'AuthController@forgotPassword');
$router->post('auth/forgot-password', 'AuthController@handleForgotPassword');
$router->get('auth/reset-password/{token}', 'AuthController@resetPassword');
$router->post('auth/reset-password', 'AuthController@handleResetPassword');

// Dashboard routes
$router->get('', 'DashboardController@index');
$router->get('dashboard', 'DashboardController@index');

// Product routes
$router->get('products', 'ProductController@list');
$router->get('products/create', 'ProductController@create');
$router->post('products', 'ProductController@store');
$router->get('products/edit/{id}', 'ProductController@edit');
$router->post('products/update/{id}', 'ProductController@update');
$router->post('products/delete/{id}', 'ProductController@delete');
$router->get('products/{id}', 'ProductController@show');
$router->get('products/{id}/variants', 'ProductController@variants');
$router->post('products/{id}/variants', 'ProductController@storeVariant');
$router->post('products/variants/{variantId}/delete', 'ProductController@deleteVariant');
$router->post('products/variants/{variantId}/updateStock', 'ProductController@updateStock');

// Order routes
$router->get('orders', 'OrderController@list');
$router->get('orders/create', 'OrderController@create');
$router->post('orders', 'OrderController@store');
$router->get('orders/{id}', 'OrderController@show');
$router->get('orders/edit/{id}', 'OrderController@edit');
$router->post('orders/update/{id}', 'OrderController@update');
$router->post('orders/{id}/status', 'OrderController@updateStatus');
$router->post('orders/{id}/delete', 'OrderController@delete');
$router->post('orders/{id}/adjustStock', 'OrderController@adjustStock');
$router->get('orders/exchange/{id}', 'OrderController@exchange');
$router->post('orders/exchange/store/{id}', 'OrderController@storeExchange');

// Expense routes
$router->get('expenses', 'ExpenseController@list');
$router->get('expenses/create', 'ExpenseController@create');
$router->post('expenses', 'ExpenseController@store');
$router->get('expenses/{id}', 'ExpenseController@show');
$router->get('expenses/edit/{id}', 'ExpenseController@edit');
$router->post('expenses/update/{id}', 'ExpenseController@update');
$router->post('expenses/delete/{id}', 'ExpenseController@delete');

// Report routes
$router->get('reports', 'ReportController@index');
$router->get('reports/revenue', 'ReportController@revenue');
$router->get('reports/products', 'ReportController@topProducts');
$router->get('reports/expenses', 'ReportController@expenses');
$router->get('reports/inventory', 'ReportController@inventory');
$router->get('reports/stock-shortage', 'ReportController@stockShortage');
$router->get('reports/printing', 'ReportController@printing');
$router->post('reports/export', 'ReportController@export');

// Dispatch the request
$router->dispatch();
