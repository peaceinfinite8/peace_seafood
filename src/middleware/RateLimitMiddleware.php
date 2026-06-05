<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;
use App\Utils\Database;

/**
 * Rate Limit Middleware
 * Prevents abuse by limiting requests per time window
 */
class RateLimitMiddleware
{
    /**
     * Apply rate limiting
     * 
     * @param string $key Unique identifier (email, IP, user_id)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if within limit, false if exceeded
     */
    public static function check(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
    {
        try {
            $now = time();
            $windowStart = $now - $windowSeconds;
            
            // Clean old entries
            Database::query(
                "DELETE FROM rate_limits WHERE created_at < FROM_UNIXTIME(?)",
                [$windowStart]
            );
            
            // Count attempts in current window
            $count = Database::fetchOne(
                "SELECT COUNT(*) as attempts FROM rate_limits 
                 WHERE rate_key = ? AND created_at >= FROM_UNIXTIME(?)",
                [$key, $windowStart]
            );
            
            $attempts = (int)($count['attempts'] ?? 0);
            
            if ($attempts >= $maxAttempts) {
                return false; // Rate limit exceeded
            }
            
            // Record this attempt
            Database::insert('rate_limits', [
                'rate_key' => $key,
                'created_at' => date('Y-m-d H:i:s', $now)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("RateLimitMiddleware::check Error: " . $e->getMessage());
            return true; // Allow request if rate limit check fails
        }
    }
    
    /**
     * Handle rate limit for HTTP endpoint
     * 
     * @param string $identifier Unique key for this request
     * @param int $maxAttempts Max requests
     * @param int $windowSeconds Time window
     */
    public static function handle(string $identifier, int $maxAttempts = 5, int $windowSeconds = 300): void
    {
        if (!self::check($identifier, $maxAttempts, $windowSeconds)) {
            $retryAfter = $windowSeconds;
            
            header("Retry-After: {$retryAfter}");
            header("X-RateLimit-Limit: {$maxAttempts}");
            header("X-RateLimit-Remaining: 0");
            header("X-RateLimit-Reset: " . (time() + $retryAfter));
            
            Response::error(
                "Terlalu banyak percobaan. Coba lagi dalam " . ceil($retryAfter / 60) . " menit.",
                429
            );
        }
    }
    
    /**
     * Get remaining attempts
     */
    public static function remaining(string $key, int $maxAttempts = 5, int $windowSeconds = 300): int
    {
        try {
            $windowStart = time() - $windowSeconds;
            
            $count = Database::fetchOne(
                "SELECT COUNT(*) as attempts FROM rate_limits 
                 WHERE rate_key = ? AND created_at >= FROM_UNIXTIME(?)",
                [$key, $windowStart]
            );
            
            $attempts = (int)($count['attempts'] ?? 0);
            return max(0, $maxAttempts - $attempts);
            
        } catch (\Exception $e) {
            return $maxAttempts;
        }
    }
}
