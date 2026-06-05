<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;

/**
 * CSRF Middleware
 * Protects against Cross-Site Request Forgery attacks
 */
class CsrfMiddleware
{
    /**
     * Generate CSRF token and store in session
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['csrf_token'] ?? null;
    }
    
    /**
     * Validate CSRF token
     * Checks token from POST data or custom header
     */
    public static function validate(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (empty($sessionToken)) {
            return false;
        }
        
        // Check token from POST data
        $postToken = $_POST['csrf_token'] ?? null;
        
        // Check token from custom header (for AJAX requests)
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        // Check token from Authorization header (Bearer format)
        if (empty($postToken) && empty($headerToken)) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
                // Extract from Bearer if it contains csrf_token
                // Format: "Bearer jwt_token csrf_token"
            }
        }
        
        $requestToken = $postToken ?? $headerToken;
        
        if (empty($requestToken)) {
            return false;
        }
        
        // Constant-time comparison to prevent timing attacks
        return hash_equals($sessionToken, $requestToken);
    }
    
    /**
     * Middleware handler - validates CSRF token for POST/PUT/DELETE requests
     */
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Only check CSRF for state-changing methods
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return;
        }
        
        // Skip CSRF check for certain endpoints (webhooks, public APIs)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $skipPatterns = [
            '/api/auth/login',
            '/api/auth/signup', 
            '/api/webhook/',
            '/api/saas/approve-payment', // Magic link (uses token)
        ];
        
        foreach ($skipPatterns as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                return; // Skip CSRF check
            }
        }
        
        // Validate CSRF token
        if (!self::validate()) {
            error_log("CSRF validation failed for {$method} {$uri}");
            Response::error('CSRF token validation failed', 403);
        }
    }
    
    /**
     * Regenerate CSRF token (call after login for security)
     */
    public static function regenerateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return $_SESSION['csrf_token'];
    }
}
