<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;
use App\Utils\Database;

/**
 * Maintenance Mode Middleware
 * 
 * Blocks all requests during maintenance mode
 * Allows SaaS Owner to bypass for system administration
 */
class MaintenanceMiddleware
{
    /**
     * Check if system is in maintenance mode
     * 
     * @param array|null $user Current authenticated user
     * @return void
     */
    public static function handle(?array $user = null): void
    {
        // Check maintenance mode from settings
        $maintenanceMode = self::isMaintenanceMode();
        
        if (!$maintenanceMode) {
            return; // Not in maintenance, proceed normally
        }
        
        // Allow SaaS Owner to bypass maintenance mode
        if ($user && isset($user['role']) && $user['role'] === 'saas_owner') {
            // Add header to indicate bypass
            header('X-Maintenance-Bypass: true');
            header('X-Maintenance-Role: saas_owner');
            return;
        }
        
        // Get maintenance message
        $message = self::getMaintenanceMessage();
        
        // Return maintenance response
        self::sendMaintenanceResponse($message);
    }
    
    /**
     * Check if maintenance mode is enabled
     * 
     * @return bool
     */
    private static function isMaintenanceMode(): bool
    {
        try {
            // Check flag file first (fastest)
            if (file_exists(BASE_PATH . '/storage/maintenance.flag')) {
                return true;
            }
            
            // Check database setting
            $setting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = 'maintenance_mode' AND id_gudang IS NULL LIMIT 1"
            );
            
            return $setting && (int)$setting['nilai'] === 1;
            
        } catch (\Exception $e) {
            // If can't check (DB down), assume NOT in maintenance to avoid lockout
            error_log("MaintenanceMiddleware: Error checking maintenance mode: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get maintenance message
     * 
     * @return string
     */
    private static function getMaintenanceMessage(): string
    {
        try {
            $setting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = 'maintenance_message' AND id_gudang IS NULL LIMIT 1"
            );
            
            if ($setting && !empty($setting['nilai'])) {
                return $setting['nilai'];
            }
        } catch (\Exception $e) {
            // Use default if can't fetch
        }
        
        return 'Sistem sedang dalam pemeliharaan. Mohon coba beberapa saat lagi.';
    }
    
    /**
     * Send maintenance mode response
     * 
     * @param string $message
     * @return void
     */
    private static function sendMaintenanceResponse(string $message): void
    {
        // For API requests, return JSON
        if (self::isApiRequest()) {
            Response::json([
                'success' => false,
                'message' => $message,
                'maintenance_mode' => true,
                'retry_after' => 3600, // Suggest retry after 1 hour
            ], 503);
            exit;
        }
        
        // For web requests, show HTML page
        self::sendMaintenanceHtml($message);
        exit;
    }
    
    /**
     * Check if request is API request
     * 
     * @return bool
     */
    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check URI
        if (str_contains($uri, '/api/')) {
            return true;
        }
        
        // Check Accept header
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Send maintenance HTML page
     * 
     * @param string $message
     * @return void
     */
    private static function sendMaintenanceHtml(string $message): void
    {
        http_response_code(503);
        header('Retry-After: 3600');
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - Peace Seafood WMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .maintenance-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 500px;
            text-align: center;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 16px;
        }
        
        p {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-weight: 600;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 20px;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        
        .refresh-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="icon">🔧</div>
        <h1>Maintenance Mode</h1>
        <p>{$message}</p>
        
        <div class="info">
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">Under Maintenance</span>
            </div>
            <div class="info-item">
                <span class="info-label">Expected Duration:</span>
                <span class="info-value">~1 hour</span>
            </div>
            <div class="info-item">
                <span class="info-label">Time:</span>
                <span class="info-value" id="current-time"></span>
            </div>
        </div>
        
        <button class="refresh-btn" onclick="location.reload()">
            🔄 Check Again
        </button>
    </div>
    
    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeStr;
        }
        
        updateTime();
        setInterval(updateTime, 1000);
        
        // Auto-refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 5 * 60 * 1000);
    </script>
</body>
</html>
HTML;
        
        echo $html;
    }
    
    /**
     * Enable maintenance mode
     * 
     * @param string $message Custom maintenance message
     * @return bool
     */
    public static function enable(string $message = ''): bool
    {
        try {
            // Create flag file
            file_put_contents(BASE_PATH . '/storage/maintenance.flag', date('Y-m-d H:i:s'));
            
            // Update database
            Database::query(
                "UPDATE settings SET nilai = '1' WHERE kunci = 'maintenance_mode' AND id_gudang IS NULL"
            );
            
            if ($message) {
                Database::query(
                    "UPDATE settings SET nilai = ? WHERE kunci = 'maintenance_message' AND id_gudang IS NULL",
                    [$message]
                );
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("MaintenanceMiddleware::enable - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disable maintenance mode
     * 
     * @return bool
     */
    public static function disable(): bool
    {
        try {
            // Remove flag file
            $flagFile = BASE_PATH . '/storage/maintenance.flag';
            if (file_exists($flagFile)) {
                unlink($flagFile);
            }
            
            // Update database
            Database::query(
                "UPDATE settings SET nilai = '0' WHERE kunci = 'maintenance_mode' AND id_gudang IS NULL"
            );
            
            return true;
        } catch (\Exception $e) {
            error_log("MaintenanceMiddleware::disable - Error: " . $e->getMessage());
            return false;
        }
    }
}
