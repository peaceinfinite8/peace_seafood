<?php

namespace Tests\Authorization;

use Tests\TestCase;

/**
 * Test Role-Based Access Control
 * - Test access to endpoints by different roles
 * - Test route guards
 * - Test 403 Forbidden responses
 */
class RoleAccessTest extends TestCase
{
    /**
     * Test SaaS Owner can access platform settings
     */
    public function testSaasOwnerCanAccessPlatformSettings(): void
    {
        $user = $this->getTestUser('saas_owner');
        if (!$user) {
            $this->markTestSkipped('No saas_owner user found in database');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/saas/settings', [], $token);
        
        $this->assertSuccess($response, 'SaaS Owner should access platform settings');
    }
    
    /**
     * Test Bos cannot access platform settings
     */
    public function testBosCannotAccessPlatformSettings(): void
    {
        $user = $this->getTestUser('bos');
        if (!$user) {
            $this->markTestSkipped('No bos user found in database');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/saas/settings', [], $token);
        
        $this->assertError($response, 403, 'Bos should not access platform settings');
    }
    
    /**
     * Test SaaS Owner can access centralized logs
     */
    public function testSaasOwnerCanAccessCentralizedLogs(): void
    {
        $user = $this->getTestUser('saas_owner');
        if (!$user) {
            $this->markTestSkipped('No saas_owner user found');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/saas/logs', [], $token);
        
        $this->assertSuccess($response, 'SaaS Owner should access centralized logs');
    }
    
    /**
     * Test Admin cannot access centralized logs
     */
    public function testAdminCannotAccessCentralizedLogs(): void
    {
        $user = $this->getTestUser('admin');
        if (!$user) {
            $this->markTestSkipped('No admin user found');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/saas/logs', [], $token);
        
        $this->assertError($response, 403, 'Admin should not access centralized logs');
    }
    
    /**
     * Test Bos can access their own gudang settings
     */
    public function testBosCanAccessOwnGudangSettings(): void
    {
        $user = $this->getTestUser('bos');
        if (!$user) {
            $this->markTestSkipped('No bos user found');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/settings', [], $token);
        
        $this->assertSuccess($response, 'Bos should access their settings');
    }
    
    /**
     * Test Admin can access dashboard
     */
    public function testAdminCanAccessDashboard(): void
    {
        $user = $this->getTestUser('admin');
        if (!$user) {
            $this->markTestSkipped('No admin user found');
            return;
        }
        
        $token = $this->generateToken($user);
        $response = $this->api('GET', '/dashboard', [], $token);
        
        $this->assertSuccess($response, 'Admin should access dashboard');
    }
    
    /**
     * Test Checker has limited access
     */
    public function testCheckerHasLimitedAccess(): void
    {
        $user = $this->getTestUser('checker');
        if (!$user) {
            $this->markTestSkipped('No checker user found');
            return;
        }
        
        $token = $this->generateToken($user);
        
        // Should access dashboard
        $dashboardResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($dashboardResponse, 'Checker should access dashboard');
        
        // Should NOT access settings
        $settingsResponse = $this->api('GET', '/settings', [], $token);
        $this->assertError($settingsResponse, 403, 'Checker should not access settings');
    }
    
    /**
     * Test Helper has minimal access
     */
    public function testHelperHasMinimalAccess(): void
    {
        $user = $this->getTestUser('helper');
        if (!$user) {
            $this->markTestSkipped('No helper user found');
            return;
        }
        
        $token = $this->generateToken($user);
        
        // Should access dashboard
        $dashboardResponse = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($dashboardResponse, 'Helper should access dashboard');
    }
    
    /**
     * Test Viewer has read-only access
     */
    public function testViewerHasReadOnlyAccess(): void
    {
        $user = $this->getTestUser('viewer');
        if (!$user) {
            $this->markTestSkipped('No viewer user found');
            return;
        }
        
        $token = $this->generateToken($user);
        
        // Should access dashboard (read)
        $response = $this->api('GET', '/dashboard', [], $token);
        $this->assertSuccess($response, 'Viewer should access dashboard for reading');
    }
}
