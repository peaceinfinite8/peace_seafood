<?php

declare(strict_types=1);

namespace App\Controllers\SaaS;

use App\Services\SaaS\WebhookService;
use App\Utils\Response;

/**
 * Webhook Controller
 * Handles incoming payment webhooks from payment gateways
 */
class WebhookController
{
    /**
     * Handle payment webhook (POST /api/webhook/payment)
     * Public endpoint - no auth required
     * 
     * Expected payload:
     * {
     *   "order_id": "ORDER_123",
     *   "id_gudang": 1,
     *   "amount": 500000,
     *   "status": "paid",
     *   "payment_method": "bank_transfer",
     *   "signature": "hmac_signature"
     * }
     */
    public function handlePayment(): void
    {
        try {
            // Get raw input
            $rawInput = file_get_contents('php://input');
            $payload = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error('Invalid JSON payload', 400);
            }
            
            // Validate required fields
            $requiredFields = ['order_id', 'id_gudang', 'amount', 'status'];
            foreach ($requiredFields as $field) {
                if (empty($payload[$field])) {
                    Response::error("Missing required field: {$field}", 400);
                }
            }
            
            // Validate signature (if provided)
            if (isset($payload['signature'])) {
                $isValid = WebhookService::validateSignature($rawInput, $payload['signature']);
                if (!$isValid) {
                    error_log("Webhook signature validation failed");
                    Response::error('Invalid webhook signature', 401);
                }
            }
            
            // Process webhook
            $result = WebhookService::processPayment(
                $payload['order_id'],
                (int)$payload['id_gudang'],
                (float)$payload['amount'],
                $payload['status'],
                $payload['payment_method'] ?? 'unknown',
                $payload
            );
            
            if ($result['success']) {
                Response::success('Webhook processed successfully', [
                    'order_id' => $payload['order_id'],
                    'processed' => $result['processed']
                ]);
            } else {
                Response::error($result['message'], $result['code'] ?? 400);
            }
            
        } catch (\Exception $e) {
            error_log("WebhookController::handlePayment - Error: " . $e->getMessage());
            Response::error('Webhook processing failed', 500);
        }
    }
    
    /**
     * Test webhook endpoint (POST /api/webhook/test)
     * For development/testing only
     */
    public function test(): void
    {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            
            error_log("Test webhook received: " . json_encode($payload));
            
            Response::success('Test webhook received', [
                'payload' => $payload,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("WebhookController::test - Error: " . $e->getMessage());
            Response::error('Test webhook failed', 500);
        }
    }
}
