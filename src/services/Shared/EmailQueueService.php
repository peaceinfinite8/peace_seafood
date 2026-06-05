<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Utils\Database;
use App\Utils\Email;
use Exception;

/**
 * Email Queue Service
 * Handles asynchronous email sending with retry logic
 */
class EmailQueueService
{
    /**
     * Add email to queue for later processing
     * 
     * @param string $to Email recipient
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param int $priority 1=highest, 10=lowest (default 5)
     * @param string|null $scheduledAt Future send time (Y-m-d H:i:s)
     * @return int Queue ID
     */
    public static function enqueue(
        string $to,
        string $subject,
        string $body,
        int $priority = 5,
        ?string $scheduledAt = null
    ): int {
        try {
            $queueId = Database::insert('email_queue', [
                'to_email' => $to,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'priority' => max(1, min(10, $priority)), // Clamp to 1-10
                'scheduled_at' => $scheduledAt,
                'attempts' => 0,
                'max_attempts' => 3
            ]);
            
            error_log("EmailQueue: Queued email #{$queueId} to {$to}");
            
            return $queueId;
            
        } catch (Exception $e) {
            error_log("EmailQueueService::enqueue - Error: " . $e->getMessage());
            
            // Fallback: send immediately if queue fails
            Email::send($to, $subject, $body);
            return 0;
        }
    }
    
    /**
     * Process email queue (call from CLI worker)
     * 
     * @param int $batchSize Number of emails to process
     * @return array ['processed' => int, 'sent' => int, 'failed' => int]
     */
    public static function processQueue(int $batchSize = 10): array
    {
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        
        try {
            // Get pending emails (prioritized, ready to send)
            $emails = Database::fetchAll(
                "SELECT * FROM email_queue 
                 WHERE status = 'pending' 
                   AND attempts < max_attempts
                   AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                 ORDER BY priority ASC, created_at ASC 
                 LIMIT ?",
                [$batchSize]
            );
            
            foreach ($emails as $email) {
                $stats['processed']++;
                
                // Mark as processing
                Database::update('email_queue', ['id' => $email['id']], [
                    'status' => 'processing',
                    'processing_at' => date('Y-m-d H:i:s')
                ]);
                
                // Try to send with retry logic
                $result = self::sendWithRetry(
                    $email['to_email'],
                    $email['subject'],
                    $email['body'],
                    (int)$email['attempts']
                );
                
                if ($result['success']) {
                    // Mark as sent
                    Database::update('email_queue', ['id' => $email['id']], [
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                        'attempts' => (int)$email['attempts'] + 1
                    ]);
                    
                    $stats['sent']++;
                    error_log("EmailQueue: Sent email #{$email['id']} to {$email['to_email']}");
                    
                } else {
                    // Mark as failed or back to pending for retry
                    $newAttempts = (int)$email['attempts'] + 1;
                    $maxAttempts = (int)$email['max_attempts'];
                    
                    if ($newAttempts >= $maxAttempts) {
                        // Max attempts reached - mark as failed
                        Database::update('email_queue', ['id' => $email['id']], [
                            'status' => 'failed',
                            'failed_at' => date('Y-m-d H:i:s'),
                            'attempts' => $newAttempts,
                            'error_message' => $result['error'] ?? 'Unknown error'
                        ]);
                        
                        $stats['failed']++;
                        error_log("EmailQueue: Failed email #{$email['id']} after {$newAttempts} attempts");
                        
                    } else {
                        // Retry later - back to pending
                        Database::update('email_queue', ['id' => $email['id']], [
                            'status' => 'pending',
                            'attempts' => $newAttempts,
                            'error_message' => $result['error'] ?? 'Retry scheduled'
                        ]);
                        
                        error_log("EmailQueue: Retry scheduled for email #{$email['id']} (attempt {$newAttempts}/{$maxAttempts})");
                    }
                }
                
                // Small delay to avoid rate limiting
                usleep(100000); // 100ms
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("EmailQueueService::processQueue - Error: " . $e->getMessage());
            return $stats;
        }
    }
    
    /**
     * Send email with exponential backoff retry
     */
    private static function sendWithRetry(
        string $to,
        string $subject,
        string $body,
        int $attemptNumber
    ): array {
        try {
            // Exponential backoff: wait before retry
            if ($attemptNumber > 0) {
                $delay = pow(2, $attemptNumber - 1); // 1s, 2s, 4s, 8s...
                sleep(min($delay, 30)); // Max 30 seconds
            }
            
            $sent = Email::send($to, $subject, $body);
            
            if ($sent) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Email::send() returned false'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get queue statistics
     */
    public static function getStats(): array
    {
        try {
            $stats = Database::fetchOne(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                 FROM email_queue"
            );
            
            return [
                'total' => (int)$stats['total'],
                'pending' => (int)$stats['pending'],
                'processing' => (int)$stats['processing'],
                'sent' => (int)$stats['sent'],
                'failed' => (int)$stats['failed']
            ];
            
        } catch (Exception $e) {
            error_log("EmailQueueService::getStats - Error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'processing' => 0,
                'sent' => 0,
                'failed' => 0
            ];
        }
    }
    
    /**
     * Cleanup old sent/failed emails
     * 
     * @param int $daysToKeep Keep records for X days (default 30)
     * @return int Number of records deleted
     */
    public static function cleanup(int $daysToKeep = 30): int
    {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
            
            $result = Database::query(
                "DELETE FROM email_queue 
                 WHERE status IN ('sent', 'failed') 
                   AND created_at < ?",
                [$cutoffDate]
            );
            
            $deleted = $result->rowCount();
            error_log("EmailQueue: Cleaned up {$deleted} old records");
            
            return $deleted;
            
        } catch (Exception $e) {
            error_log("EmailQueueService::cleanup - Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Retry failed emails
     * 
     * @return int Number of emails reset for retry
     */
    public static function retryFailed(): int
    {
        try {
            $result = Database::query(
                "UPDATE email_queue 
                 SET status = 'pending', 
                     attempts = 0,
                     error_message = NULL,
                     failed_at = NULL
                 WHERE status = 'failed' 
                   AND attempts < max_attempts"
            );
            
            $count = $result->rowCount();
            error_log("EmailQueue: Reset {$count} failed emails for retry");
            
            return $count;
            
        } catch (Exception $e) {
            error_log("EmailQueueService::retryFailed - Error: " . $e->getMessage());
            return 0;
        }
    }
}
