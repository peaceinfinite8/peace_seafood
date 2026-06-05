<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;

/**
 * API Version Middleware
 * 
 * Handles API versioning for backward compatibility
 * Supports version in URL path: /api/v1/endpoint
 * 
 * Current version: v1
 * Supported versions: v1
 */
class ApiVersionMiddleware
{
    const CURRENT_VERSION = 'v1';
    const SUPPORTED_VERSIONS = ['v1'];
    const DEFAULT_VERSION = 'v1';
    
    private static ?string $currentVersion = null;
    
    /**
     * Extract and validate API version from URI
     * 
     * @param string $uri Current request URI
     * @return string Validated version or default
     */
    public static function handle(string $uri): string
    {
        // Extract version from URI: /api/v1/endpoint -> v1
        if (preg_match('#^/api/(v\d+)/#', $uri, $matches)) {
            $version = $matches[1];
            
            // Validate version
            if (in_array($version, self::SUPPORTED_VERSIONS, true)) {
                self::$currentVersion = $version;
                return $version;
            }
            
            // Unsupported version
            Response::json([
                'success' => false,
                'message' => "API version '{$version}' is not supported",
                'supported_versions' => self::SUPPORTED_VERSIONS,
                'current_version' => self::CURRENT_VERSION,
            ], 400);
            exit;
        }
        
        // No version in URI, use default (for backward compatibility)
        self::$currentVersion = self::DEFAULT_VERSION;
        return self::DEFAULT_VERSION;
    }
    
    /**
     * Get current API version
     * 
     * @return string
     */
    public static function getVersion(): string
    {
        return self::$currentVersion ?? self::DEFAULT_VERSION;
    }
    
    /**
     * Check if version is supported
     * 
     * @param string $version
     * @return bool
     */
    public static function isSupported(string $version): bool
    {
        return in_array($version, self::SUPPORTED_VERSIONS, true);
    }
    
    /**
     * Add version info to response headers
     * 
     * @return void
     */
    public static function addVersionHeaders(): void
    {
        $version = self::getVersion();
        
        header("X-API-Version: {$version}");
        header("X-API-Current-Version: " . self::CURRENT_VERSION);
        header("X-API-Supported-Versions: " . implode(', ', self::SUPPORTED_VERSIONS));
    }
    
    /**
     * Strip version from URI for routing
     * 
     * Converts /api/v1/auth/login -> /api/auth/login
     * 
     * @param string $uri
     * @return string
     */
    public static function stripVersion(string $uri): string
    {
        return preg_replace('#^/api/v\d+/#', '/api/', $uri);
    }
    
    /**
     * Get version-specific behavior
     * 
     * Use this for version-specific logic:
     * if (ApiVersionMiddleware::isVersion('v2')) {
     *     // v2 behavior
     * } else {
     *     // v1 behavior
     * }
     * 
     * @param string $version
     * @return bool
     */
    public static function isVersion(string $version): bool
    {
        return self::getVersion() === $version;
    }
    
    /**
     * Get deprecation notice for version
     * 
     * @param string $version
     * @return string|null
     */
    public static function getDeprecationNotice(string $version): ?string
    {
        $deprecations = [
            // Future deprecations can be added here
            // 'v1' => 'API v1 will be deprecated on 2027-01-01. Please migrate to v2.',
        ];
        
        return $deprecations[$version] ?? null;
    }
    
    /**
     * Add deprecation warning to response if applicable
     * 
     * @return void
     */
    public static function addDeprecationWarning(): void
    {
        $version = self::getVersion();
        $notice = self::getDeprecationNotice($version);
        
        if ($notice) {
            header("X-API-Deprecation-Warning: {$notice}");
        }
    }
}
