<?php

namespace Tests\Payment;

use Tests\TestCase;
use App\Utils\Database;

/**
 * Test Magic Link Payment Approval Flow
 * - Valid token approval
 * - Expired token
 * - Already used token
 * - Token not found
 */
class MagicLinkTest extends TestCase
{
    private $testGudangId;
    private $testBosId;
    
    /**
     * Setup test data
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get test gudang and bos
        $gudang = $this->getTestGudang();
        $this->testGudangId = $gudang['id'];
        
        $bos = Database::fetchOne(
            "SELECT id FROM users WHERE role = 'bos' AND id_gudang = ? LIMIT 1",
            [$this->testGudangId]
        );
        $this->testBosId = $bos['id'];
    }
    
    /**
     * Test approval with valid token
     */
    public function testApprovalWithValidToken(): void
    {
        // Create payment request with valid token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'pending',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt
        ]);
        
        // Approve via magic link
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        
        // Should return 200 (success page)
        $this->assertEquals(200, $response['status'], 'Should approve with valid token');
        
        // Verify payment request is approved
        $request = Database::fetchOne(
            "SELECT status, magic_token_used_at, approved_at FROM payment_requests WHERE id = ?",
            [$requestId]
        );
        
        $this->assertEquals('approved', $request['status'], 'Status should be approved');
        $this->assertNotNull($request['magic_token_used_at'], 'Token should be marked as used');
        $this->assertNotNull($request['approved_at'], 'Approved timestamp should be set');
        
        // Verify subscription extended
        $gudang = Database::fetchOne(
            "SELECT subscription_until, status_langganan FROM gudang WHERE id = ?",
            [$this->testGudangId]
        );
        
        $this->assertEquals('active', $gudang['status_langganan'], 'Gudang should be active');
        $this->assertGreaterThan(time(), strtotime($gudang['subscription_until']), 'Subscription should be extended');
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
    }
    
    /**
     * Test approval with expired token
     */
    public function testApprovalWithExpiredToken(): void
    {
        // Create payment request with expired token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('-1 hour')); // Already expired
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'pending',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt
        ]);
        
        // Try to approve
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        
        // Should return error page (200 with error message)
        $this->assertEquals(200, $response['status'], 'Should return error page');
        $this->assertStringContainsString('kadaluarsa', strtolower($response['raw']), 'Should contain expired message');
        
        // Verify payment request is still pending
        $request = Database::fetchOne(
            "SELECT status FROM payment_requests WHERE id = ?",
            [$requestId]
        );
        
        $this->assertEquals('pending', $request['status'], 'Status should remain pending');
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
    }
    
    /**
     * Test approval with already used token
     */
    public function testApprovalWithUsedToken(): void
    {
        // Create payment request with used token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'approved',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt,
            'magic_token_used_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'approved_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]);
        
        // Try to approve again
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        
        // Should return error page
        $this->assertEquals(200, $response['status'], 'Should return error page');
        $this->assertStringContainsString('digunakan', strtolower($response['raw']), 'Should contain used message');
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
    }
    
    /**
     * Test approval with invalid token
     */
    public function testApprovalWithInvalidToken(): void
    {
        // Try to approve with random token
        $token = 'invalid_token_12345';
        
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        
        // Should return error page
        $this->assertEquals(200, $response['status'], 'Should return error page');
        $this->assertStringContainsString('tidak valid', strtolower($response['raw']), 'Should contain invalid message');
    }
    
    /**
     * Test approval with missing token
     */
    public function testApprovalWithMissingToken(): void
    {
        $response = $this->api('GET', '/saas/approve-payment');
        
        // Should return 400 error
        $this->assertError($response, 400, 'Should return 400 when token is missing');
    }
    
    /**
     * Test token is single-use only
     */
    public function testTokenIsSingleUse(): void
    {
        // Create payment request
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'pending',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt
        ]);
        
        // First approval - should succeed
        $response1 = $this->api('GET', '/saas/approve-payment?token=' . $token);
        $this->assertEquals(200, $response1['status'], 'First approval should succeed');
        
        // Second approval - should fail
        $response2 = $this->api('GET', '/saas/approve-payment?token=' . $token);
        $this->assertEquals(200, $response2['status'], 'Should return error page');
        $this->assertStringContainsString('digunakan', strtolower($response2['raw']), 'Should indicate token already used');
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
    }
    
    /**
     * Test notification created after approval
     */
    public function testNotificationCreatedAfterApproval(): void
    {
        // Create payment request
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'pending',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt
        ]);
        
        // Approve
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        $this->assertEquals(200, $response['status']);
        
        // Check notification created for Bos
        $notification = Database::fetchOne(
            "SELECT * FROM notifikasi 
             WHERE id_user = ? AND type = 'payment_approved' 
             ORDER BY created_at DESC LIMIT 1",
            [$this->testBosId]
        );
        
        $this->assertNotNull($notification, 'Notification should be created for Bos');
        $this->assertEquals('payment_approved', $notification['type']);
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
        if ($notification) {
            Database::query("DELETE FROM notifikasi WHERE id = ?", [$notification['id']]);
        }
    }
    
    /**
     * Test activity log created after approval
     */
    public function testActivityLogCreatedAfterApproval(): void
    {
        // Create payment request
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $requestId = Database::insert('payment_requests', [
            'id_gudang' => $this->testGudangId,
            'id_bos' => $this->testBosId,
            'nominal' => 500000,
            'bukti_transfer_path' => 'storage/uploads/payment_proofs/test.jpg',
            'status' => 'pending',
            'magic_token' => $token,
            'magic_token_expires_at' => $expiresAt
        ]);
        
        // Approve
        $response = $this->api('GET', '/saas/approve-payment?token=' . $token);
        $this->assertEquals(200, $response['status']);
        
        // Check activity log
        $log = Database::fetchOne(
            "SELECT * FROM activity_log 
             WHERE id_gudang = ? AND action = 'payment_approved' 
             ORDER BY created_at DESC LIMIT 1",
            [$this->testGudangId]
        );
        
        $this->assertNotNull($log, 'Activity log should be created');
        $this->assertEquals('payment_approved', $log['action']);
        
        // Cleanup
        Database::query("DELETE FROM payment_requests WHERE id = ?", [$requestId]);
        if ($log) {
            Database::query("DELETE FROM activity_log WHERE id = ?", [$log['id']]);
        }
    }
}
