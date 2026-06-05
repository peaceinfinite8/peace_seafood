<?php

namespace Tests\Payment;

use Tests\TestCase;
use App\Utils\Database;

/**
 * Test Webhook Payment Idempotency
 * - Same order_id should not create duplicate records
 * - Duplicate webhook calls should be ignored
 * - Webhook validation and security
 */
class WebhookTest extends TestCase
{
    private $testGudangId;
    
    /**
     * Setup test data
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $gudang = $this->getTestGudang();
        $this->testGudangId = $gudang['id'];
    }
    
    /**
     * Test duplicate webhook with same order_id is ignored
     */
    public function testDuplicateWebhookIsIgnored(): void
    {
        $orderId = 'ORDER_TEST_' . time() . '_' . uniqid();
        
        // First webhook call - should process
        $webhookData = [
            'order_id' => $orderId,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid',
            'payment_method' => 'bank_transfer'
        ];
        
        $response1 = $this->api('POST', '/webhook/payment', $webhookData);
        
        // Should succeed (or return appropriate response based on implementation)
        $this->assertTrue(
            $response1['status'] >= 200 && $response1['status'] < 300,
            'First webhook should be processed'
        );
        
        // Second webhook call with same order_id - should be ignored
        $response2 = $this->api('POST', '/webhook/payment', $webhookData);
        
        // Should return success but not process again (idempotent)
        $this->assertTrue(
            $response2['status'] >= 200 && $response2['status'] < 300,
            'Second webhook should return success (idempotent)'
        );
        
        // Verify only one payment record created
        $count = Database::fetchOne(
            "SELECT COUNT(*) as count FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
        
        $this->assertEquals(1, $count['count'], 'Should only create one payment record');
        
        // Cleanup
        Database::query(
            "DELETE FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
    }
    
    /**
     * Test different order_id creates separate records
     */
    public function testDifferentOrderIdCreatesNewRecord(): void
    {
        $orderId1 = 'ORDER_TEST_1_' . time();
        $orderId2 = 'ORDER_TEST_2_' . time();
        
        // First webhook
        $webhookData1 = [
            'order_id' => $orderId1,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        $response1 = $this->api('POST', '/webhook/payment', $webhookData1);
        $this->assertTrue($response1['status'] >= 200 && $response1['status'] < 300);
        
        // Second webhook with different order_id
        $webhookData2 = [
            'order_id' => $orderId2,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        $response2 = $this->api('POST', '/webhook/payment', $webhookData2);
        $this->assertTrue($response2['status'] >= 200 && $response2['status'] < 300);
        
        // Verify two records created
        $count = Database::fetchOne(
            "SELECT COUNT(*) as count FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
        
        $this->assertEquals(2, $count['count'], 'Should create two separate payment records');
        
        // Cleanup
        Database::query(
            "DELETE FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
    }
    
    /**
     * Test webhook with missing required fields
     */
    public function testWebhookWithMissingFields(): void
    {
        $webhookData = [
            'order_id' => 'ORDER_MISSING_' . time()
            // Missing id_gudang, amount, status
        ];
        
        $response = $this->api('POST', '/webhook/payment', $webhookData);
        
        // Should return error
        $this->assertError($response, null, 'Should return error with missing fields');
    }
    
    /**
     * Test webhook with invalid gudang
     */
    public function testWebhookWithInvalidGudang(): void
    {
        $webhookData = [
            'order_id' => 'ORDER_INVALID_' . time(),
            'id_gudang' => 99999, // Non-existent gudang
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        $response = $this->api('POST', '/webhook/payment', $webhookData);
        
        // Should return error
        $this->assertError($response, null, 'Should return error with invalid gudang');
    }
    
    /**
     * Test webhook idempotency with concurrent requests
     */
    public function testWebhookIdempotencyWithConcurrentRequests(): void
    {
        $orderId = 'ORDER_CONCURRENT_' . time();
        
        $webhookData = [
            'order_id' => $orderId,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        // Simulate concurrent requests (send 5 times rapidly)
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->api('POST', '/webhook/payment', $webhookData);
        }
        
        // All should return success (idempotent)
        foreach ($responses as $response) {
            $this->assertTrue(
                $response['status'] >= 200 && $response['status'] < 300,
                'All concurrent requests should return success'
            );
        }
        
        // Verify only one payment record created
        $count = Database::fetchOne(
            "SELECT COUNT(*) as count FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
        
        $this->assertEquals(1, $count['count'], 'Should only create one payment record despite concurrent requests');
        
        // Cleanup
        Database::query(
            "DELETE FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
    }
    
    /**
     * Test webhook extends subscription correctly
     */
    public function testWebhookExtendsSubscription(): void
    {
        $orderId = 'ORDER_EXTEND_' . time();
        
        // Get current subscription
        $gudang = Database::fetchOne(
            "SELECT subscription_until FROM gudang WHERE id = ?",
            [$this->testGudangId]
        );
        $oldExpiry = $gudang['subscription_until'];
        
        // Send webhook
        $webhookData = [
            'order_id' => $orderId,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        $response = $this->api('POST', '/webhook/payment', $webhookData);
        $this->assertTrue($response['status'] >= 200 && $response['status'] < 300);
        
        // Verify subscription extended
        $gudang = Database::fetchOne(
            "SELECT subscription_until FROM gudang WHERE id = ?",
            [$this->testGudangId]
        );
        $newExpiry = $gudang['subscription_until'];
        
        $this->assertGreaterThan(
            strtotime($oldExpiry),
            strtotime($newExpiry),
            'Subscription should be extended after payment'
        );
        
        // Cleanup - restore old subscription
        Database::update('gudang', ['id' => $this->testGudangId], [
            'subscription_until' => $oldExpiry
        ]);
        
        Database::query(
            "DELETE FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
    }
    
    /**
     * Test webhook with invalid status
     */
    public function testWebhookWithInvalidStatus(): void
    {
        $webhookData = [
            'order_id' => 'ORDER_INVALID_STATUS_' . time(),
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'invalid_status'
        ];
        
        $response = $this->api('POST', '/webhook/payment', $webhookData);
        
        // Should handle gracefully (either reject or process with caution)
        // Implementation dependent - just ensure it doesn't crash
        $this->assertIsArray($response['body'], 'Should return valid response structure');
    }
    
    /**
     * Test webhook creates activity log
     */
    public function testWebhookCreatesActivityLog(): void
    {
        $orderId = 'ORDER_LOG_' . time();
        
        $webhookData = [
            'order_id' => $orderId,
            'id_gudang' => $this->testGudangId,
            'amount' => 500000,
            'status' => 'paid'
        ];
        
        $response = $this->api('POST', '/webhook/payment', $webhookData);
        $this->assertTrue($response['status'] >= 200 && $response['status'] < 300);
        
        // Check activity log created
        $log = Database::fetchOne(
            "SELECT * FROM activity_log 
             WHERE id_gudang = ? AND action LIKE '%payment%' 
             ORDER BY created_at DESC LIMIT 1",
            [$this->testGudangId]
        );
        
        $this->assertNotNull($log, 'Activity log should be created after webhook');
        
        // Cleanup
        Database::query(
            "DELETE FROM payment_requests WHERE id_gudang = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->testGudangId]
        );
        
        if ($log) {
            Database::query("DELETE FROM activity_log WHERE id = ?", [$log['id']]);
        }
    }
}
