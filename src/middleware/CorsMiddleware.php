<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * CORS Middleware
 */
class CorsMiddleware
{
    public static function handle(): void
    {
        $config        = require dirname(__DIR__, 2) . '/config/app.php';
        $allowedOrigin = $config['cors']['origin'];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Allow configured origin or localhost variants
        $allowed = explode(',', $allowedOrigin);
        if (in_array($origin, $allowed, true) || empty($origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
