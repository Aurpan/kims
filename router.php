<?php
// Router file for PHP built-in development server
// This file handles URL rewriting that would normally be done by .htaccess

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash and handle root
if ($request === '/' || $request === '') {
    $url = '';
} else {
    $url = ltrim($request, '/');
}

// Serve static files from the public directory directly
$file_path = __DIR__ . '/public/' . $url;
if ($url !== '' && is_file($file_path)) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
    ];
    $mime = $mimeTypes[$ext] ?? mime_content_type($file_path);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
}

// Check if it's a real directory
if ($url !== '' && is_dir($file_path)) {
    return false;
}

// Route to index.php with the URL as a query parameter
$_GET['url'] = $url;

// Change to public directory so relative paths work
chdir(__DIR__ . '/public');

require 'index.php';
