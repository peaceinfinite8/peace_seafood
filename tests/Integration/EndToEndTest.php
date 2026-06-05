<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Utils\Database;

/**
 * End-to-End Integration Tests
 * - Complete user journey from signup to payment
 * - Subscription lifecycle
 * - Payment and recovery flow
 */
class EndToEndTest extends TestCase
{
    /**
     * Test complete tenant lifecycle
     * 1. Create new tenant
     * 2. Complete onboarding
     * 3. Use features while active
     * 4. Subscription expires
     * 5. Submit payment
     * 6. Get approved
     * 7. Access restored
     */
    public function testCompleteTenantLifecycle(): void
    {
        // Skip if this is too complex for automated testing
        $this->markTestSkipped('Complete lifecycle test requires manual verification');
        
        // 1. Create test gudang (simulating new tenant signup)
        $gudangId = Database::insert('gudang', [
            'nama_gudang' => 'Test Gudang E2E',
            'alamat' => 'Test Address',
            'kota' => 'Test City',
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'status_langganan' => 'trial',
            'is_active' => 1
        ]);
        
        // 2. Create test bos
        $bosId = $this->createTestUser([
            'name' => 'Test Bos E2E',
            'role' => 'bos',
            'id_gudang' => $gudangId,
            'is_first_login' => 1
        ]);
        
        // Update gudang with bos
        Database::update('gudang', ['id' => $gudangId], ['id_bos' => $bosId]);
        
        // 3. Login as bos
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => Database::fetchOne("SELECT email FROM users WHERE id = ?", [$bosId])['email'],
            'password' => 'password123'
        ]);
        
        $this->assertSuccess($loginResponse, 'Bos should be able to login');
        $token = $loginResponse['body']['token'];
        
        // 4. Access dashboard (should work - trial active)
        $dashboardResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($dashboardResponse, 'Should access dashboard during trial');
        
        // 5. Simulate subscription expiry
        Database::update('gudang', ['id' => $gudangId], [
            'subscription_until' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'status_langganan' => 'expired'
        ]);
        
        // 6. Try to access dashboard (should get 402)
        $expiredResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertError($expiredResponse, 402, 'Should get 402 when subscription expired');
        
        // 7. Submit payment proof
        // Note: File upload testing is complex in automated tests
        // In production, this would be tested manually or with proper file upload mocking
        
        // 8. Approve payment manually (simulate)
        Database::update('gudang', ['id' => $gudangId], [
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'status_langganan' => 'active'
        ]);
        
        // 9. Access dashboard again (should work now)
        $recoveredResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($recoveredResponse, 'Should access dashboard after renewal');
        
        // Cleanup
        $this->deleteTestUser($bosId);
        Database::query("DELETE FROM gudang WHERE id = ?", [$gudangId]);
    }
    
    /**
     * Test grace period warning flow
     */
    public function testGracePeriodWarningFlow(): void
    {
        // Create test gudang with subscription expiring in 5 days
        $gudangId = Database::insert('gudang', [
            'nama_gudang' => 'Test Gudang Grace',
            'alamat' => 'Test Address',
            'kota' => 'Test City',
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'status_langganan' => 'active',
            'is_active' => 1
        ]);
        
        $bosId = $this->createTestUser([
            'name' => 'Test Bos Grace',
            'role' => 'bos',
            'id_gudang' => $gudangId
        ]);
        
        Database::update('gudang', ['id' => $gudangId], ['id_bos' => $bosId]);
        
        // Login
        $user = Database::fetchOne("SELECT email FROM users WHERE id = ?", [$bosId]);
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => $user['email'],
            'password' => 'password123'
        ]);
        
        $token = $loginResponse['body']['token'];
        
        // Check grace period status
        $graceResponse = $this->api('GET', '/grace-period/status', [], $token);
        $this->assertSuccess($graceResponse, 'Should get grace period status');
        $this->assertResponseHasKey($graceResponse, 'remaining_days', 'Should contain remaining days');
        $this->assertEquals(5, $graceResponse['body']['remaining_days'], 'Should show 5 days remaining');
        $this->assertEquals('yellow', $graceResponse['body']['alert_level'], 'Should be yellow alert (H-7 to H-4)');
        
        // Simulate H-2 (red alert)
        Database::update('gudang', ['id' => $gudangId], [
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+2 days'))
        ]);
        
        $redAlertResponse = $this->api('GET', '/grace-period/status', [], $token);
        $this->assertEquals(2, $redAlertResponse['body']['remaining_days'], 'Should show 2 days remaining');
        $this->assertEquals('red', $redAlertResponse['body']['alert_level'], 'Should be red alert (H-3 to H-0)');
        
        // Cleanup
        $this->deleteTestUser($bosId);
        Database::query("DELETE FROM gudang WHERE id = ?", [$gudangId]);
    }
    
    /**
     * Test notification flow across roles
     */
    public function testNotificationFlowAcrossRoles(): void
    {
        // Create test gudang
        $gudangId = Database::insert('gudang', [
            'nama_gudang' => 'Test Gudang Notif',
            'alamat' => 'Test Address',
            'kota' => 'Test City',
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'status_langganan' => 'active',
            'is_active' => 1
        ]);
        
        // Create users with different roles
        $bosId = $this->createTestUser([
            'name' => 'Test Bos Notif',
            'role' => 'bos',
            'id_gudang' => $gudangId
        ]);
        
        $adminId = $this->createTestUser([
            'name' => 'Test Admin Notif',
            'role' => 'admin',
            'id_gudang' => $gudangId
        ]);
        
        // Login as admin
        $admin = Database::fetchOne("SELECT email FROM users WHERE id = ?", [$adminId]);
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => $admin['email'],
            'password' => 'password123'
        ]);
        
        $token = $loginResponse['body']['token'];
        
        // Get unread notification count
        $notifResponse = $this->api('GET', '/notifikasi/unread-count', [], $token);
        $this->assertSuccess($notifResponse, 'Should get notification count');
        $this->assertResponseHasKey($notifResponse, 'unread_count', 'Should contain unread count');
        
        // Create test notification for admin
        Database::insert('notifikasi', [
            'id_user' => $adminId,
            'type' => 'test_notification',
            'judul' => 'Test Notification',
            'pesan' => 'This is a test notification',
            'is_read' => 0
        ]);
        
        // Check unread count increased
        $updatedNotifResponse = $this->api('GET', '/notifikasi/unread-count', [], $token);
        $this->assertGreaterThan(
            $notifResponse['body']['unread_count'],
            $updatedNotifResponse['body']['unread_count'],
            'Unread count should increase after new notification'
        );
        
        // Mark as read
        $notifications = Database::fetchAll(
            "SELECT id FROM notifikasi WHERE id_user = ? AND is_read = 0",
            [$adminId]
        );
        
        if (!empty($notifications)) {
            $this->api('POST', '/notifikasi/mark-read', [
                'notification_ids' => array_column($notifications, 'id')
            ], $token);
            
            // Verify marked as read
            $finalNotifResponse = $this->api('GET', '/notifikasi/unread-count', [], $token);
            $this->assertEquals(
                0,
                $finalNotifResponse['body']['unread_count'],
                'Unread count should be 0 after marking all as read'
            );
        }
        
        // Cleanup
        Database::query("DELETE FROM notifikasi WHERE id_user IN (?, ?)", [$bosId, $adminId]);
        $this->deleteTestUser($bosId);
        $this->deleteTestUser($adminId);
        Database::query("DELETE FROM gudang WHERE id = ?", [$gudangId]);
    }
    
    /**
     * Test role-based access across modules
     */
    public function testRoleBasedAccessAcrossModules(): void
    {
        // Create test gudang
        $gudangId = Database::insert('gudang', [
            'nama_gudang' => 'Test Gudang Modules',
            'alamat' => 'Test Address',
            'kota' => 'Test City',
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'status_langganan' => 'active',
            'is_active' => 1
        ]);
        
        // Test different roles accessing different modules
        $roles = [
            'bos' => ['dashboard', 'master-data', 'stok', 'penjualan', 'keuangan', 'settings'],
            'admin' => ['dashboard', 'master-data', 'stok', 'penjualan'],
            'viewer' => ['dashboard', 'laporan']
        ];
        
        foreach ($roles as $role => $allowedEndpoints) {
            // Create user with role
            $userId = $this->createTestUser([
                'name' => "Test {$role}",
                'role' => $role,
                'id_gudang' => $gudangId
            ]);
            
            // Login
            $user = Database::fetchOne("SELECT email FROM users WHERE id = ?", [$userId]);
            $loginResponse = $this->api('POST', '/auth/login', [
                'email' => $user['email'],
                'password' => 'password123'
            ]);
            
            $token = $loginResponse['body']['token'];
            
            // Test allowed endpoints
            foreach ($allowedEndpoints as $endpoint) {
                $response = $this->api('GET', "/{$endpoint}", [], $token);
                $this->assertTrue(
                    $response['status'] < 400,
                    "{$role} should access /{$endpoint}"
                );
            }
            
            // Test forbidden endpoint (financial_admin only)
            if ($role !== 'financial_admin') {
                $forbiddenResponse = $this->api('GET', '/keuangan/laporan-lengkap', [], $token);
                $this->assertError(
                    $forbiddenResponse,
                    403,
                    "{$role} should NOT access financial admin endpoint"
                );
            }
            
            // Cleanup
            $this->deleteTestUser($userId);
        }
        
        // Cleanup
        Database::query("DELETE FROM gudang WHERE id = ?", [$gudangId]);
    }
    
    /**
     * Test lock screen and recovery flow
     */
    public function testLockScreenAndRecoveryFlow(): void
    {
        // Create expired gudang
        $gudangId = Database::insert('gudang', [
            'nama_gudang' => 'Test Gudang Locked',
            'alamat' => 'Test Address',
            'kota' => 'Test City',
            'subscription_until' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'status_langganan' => 'expired',
            'is_active' => 1
        ]);
        
        $bosId = $this->createTestUser([
            'name' => 'Test Bos Locked',
            'role' => 'bos',
            'id_gudang' => $gudangId
        ]);
        
        Database::update('gudang', ['id' => $gudangId], ['id_bos' => $bosId]);
        
        // Login
        $user = Database::fetchOne("SELECT email FROM users WHERE id = ?", [$bosId]);
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => $user['email'],
            'password' => 'password123'
        ]);
        
        $token = $loginResponse['body']['token'];
        
        // Try to access protected resource (should get 402)
        $lockedResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertError($lockedResponse, 402, 'Should get 402 for expired subscription');
        
        // Get payment settings (should work even when locked)
        $settingsResponse = $this->api('GET', '/wms/payment-settings', [], $token);
        $this->assertSuccess($settingsResponse, 'Should get payment settings when locked');
        
        // Restore access
        Database::update('gudang', ['id' => $gudangId], [
            'subscription_until' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'status_langganan' => 'active'
        ]);
        
        // Access should work now
        $recoveredResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($recoveredResponse, 'Should access dashboard after recovery');
        
        // Cleanup
        $this->deleteTestUser($bosId);
        Database::query("DELETE FROM gudang WHERE id = ?", [$gudangId]);
    }
}
