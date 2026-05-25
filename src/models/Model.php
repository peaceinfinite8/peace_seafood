<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;
use PDOStatement;

/**
 * Base Model
 */
abstract class Model
{
    protected static string $table  = '';
    protected static string $pk     = 'id';

    protected \PDO $db;

    public function __construct()
    {
        $this->db = \App\Utils\Database::getInstance();
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne(
            "SELECT * FROM `" . static::$table . "` WHERE `" . static::$pk . "` = ?",
            [$id]
        );
    }

    public static function findOrFail(int $id): array
    {
        $row = static::find($id);
        if (!$row) {
            throw new \RuntimeException(ucfirst(static::$table) . " dengan ID {$id} tidak ditemukan");
        }
        return $row;
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM `" . static::$table . "` ORDER BY {$orderBy}"
        );
    }

    public static function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM `" . static::$table . "` WHERE `{$column}` = ? ORDER BY {$orderBy}",
            [$value]
        );
    }

    public static function create(array $data): int
    {
        $columns      = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        Database::query(
            "INSERT INTO `" . static::$table . "` (`{$columns}`) VALUES ({$placeholders})",
            array_values($data)
        );

        return (int) Database::lastInsertId();
    }

    // removed static update to avoid duplicate method name with instance API

    public static function delete(int $id): bool
    {
        return Database::execute(
            "DELETE FROM `" . static::$table . "` WHERE `" . static::$pk . "` = ?",
            [$id]
        );
    }

    public static function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM `" . static::$table . "`";
        if ($where) $sql .= " WHERE {$where}";
        return (int)(Database::fetchOne($sql, $params)['cnt'] ?? 0);
    }

    public static function exists(string $column, mixed $value, int $excludeId = 0): bool
    {
        $sql    = "SELECT COUNT(*) as cnt FROM `" . static::$table . "` WHERE `{$column}` = ?";
        $params = [$value];

        if ($excludeId > 0) {
            $sql    .= " AND `" . static::$pk . "` != ?";
            $params[] = $excludeId;
        }

        return (int)(Database::fetchOne($sql, $params)['cnt'] ?? 0) > 0;
    }

    // -----------------
    // Instance compatibility helpers
    // -----------------

    public static function insert(array $data): int
    {
        return static::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $condition = "`" . static::$pk . "` = ?";
        return Database::update(static::$table, $data, $condition, [$id]);
    }

    public static function findById(int $id): array|false
    {
        return static::find($id);
    }

    public function findAll(array $where = [], string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM `" . static::$table . "`";
        $params = [];

        if (!empty($where)) {
            $clauses = [];
            foreach ($where as $col => $val) {
                $clauses[] = "`{$col}` = ?";
                $params[] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        $sql .= " ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findActive(string $orderBy = 'id DESC'): array
    {
        return $this->findAll(['is_active' => 1], $orderBy);
    }
}
