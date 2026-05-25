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
        return self::query($sql, $params)->fetch();
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function execute(string $sql, array $params = []): bool
    {
        return self::query($sql, $params)->rowCount() > 0;
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
        $stmt = self::getInstance()->prepare("INSERT INTO `{$table}` ({$cols}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Update rows matching condition
     */
    public static function update(string $table, array $data, string $condition, array $condParams = []): bool
    {
        $setClause = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $params    = array_merge(array_values($data), $condParams);
        $stmt = self::getInstance()->prepare("UPDATE `{$table}` SET {$setClause} WHERE {$condition}");
        $stmt->execute($params);
        return $stmt->rowCount() >= 0;
    }
}
