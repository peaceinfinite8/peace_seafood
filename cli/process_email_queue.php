#!/usr/bin/env php
<?php
/**
 * Email Queue Worker
 * Processes queued emails in background
 * 
 * Usage:
 *   php cli/process_email_queue.php
 * 
 * Cron setup (every 5 minutes):
 *   */5 * * * * /usr/bin/php /var/www/peace_seafood/cli/process_email_queue.php >> /var/log/email_queue.log 2>&1
 */

require_once __DIR__ . '/../config/app.php';

use App\Services\Shared\EmailQueueService;

echo "=================================\n";
echo "Email Queue Worker\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n";
echo "=================================\n\n";

try {
    // Get current queue stats
    $statsBefore = EmailQueueService::getStats();
    echo "Queue Stats (before):\n";
    echo "  Total: {$statsBefore['total']}\n";
    echo "  Pending: {$statsBefore['pending']}\n";
    echo "  Processing: {$statsBefore['processing']}\n";
    echo "  Sent: {$statsBefore['sent']}\n";
    echo "  Failed: {$statsBefore['failed']}\n\n";
    
    // Process queue (batch of 50 emails)
    echo "Processing queue...\n";
    $result = EmailQueueService::processQueue(50);
    
    echo "\nResults:\n";
    echo "  Processed: {$result['processed']}\n";
    echo "  Sent: {$result['sent']}\n";
    echo "  Failed: {$result['failed']}\n\n";
    
    // Get updated stats
    $statsAfter = EmailQueueService::getStats();
    echo "Queue Stats (after):\n";
    echo "  Pending: {$statsAfter['pending']}\n";
    echo "  Sent: {$statsAfter['sent']} (+" . ($statsAfter['sent'] - $statsBefore['sent']) . ")\n";
    echo "  Failed: {$statsAfter['failed']} (+" . ($statsAfter['failed'] - $statsBefore['failed']) . ")\n\n";
    
    // Cleanup old records (older than 30 days)
    if (date('H') === '03') { // Run cleanup at 3 AM only
        echo "Running cleanup...\n";
        $deleted = EmailQueueService::cleanup(30);
        echo "Deleted {$deleted} old records\n\n";
    }
    
    echo "=================================\n";
    echo "Completed: " . date('Y-m-d H:i:s') . "\n";
    echo "=================================\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
