<?php
declare(strict_types=1);

// Base path
define('BASE_PATH', dirname(__DIR__));

// Composer autoload if exists
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Basic URI parsing — remove project base if present
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = preg_replace('#^/peace_seafood#', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// Route to API or Web router
if (strpos($uri, '/api') === 0) {
    require BASE_PATH . '/routes/api.php';
} else {
    require BASE_PATH . '/routes/web.php';
}

