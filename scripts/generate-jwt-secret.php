#!/usr/bin/env php
<?php

/**
 * Generate Strong JWT Secret
 * 
 * Usage: php scripts/generate-jwt-secret.php
 * 
 * This script generates a cryptographically secure random string
 * suitable for use as JWT_SECRET in production.
 */

echo "\n";
echo "🔐 JWT Secret Generator\n";
echo "========================\n\n";

// Generate 32 random bytes (64 hex characters)
$secret = bin2hex(random_bytes(32));

echo "Generated JWT Secret:\n";
echo "---------------------\n";
echo $secret . "\n\n";

echo "Add this to your .env file:\n";
echo "JWT_SECRET=" . $secret . "\n\n";

echo "⚠️  IMPORTANT:\n";
echo "- Keep this secret safe and never commit it to version control\n";
echo "- Use different secrets for development, staging, and production\n";
echo "- Changing this secret will invalidate all existing JWT tokens\n";
echo "- Store this secret securely (password manager, secrets vault)\n\n";

// Check if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    echo "📝 Your current .env file exists.\n";
    echo "   To update it, run:\n";
    echo "   sed -i 's/^JWT_SECRET=.*/JWT_SECRET=" . $secret . "/' .env\n\n";
    
    // Check if current secret is the default
    $envContent = file_get_contents(__DIR__ . '/../.env');
    if (strpos($envContent, 'JWT_SECRET=change-this-secret') !== false) {
        echo "⚠️  WARNING: Your .env file still has the default JWT secret!\n";
        echo "   This is a CRITICAL security risk in production.\n";
        echo "   Please update it immediately.\n\n";
    }
} else {
    echo "ℹ️  No .env file found. Copy .env.example to .env first:\n";
    echo "   cp .env.example .env\n\n";
}

echo "✅ Done!\n\n";
