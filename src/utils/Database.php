<?php

declare(strict_types=1);

namespace App\Utils;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Database Singleton Wrapper
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $columnCache = [];

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = require __DIR__ . '/../../config/database.php';
        }
        return self::$instance;
    }

    public static function prepare(string $sql): PDOStatement
    {
        return self::getInstance()->prepare($sql);
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): array|false
    {
        try {
            return self::query($sql, $params)->fetch();
        } catch (PDOException $e) {
            error_log('Database::fetchOne error: ' . $e->getMessage() . " -- SQL: {$sql}");
            return false;
        }
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            return self::query($sql, $params)->fetchAll();
        } catch (PDOException $e) {
            error_log('Database::fetchAll error: ' . $e->getMessage() . " -- SQL: {$sql}");
            return [];
        }
    }

    public static function execute(string $sql, array $params = []): bool
    {
        try {
            return self::query($sql, $params)->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Database::execute error: ' . $e->getMessage() . " -- SQL: {$sql}");
            return false;
        }
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }

    /**
     * Insert a row and return new ID
     */
    public static function insert(string $table, array $data): int
    {
        $cols        = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        try {
            $stmt = self::getInstance()->prepare("INSERT INTO `{$table}` ({$cols}) VALUES ({$placeholders})");
            $stmt->execute(array_values($data));
            return (int) self::getInstance()->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database::insert error: ' . $e->getMessage() . " -- TABLE: {$table}");
            return 0;
        }
    }

    /**
     * Update rows matching condition
     */
    public static function update(string $table, array $data, string $condition, array $condParams = []): bool
    {
        $setClause = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $params    = array_merge(array_values($data), $condParams);
        try {
            $stmt = self::getInstance()->prepare("UPDATE `{$table}` SET {$setClause} WHERE {$condition}");
            $stmt->execute($params);
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            error_log('Database::update error: ' . $e->getMessage() . " -- TABLE: {$table}");
            return false;
        }
    }

    /**
     * Check whether a column exists in the current database schema.
     * Cached to avoid repeated INFORMATION_SCHEMA lookups.
     */
    public static function hasColumn(string $table, string $column): bool
    {
        $key = strtolower($table . '.' . $column);

        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }

        $row = self::fetchOne(
            "SELECT COUNT(*) as cnt
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return self::$columnCache[$key] = ((int)($row['cnt'] ?? 0)) > 0;
    }

    /**
     * Check whether a table exists in the current database schema.
     */
    public static function hasTable(string $table): bool
    {
        $key = strtolower('table.' . $table);

        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }

        $row = self::fetchOne(
            "SELECT COUNT(*) as cnt
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?",
            [$table]
        );

        return self::$columnCache[$key] = ((int)($row['cnt'] ?? 0)) > 0;
    }
}
