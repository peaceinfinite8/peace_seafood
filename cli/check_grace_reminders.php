#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * CLI Script: Check Grace Period Reminders
 * Run this script daily via cron to check and send grace period email reminders
 * 
 * Usage:
 *   php cli/check_grace_reminders.php
 * 
 * Cron example (run daily at 8 AM):
 *   0 8 * * * cd /path/to/peace_seafood && php cli/check_grace_reminders.php
 */

// Bootstrap
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Include database config
$pdo = require BASE_PATH . '/config/database.php';

use App\Services\WMS\GracePeriodService;
use App\Services\Shared\NotificationService;
use App\Utils\Database;

echo "==============================================\n";
echo "Grace Period Reminder Check\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "==============================================\n\n";

try {
    // Check if email reminders are enabled
    $setting = Database::fetchOne(
        "SELECT nilai FROM settings WHERE kunci = 'notification_email_reminder' AND id_gudang IS NULL LIMIT 1"
    );
    
    if ($setting && (int)$setting['nilai'] !== 1) {
        echo "❌ Email reminders are disabled in settings.\n";
        echo "   Enable 'notification_email_reminder' in settings to use this feature.\n\n";
        exit(0);
    }

    echo "✓ Email reminders enabled\n";
    echo "Checking all active gudang subscriptions...\n\n";

    // Run grace period check
    $result = GracePeriodService::checkAndSendReminders();

    if ($result['success']) {
        echo "✅ SUCCESS\n";
        echo "   Emails sent: {$result['sent']}\n";
        
        // Also create in-app notifications for grace period
        $allGudang = Database::fetchAll(
            "SELECT g.id, g.subscription_until
             FROM gudang g
             WHERE g.is_active = 1 
               AND g.subscription_until IS NOT NULL
               AND g.status_langganan = 'aktif'"
        );

        $notifCreated = 0;
        foreach ($allGudang as $gudang) {
            $remaining = GracePeriodService::getRemainingDays((int)$gudang['id']);
            if ($remaining !== null && in_array($remaining, [7, 3, 1], true)) {
                NotificationService::createGracePeriodNotifications((int)$gudang['id'], $remaining);
                $notifCreated++;
            }
        }
        
        echo "   In-app notifications created for: {$notifCreated} gudang\n";
    } else {
        echo "❌ FAILED\n";
        echo "   Error: " . ($result['error'] ?? 'Unknown error') . "\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "❌ EXCEPTION OCCURRED\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n==============================================\n";
echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
echo "==============================================\n";

exit(0);

