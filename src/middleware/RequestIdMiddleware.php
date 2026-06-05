<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Request ID Middleware
 * 
 * Generates unique request ID for distributed tracing and debugging
 * Adds X-Request-ID header to response and makes it available for logging
 */
class RequestIdMiddleware
{
    private static ?string $currentRequestId = null;
    
    /**
     * Generate and attach request ID
     * 
     * @return void
     */
    public static function handle(): void
    {
        // Check if request already has an ID (from proxy/load balancer)
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
        
        // Generate new ID if not present
        if (!$requestId) {
            $requestId = self::generateRequestId();
        }
        
        // Store for later use
        self::$currentRequestId = $requestId;
        
        // Add to response headers
        header("X-Request-ID: {$requestId}");
        
        // Store in globals for easy access in logging
        $GLOBALS['request_id'] = $requestId;
    }
    
    /**
     * Get current request ID
     * 
     * @return string|null
     */
    public static function getRequestId(): ?string
    {
        return self::$currentRequestId ?? $GLOBALS['request_id'] ?? null;
    }
    
    /**
     * Generate unique request ID
     * 
     * Format: req_YYYYMMDD_HHMMSS_UNIQUEID
     * Example: req_20260606_143045_65a3b2c1d4e5f
     * 
     * @return string
     */
    private static function generateRequestId(): string
    {
        $timestamp = date('Ymd_His');
        $uniqueId = bin2hex(random_bytes(8));
        
        return "req_{$timestamp}_{$uniqueId}";
    }
    
    /**
     * Log with request ID prefix
     * 
     * @param string $message
     * @param string $level
     * @return void
     */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $requestId = self::getRequestId() ?? 'no-request-id';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$requestId}] [{$level}] {$message}";
        
        error_log($logMessage);
    }
    
    /**
     * Get formatted request context for logging
     * 
     * @return array
     */
    public static function getContext(): array
    {
        return [
            'request_id' => self::getRequestId(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}
