<?php

namespace App\Controllers;

use App\Utils\Response;
use App\Utils\Database;

/**
 * Health Check Controller
 * 
 * Provides system health status for monitoring tools
 * Used by: Uptime Robot, Datadog, New Relic, etc.
 * 
 * Endpoint: GET /api/health
 */
class HealthController
{
    /**
     * Perform comprehensive health check
     * 
     * Returns:
     * - 200 OK if all systems healthy
     * - 503 Service Unavailable if any critical system down
     * 
     * @return void
     */
    public static function check(): void
    {
        $startTime = microtime(true);
        
        $checks = [
            'database' => self::checkDatabase(),
            'disk_space' => self::checkDiskSpace(),
            'email_queue' => self::checkEmailQueue(),
            'uploads_writable' => self::checkUploadsWritable(),
            'cache_writable' => self::checkCacheWritable(),
        ];
        
        // Overall health status
        $healthy = !in_array(false, array_column($checks, 'healthy'), true);
        
        // Calculate response time
        $responseTime = round((microtime(true) - $startTime) * 1000, 2); // ms
        
        $response = [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time_ms' => $responseTime,
            'checks' => $checks,
            'version' => self::getAppVersion(),
            'environment' => $_ENV['APP_ENV'] ?? 'production',
        ];
        
        Response::json($response, $healthy ? 200 : 503);
    }
    
    /**
     * Check database connectivity and performance
     * 
     * @return array
     */
    private static function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            
            // Test query
            $result = Database::fetchOne("SELECT 1 as ping, NOW() as db_time");
            
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            if (!$result || $result['ping'] != 1) {
                return [
                    'healthy' => false,
                    'message' => 'Database ping failed',
                    'response_time_ms' => $responseTime,
                ];
            }
            
            // Check connection count (warning if > 80% of max)
            $connections = Database::fetchOne("SHOW STATUS LIKE 'Threads_connected'");
            $maxConnections = Database::fetchOne("SHOW VARIABLES LIKE 'max_connections'");
            
            $currentConn = (int)($connections['Value'] ?? 0);
            $maxConn = (int)($maxConnections['Value'] ?? 151);
            $usage = $maxConn > 0 ? round(($currentConn / $maxConn) * 100, 2) : 0;
            
            return [
                'healthy' => true,
                'message' => 'Database connected',
                'response_time_ms' => $responseTime,
                'connections' => [
                    'current' => $currentConn,
                    'max' => $maxConn,
                    'usage_percent' => $usage,
                ],
            ];
            
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check disk space availability
     * 
     * @return array
     */
    private static function checkDiskSpace(): array
    {
        try {
            $path = realpath(__DIR__ . '/../../');
            $freeSpace = disk_free_space($path);
            $totalSpace = disk_total_space($path);
            
            if ($freeSpace === false || $totalSpace === false) {
                return [
                    'healthy' => false,
                    'message' => 'Cannot read disk space',
                ];
            }
            
            $freeSpaceGB = round($freeSpace / (1024 ** 3), 2);
            $totalSpaceGB = round($totalSpace / (1024 ** 3), 2);
            $usagePercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
            
            // Warning if less than 1GB free OR usage > 90%
            $healthy = $freeSpaceGB >= 1 && $usagePercent < 90;
            
            return [
                'healthy' => $healthy,
                'message' => $healthy ? 'Disk space sufficient' : 'Low disk space',
                'free_gb' => $freeSpaceGB,
                'total_gb' => $totalSpaceGB,
                'usage_percent' => $usagePercent,
            ];
            
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Disk space check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check email queue health
     * 
     * @return array
     */
    private static function checkEmailQueue(): array
    {
        try {
            // Check for stuck emails (processing > 10 minutes)
            $stuckEmails = Database::fetchOne(
                "SELECT COUNT(*) as count FROM email_queue 
                 WHERE status = 'processing' 
                 AND processing_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
            );
            
            // Check pending queue size
            $pending = Database::fetchOne(
                "SELECT COUNT(*) as count FROM email_queue WHERE status = 'pending'"
            );
            
            // Check failed emails in last hour
            $recentFailed = Database::fetchOne(
                "SELECT COUNT(*) as count FROM email_queue 
                 WHERE status = 'failed' 
                 AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            );
            
            $stuckCount = (int)($stuckEmails['count'] ?? 0);
            $pendingCount = (int)($pending['count'] ?? 0);
            $failedCount = (int)($recentFailed['count'] ?? 0);
            
            // Unhealthy if: stuck emails exist OR pending > 100 OR recent failures > 10
            $healthy = $stuckCount === 0 && $pendingCount < 100 && $failedCount < 10;
            
            return [
                'healthy' => $healthy,
                'message' => $healthy ? 'Email queue operational' : 'Email queue issues detected',
                'stuck_emails' => $stuckCount,
                'pending_emails' => $pendingCount,
                'failed_last_hour' => $failedCount,
            ];
            
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Email queue check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check if uploads directory is writable
     * 
     * @return array
     */
    private static function checkUploadsWritable(): array
    {
        $uploadPath = realpath(__DIR__ . '/../../storage/uploads');
        
        if (!$uploadPath || !is_dir($uploadPath)) {
            return [
                'healthy' => false,
                'message' => 'Uploads directory not found',
            ];
        }
        
        $writable = is_writable($uploadPath);
        
        return [
            'healthy' => $writable,
            'message' => $writable ? 'Uploads directory writable' : 'Uploads directory not writable',
            'path' => $uploadPath,
        ];
    }
    
    /**
     * Check if cache directory is writable
     * 
     * @return array
     */
    private static function checkCacheWritable(): array
    {
        $cachePath = realpath(__DIR__ . '/../../storage/cache');
        
        // Create cache directory if not exists
        if (!$cachePath) {
            $cachePath = __DIR__ . '/../../storage/cache';
            if (!@mkdir($cachePath, 0755, true) && !is_dir($cachePath)) {
                return [
                    'healthy' => false,
                    'message' => 'Cache directory cannot be created',
                ];
            }
        }
        
        $writable = is_writable($cachePath);
        
        return [
            'healthy' => $writable,
            'message' => $writable ? 'Cache directory writable' : 'Cache directory not writable',
            'path' => $cachePath,
        ];
    }
    
    /**
     * Get application version
     * 
     * @return string
     */
    private static function getAppVersion(): string
    {
        // Read from composer.json or version file
        $composerFile = __DIR__ . '/../../composer.json';
        
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            return $composer['version'] ?? '1.0.0';
        }
        
        return '1.0.0';
    }
}
