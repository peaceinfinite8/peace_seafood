<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Autoload
require_once BASE_PATH . '/vendor/autoload.php';

// Load .env (tolerant to parse errors in development)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
} catch (Dotenv\Exception\InvalidFileException $e) {
    // Log but continue — avoid fatal on malformed .env in local dev
    error_log('Dotenv parse warning: ' . $e->getMessage());
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Get URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path
// Root .htaccess rewrites /peace_seafood/... -> public/...
// tapi REQUEST_URI tetap /peace_seafood/...
$basePath = '/peace_seafood';
$uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
// Juga strip /public jika request lewat langsung ke public/
$uri = preg_replace('#^/public#', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// ============================================================
// API Routes — JSON responses
// ============================================================
if (str_starts_with($uri, '/api')) {
    // CORS
    App\Middleware\CorsMiddleware::handle();
    // Route to API
    try {
        require_once BASE_PATH . '/routes/api.php';
    } catch (\Throwable $e) {
        App\Utils\Response::json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            'error'   => ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? $e->getMessage() : 'Internal Server Error'
        ], 500);
    }
    exit;
}

// ============================================================
// Frontend Routes — HTML pages
// ============================================================
require_once BASE_PATH . '/routes/web.php';
