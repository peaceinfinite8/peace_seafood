<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Utils\Database;
use App\Utils\Email;
use Exception;

/**
 * Webhook Service
 * Handles payment webhook processing with idempotency
 */
class WebhookService
{
    /**
     * Process payment webhook with idempotency
     * 
     * @param string $orderId Unique order identifier
     * @param int $idGudang Warehouse ID
     * @param float $amount Payment amount
     * @param string $status Payment status (paid, pending, failed)
     * @param string $paymentMethod Payment method used
     * @param array $rawPayload Full webhook payload for logging
     * @return array ['success' => bool, 'processed' => bool, 'message' => string]
     */
    public static function processPayment(
        string $orderId,
        int $idGudang,
        float $amount,
        string $status,
        string $paymentMethod,
        array $rawPayload
    ): array {
        Database::beginTransaction();
        
        try {
            // 1. Check idempotency - has this order_id been processed?
            $existing = Database::fetchOne(
                "SELECT id, status FROM payment_requests 
                 WHERE order_id = ? 
                 FOR UPDATE",
                [$orderId]
            );
            
            if ($existing) {
                Database::rollBack();
                return [
                    'success' => true,
                    'processed' => false,
                    'message' => 'Order already processed (idempotent)',
                    'order_id' => $orderId
                ];
            }
            
            // 2. Validate gudang exists
            $gudang = Database::fetchOne(
                "SELECT id, nama_gudang, id_bos FROM gudang WHERE id = ? AND is_active = 1",
                [$idGudang]
            );
            
            if (!$gudang) {
                Database::rollBack();
                return [
                    'success' => false,
                    'processed' => false,
                    'message' => 'Gudang not found or inactive',
                    'code' => 404
                ];
            }
            
            // 3. Only process if status is "paid"
            if ($status !== 'paid') {
                // Log the webhook but don't process payment
                self::logWebhook($orderId, $idGudang, $status, $rawPayload);
                
                Database::commit();
                return [
                    'success' => true,
                    'processed' => false,
                    'message' => "Payment status '{$status}' - not processed"
                ];
            }
            
            // 4. Get renewal duration from settings
            $renewalSetting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = 'renewal_duration_days' AND id_gudang IS NULL"
            );
            $days = (int)($renewalSetting['nilai'] ?? 30);
            
            // 5. Calculate new subscription date
            $currentExpiry = Database::fetchOne(
                "SELECT subscription_until FROM gudang WHERE id = ?",
                [$idGudang]
            );
            
            $baseDate = $currentExpiry['subscription_until'] ?? date('Y-m-d H:i:s');
            if (strtotime($baseDate) < time()) {
                $baseDate = date('Y-m-d H:i:s'); // Start from now if already expired
            }
            $newExpiry = date('Y-m-d H:i:s', strtotime($baseDate . " +{$days} days"));
            
            // 6. Create payment request record
            $requestId = Database::insert('payment_requests', [
                'id_gudang' => $idGudang,
                'id_bos' => $gudang['id_bos'],
                'order_id' => $orderId,
                'nominal' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'approved',
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => self::getSaasOwnerId(),
                'webhook_payload' => json_encode($rawPayload)
            ]);
            
            // 7. Update gudang subscription
            Database::update('gudang', ['id' => $idGudang], [
                'subscription_until' => $newExpiry,
                'status_langganan' => 'active'
            ]);
            
            // 8. Create notification for Bos
            $bos = Database::fetchOne("SELECT id, email, name FROM users WHERE id = ?", [$gudang['id_bos']]);
            
            if ($bos) {
                \App\Services\Shared\NotificationService::createNotification(
                    $bos['id'],
                    'payment_approved',
                    'Pembayaran Diterima',
                    "Masa aktif gudang {$gudang['nama_gudang']} telah diperpanjang hingga " . date('d M Y', strtotime($newExpiry)),
                    null,
                    $idGudang
                );
                
                // Send email confirmation
                self::sendConfirmationEmail($bos['email'], $gudang['nama_gudang'], $amount, $newExpiry);
            }
            
            // 9. Create notification for SaaS Owner
            $owner = Database::fetchOne("SELECT id FROM users WHERE role = 'saas_owner' LIMIT 1");
            if ($owner) {
                \App\Services\Shared\NotificationService::createNotification(
                    $owner['id'],
                    'payment_received',
                    'Pembayaran Diterima',
                    "Gudang {$gudang['nama_gudang']} melakukan pembayaran Rp " . number_format($amount, 0, ',', '.'),
                    "/saas/payments?request_id={$requestId}",
                    null
                );
            }
            
            // 10. Log activity
            Database::insert('activity_log', [
                'id_user' => self::getSaasOwnerId(),
                'id_gudang' => $idGudang,
                'action' => 'webhook_payment',
                'table_name' => 'payment_requests',
                'record_id' => $requestId,
                'description' => "Webhook payment processed: Order {$orderId}, Amount: {$amount}, Extended until {$newExpiry}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
            
            // 11. Log webhook
            self::logWebhook($orderId, $idGudang, 'processed', $rawPayload);
            
            Database::commit();
            
            return [
                'success' => true,
                'processed' => true,
                'message' => 'Payment processed successfully',
                'request_id' => $requestId,
                'new_expiry' => $newExpiry
            ];
            
        } catch (Exception $e) {
            Database::rollBack();
            error_log("WebhookService::processPayment - Error: " . $e->getMessage());
            
            // Log failed webhook
            self::logWebhook($orderId, $idGudang, 'error', array_merge($rawPayload, [
                'error' => $e->getMessage()
            ]));
            
            return [
                'success' => false,
                'processed' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
    
    /**
     * Validate webhook signature (HMAC)
     */
    public static function validateSignature(string $payload, string $signature): bool
    {
        try {
            // Get webhook secret from settings or env
            $secret = $_ENV['WEBHOOK_SECRET'] ?? self::getSetting('webhook_secret', 'default-webhook-secret');
            
            // Calculate HMAC
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            
            // Constant-time comparison to prevent timing attacks
            return hash_equals($expectedSignature, $signature);
            
        } catch (Exception $e) {
            error_log("WebhookService::validateSignature - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log webhook for audit trail
     */
    private static function logWebhook(string $orderId, int $idGudang, string $status, array $payload): void
    {
        try {
            Database::insert('webhook_log', [
                'order_id' => $orderId,
                'id_gudang' => $idGudang,
                'status' => $status,
                'payload' => json_encode($payload),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("WebhookService::logWebhook - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send payment confirmation email
     */
    private static function sendConfirmationEmail(string $email, string $gudangName, float $amount, string $newExpiry): void
    {
        try {
            $subject = '✅ Pembayaran Diterima - Akses Gudang Diperpanjang';
            
            $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
            $formattedExpiry = date('d F Y', strtotime($newExpiry));
            
            ob_start();
            include BASE_PATH . '/src/views/emails/payment_approved.php';
            $body = ob_get_clean();
            
            Email::send($email, $subject, $body);
            
        } catch (Exception $e) {
            error_log("WebhookService::sendConfirmationEmail - Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get SaaS Owner ID
     */
    private static function getSaasOwnerId(): int
    {
        try {
            $owner = Database::fetchOne("SELECT id FROM users WHERE role = 'saas_owner' LIMIT 1");
            return $owner ? (int)$owner['id'] : 1;
        } catch (Exception $e) {
            return 1; // Fallback
        }
    }
    
    /**
     * Get setting value
     */
    private static function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = Database::fetchOne(
                "SELECT nilai FROM settings WHERE kunci = ? AND id_gudang IS NULL",
                [$key]
            );
            return $setting ? $setting['nilai'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}
