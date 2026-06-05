<?php

/**
 * PHPUnit Bootstrap File
 * Sets up test environment with SEPARATE TEST DATABASE
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Set test environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';

// CRITICAL: Use test database to avoid corrupting production data
$_ENV['DB_NAME'] = 'peace_seafood_test';

// Disable email sending in tests
$_ENV['EMAIL_QUEUE_ENABLED'] = 'false';

// Initialize test database connection
try {
    require_once BASE_PATH . '/config/database.php';
    
    $testDb = $_ENV['DB_NAME'];
    $conn = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$testDb};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Verify we're using test database
    $result = $conn->query("SELECT DATABASE() as db")->fetch();
    if ($result['db'] !== $testDb) {
        throw new Exception("Not using test database! Using: {$result['db']}");
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  ✓ Test Environment Initialized\n";
    echo "  Database: {$testDb}\n";
    echo "  Base Path: " . BASE_PATH . "\n";
    echo "  ⚠️  This database may be modified during tests\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "❌ ERROR: Could not connect to test database\n";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "To setup test database:\n";
    echo "1. CREATE DATABASE: mysql -u root -p -e \"CREATE DATABASE peace_seafood_test\"\n";
    echo "2. IMPORT SCHEMA:   mysql -u root -p peace_seafood_test < database/schema.sql\n";
    echo "3. RUN MIGRATIONS:  find database/migrations -name '*.sql' -exec mysql -u root -p peace_seafood_test < {} \\;\n";
    echo "4. IMPORT SEEDERS:  mysql -u root -p peace_seafood_test < database/seeders/seeder.sql\n\n";
    exit(1);
}
