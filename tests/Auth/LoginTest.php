<?php

namespace Tests\Auth;

use Tests\TestCase;

/**
 * Test Authentication Flow
 * - Login with valid credentials
 * - Login with invalid credentials
 * - JWT token generation
 * - Token verification
 */
class LoginTest extends TestCase
{
    /**
     * Test successful login with valid credentials
     */
    public function testLoginWithValidCredentials(): void
    {
        $response = $this->api('POST', '/auth/login', [
            'email' => 'saas@peaceseafood.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccess($response, 'Login should succeed with valid credentials');
        $this->assertResponseHasKey($response, 'token', 'Response should contain token');
        $this->assertResponseHasKey($response, 'user', 'Response should contain user data');
        
        // Verify token is not empty
        $this->assertNotEmpty($response['body']['token'], 'Token should not be empty');
        
        // Verify user data structure
        $user = $response['body']['user'];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('role', $user);
        $this->assertArrayHasKey('name', $user);
    }
    
    /**
     * Test login with invalid email
     */
    public function testLoginWithInvalidEmail(): void
    {
        $response = $this->api('POST', '/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertError($response, 401, 'Login should fail with invalid email');
    }
    
    /**
     * Test login with wrong password
     */
    public function testLoginWithWrongPassword(): void
    {
        $response = $this->api('POST', '/auth/login', [
            'email' => 'saas@peaceseafood.com',
            'password' => 'wrongpassword'
        ]);
        
        $this->assertError($response, 401, 'Login should fail with wrong password');
    }
    
    /**
     * Test login with missing fields
     */
    public function testLoginWithMissingFields(): void
    {
        $response = $this->api('POST', '/auth/login', [
            'email' => 'saas@peaceseafood.com'
            // password missing
        ]);
        
        $this->assertError($response, null, 'Login should fail with missing password');
    }
    
    /**
     * Test accessing protected endpoint with valid token
     */
    public function testAccessProtectedEndpointWithValidToken(): void
    {
        // First login to get token
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => 'saas@peaceseafood.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccess($loginResponse);
        $token = $loginResponse['body']['token'];
        
        // Access protected endpoint
        $response = $this->api('GET', '/auth/profile', [], $token);
        
        $this->assertSuccess($response, 'Should access protected endpoint with valid token');
        $this->assertResponseHasKey($response, 'user', 'Profile response should contain user data');
    }
    
    /**
     * Test accessing protected endpoint without token
     */
    public function testAccessProtectedEndpointWithoutToken(): void
    {
        $response = $this->api('GET', '/auth/profile');
        
        $this->assertError($response, 401, 'Should return 401 without token');
    }
    
    /**
     * Test accessing protected endpoint with invalid token
     */
    public function testAccessProtectedEndpointWithInvalidToken(): void
    {
        $response = $this->api('GET', '/auth/profile', [], 'invalid.token.here');
        
        $this->assertError($response, 401, 'Should return 401 with invalid token');
    }
    
    /**
     * Test logout
     */
    public function testLogout(): void
    {
        // First login
        $loginResponse = $this->api('POST', '/auth/login', [
            'email' => 'saas@peaceseafood.com',
            'password' => 'password123'
        ]);
        
        $token = $loginResponse['body']['token'];
        
        // Logout
        $response = $this->api('POST', '/auth/logout', [], $token);
        
        $this->assertSuccess($response, 'Logout should succeed');
    }
}
