<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Utils\JWT;
use App\Utils\Database;

/**
 * Base Test Case
 * Provides common utilities for all tests
 */
abstract class TestCase extends BaseTestCase
{
    protected static $baseUrl = 'http://localhost/peace_seafood';
    protected static $apiUrl = 'http://localhost/peace_seafood/api';
    
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Teardown after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * Generate JWT token for testing
     */
    protected function generateToken(array $userData): string
    {
        return JWT::generate([
            'id' => $userData['id'] ?? 1,
            'user_id' => $userData['id'] ?? 1,
            'email' => $userData['email'] ?? 'test@example.com',
            'role' => $userData['role'] ?? 'admin',
            'id_gudang' => $userData['id_gudang'] ?? 1
        ]);
    }
    
    /**
     * Make HTTP request
     */
    protected function makeRequest(string $method, string $url, array $data = [], ?string $token = null): array
    {
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
            // Also set cookie for web routes
            $headers[] = 'Cookie: token=' . $token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }
        
        return [
            'status' => $httpCode,
            'body' => $response ? json_decode($response, true) : null,
            'raw' => $response
        ];
    }
    
    /**
     * Make API request
     */
    protected function api(string $method, string $endpoint, array $data = [], ?string $token = null): array
    {
        $url = self::$apiUrl . $endpoint;
        return $this->makeRequest($method, $url, $data, $token);
    }
    
    /**
     * Make web request
     */
    protected function web(string $method, string $path, array $data = [], ?string $token = null): array
    {
        $url = self::$baseUrl . $path;
        return $this->makeRequest($method, $url, $data, $token);
    }
    
    /**
     * Get test user by role
     */
    protected function getTestUser(string $role): ?array
    {
        try {
            return Database::fetchOne(
                "SELECT id, name, email, role, id_gudang FROM users WHERE role = ? AND is_active = 1 LIMIT 1",
                [$role]
            );
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Create test user
     */
    protected function createTestUser(array $data): int
    {
        return Database::insert('users', array_merge([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'admin',
            'is_active' => 1,
            'is_first_login' => 0
        ], $data));
    }
    
    /**
     * Delete test user
     */
    protected function deleteTestUser(int $userId): void
    {
        Database::query("DELETE FROM users WHERE id = ?", [$userId]);
    }
    
    /**
     * Get test gudang
     */
    protected function getTestGudang(): ?array
    {
        try {
            return Database::fetchOne(
                "SELECT * FROM gudang WHERE is_active = 1 LIMIT 1"
            );
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Assert response is successful (2xx)
     */
    protected function assertSuccess(array $response, string $message = ''): void
    {
        $this->assertGreaterThanOrEqual(200, $response['status'], $message);
        $this->assertLessThan(300, $response['status'], $message);
    }
    
    /**
     * Assert response is error (4xx or 5xx)
     */
    protected function assertError(array $response, int $expectedStatus = null, string $message = ''): void
    {
        if ($expectedStatus) {
            $this->assertEquals($expectedStatus, $response['status'], $message);
        } else {
            $this->assertGreaterThanOrEqual(400, $response['status'], $message);
        }
    }
    
    /**
     * Assert response contains key
     */
    protected function assertResponseHasKey(array $response, string $key, string $message = ''): void
    {
        $this->assertIsArray($response['body'], 'Response body should be array');
        $this->assertArrayHasKey($key, $response['body'], $message);
    }
}
