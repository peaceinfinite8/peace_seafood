<?php

declare(strict_types=1);

/**
 * Peace Seafood Database Migration & Seed Runner
 * Run via terminal: php database/run_setup.php
 */

define('BASE_PATH', dirname(__DIR__));

echo "=== PEACE SEAFOOD: MIGRATION & SEED RUNNER ===\n";

// 1. Parse .env file manually
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Error: .env file not found at: {$envFile}\n");
}

echo "Reading environment configurations...\n";
$envVars = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $val = trim($parts[1], " \t\n\r\0\x0B\"'");
        $envVars[$key] = $val;
    }
}

$host = $envVars['DB_HOST'] ?? '127.0.0.1';
$port = $envVars['DB_PORT'] ?? '3306';
$database = $envVars['DB_NAME'] ?? 'peace_seafood';
$user = $envVars['DB_USER'] ?? 'root';
$password = $envVars['DB_PASSWORD'] ?? '';

echo "Connecting to database `{$database}` on `{$host}:{$port}` as `{$user}`...\n";
$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

try {
    // Connect without dbname first to create it if it doesn't exist
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    // Ensure database exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$database}`");
    echo "Connected successfully to database.\n";

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . "\n");
}

// 2. Scan and Apply All Migrations Statement-by-Statement in Order
$migrationsDir = BASE_PATH . '/database/migrations';
if (is_dir($migrationsDir)) {
    echo "Scanning migrations directory for SQL files...\n";
    $files = glob($migrationsDir . '/*.sql');
    
    // Sort files chronologically by filename
    sort($files);
    
    foreach ($files as $file) {
        $filename = basename($file);
        echo "Applying Migration: {$filename}...\n";
        
        $sqlContent = file_get_contents($file);
        
        // Remove SQL comments
        $sqlContent = preg_replace('/--.*\n/', '', $sqlContent);
        $sqlContent = preg_replace('/\/*.*?\*\//', '', $sqlContent);
        
        // Split by semicolon
        $statements = explode(';', $sqlContent);
        
        $successCount = 0;
        $skipCount = 0;
        $failCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                $pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (
                    strpos($msg, 'Duplicate column') !== false || 
                    strpos($msg, 'already exists') !== false || 
                    strpos($msg, 'Duplicate key') !== false ||
                    strpos($msg, 'already exist') !== false ||
                    strpos($msg, 'Duplicate entry') !== false
                ) {
                    $skipCount++;
                } else {
                    echo "  [ERROR] Statement: " . substr($statement, 0, 50) . "...\n";
                    echo "  [REASON] " . $msg . "\n";
                    $failCount++;
                }
            }
        }
        
        echo "-> Finished {$filename}: {$successCount} succeeded, {$skipCount} skipped/already applied, {$failCount} failed.\n";
        if ($failCount > 0) {
            echo "-> Warning: Some statements failed. Please check the logs above.\n";
        }
    }
} else {
    echo "Warning: Migrations directory not found at {$migrationsDir}\n";
}

// 3. Run Seeder
$seederFile = BASE_PATH . '/database/seeders/seeder.sql';
if (file_exists($seederFile)) {
    echo "Seeding premium dummy data (Cakalang, Tongkol, Salem, Bandeng)...\n";
    $sql = file_get_contents($seederFile);
    try {
        $pdo->exec($sql);
        echo "Seeding completed successfully!\n";
    } catch (PDOException $e) {
        die("Seeder Error: " . $e->getMessage() . "\n");
    }
} else {
    echo "Warning: Seeder file not found at {$seederFile}\n";
}

echo "=== DATABASE SETUP COMPLETED SUCCESSFULLY ===\n";
