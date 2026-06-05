<?php

namespace Tests\Subscription;

use Tests\TestCase;
use App\Utils\Database;

/**
 * Test Subscription and Billing
 * - Test expired subscription returns 402
 * - Test active subscription allows access
 * - Test grace period
 */
class SubscriptionTest extends TestCase
{
    /**
     * Test expired subscription returns 402 Payment Required
     */
    public function testExpiredSubscriptionReturns402(): void
    {
        // Get a test user (bos)
        $user = $this->getTestUser('bos');
        if (!$user || !$user['id_gudang']) {
            $this->markTestSkipped('No bos user with gudang found');
            return;
        }
        
        // Store original subscription date
        $gudang = Database::fetchOne(
            "SELECT subscription_until FROM gudang WHERE id = ?",
            [$user['id_gudang']]
        );
        $originalDate = $gudang['subscription_until'];
        
        try {
            // Set subscription to expired (yesterday)
            Database::update('gudang', ['id' => $user['id_gudang']], [
                'subscription_until' => date('Y-m-d', strtotime('-1 day')),
                'status_langganan' => 'expired'
            ]);
            
            $token = $this->generateToken($user);
            
            // Try to access dashboard (should fail with 402)
            $response = $this->api('GET', '/dashboard', [], $token);
            
            $this->assertError($response, 402, 'Expired subscription should return 402');
            
        } finally {
            // Restore original date
            if ($originalDate) {
                Database::update('gudang', ['id' => $user['id_gudang']], [
                    'subscription_until' => $originalDate,
                    'status_langganan' => 'active'
                ]);
            }
        }
    }
    
    /**
     * Test active subscription allows access
     */
    public function testActiveSubscriptionAllowsAccess(): void
    {
        $user = $this->getTestUser('bos');
        if (!$user || !$user['id_gudang']) {
            $this->markTestSkipped('No bos user with gudang found');
            return;
        }
        
        // Ensure subscription is active (30 days from now)
        Database::update('gudang', ['id' => $user['id_gudang']], [
            'subscription_until' => date('Y-m-d', strtotime('+30 days')),
            'status_langganan' => 'active'
        ]);
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/dashboard', [], $token);
        
        $this->assertSuccess($response, 'Active subscription should allow access');
    }
    
    /**
     * Test suspended gudang returns 402
     */
    public function testSuspendedGudangReturns402(): void
    {
        $user = $this->getTestUser('bos');
        if (!$user || !$user['id_gudang']) {
            $this->markTestSkipped('No bos user with gudang found');
            return;
        }
        
        // Store original status
        $gudang = Database::fetchOne(
            "SELECT status_langganan FROM gudang WHERE id = ?",
            [$user['id_gudang']]
        );
        $originalStatus = $gudang['status_langganan'];
        
        try {
            // Suspend gudang
            Database::update('gudang', ['id' => $user['id_gudang']], [
                'status_langganan' => 'suspend'
            ]);
            
            $token = $this->generateToken($user);
            $response = $this->api('GET', '/dashboard', [], $token);
            
            $this->assertError($response, 402, 'Suspended gudang should return 402');
            
        } finally {
            // Restore original status
            if ($originalStatus) {
                Database::update('gudang', ['id' => $user['id_gudang']], [
                    'status_langganan' => $originalStatus
                ]);
            }
        }
    }
    
    /**
     * Test SaaS Owner and Super Admin bypass subscription check
     */
    public function testSaasOwnerBypassesSubscriptionCheck(): void
    {
        $user = $this->getTestUser('saas_owner');
        if (!$user) {
            $this->markTestSkipped('No saas_owner user found');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/dashboard', [], $token);
        
        $this->assertSuccess($response, 'SaaS Owner should bypass subscription check');
    }
    
    /**
     * Test grace period warning (within 7 days of expiry)
     */
    public function testGracePeriodWithin7Days(): void
    {
        $user = $this->getTestUser('bos');
        if (!$user || !$user['id_gudang']) {
            $this->markTestSkipped('No bos user with gudang found');
            return;
        }
        
        // Store original date
        $gudang = Database::fetchOne(
            "SELECT subscription_until FROM gudang WHERE id = ?",
            [$user['id_gudang']]
        );
        $originalDate = $gudang['subscription_until'];
        
        try {
            // Set subscription to expire in 5 days (grace period)
            Database::update('gudang', ['id' => $user['id_gudang']], [
                'subscription_until' => date('Y-m-d', strtotime('+5 days')),
                'status_langganan' => 'active'
            ]);
            
            $token = $this->generateToken($user);
            
            // Get grace period banner data
            $response = $this->api('GET', '/grace-period/banner', [], $token);
            
            $this->assertSuccess($response, 'Should get grace period banner data');
            
            if ($response['body']) {
                $banner = $response['body'];
                $this->assertArrayHasKey('show_banner', $banner);
                $this->assertTrue($banner['show_banner'], 'Banner should show in grace period');
                $this->assertArrayHasKey('days_remaining', $banner);
                $this->assertEquals(5, $banner['days_remaining'], 'Days remaining should be 5');
            }
            
        } finally {
            // Restore original date
            if ($originalDate) {
                Database::update('gudang', ['id' => $user['id_gudang']], [
                    'subscription_until' => $originalDate
                ]);
            }
        }
    }
}
