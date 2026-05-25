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

    public static function update(int $id, array $data): bool
    {
        $sets = implode(' = ?, ', array_map(fn($k) => "`{$k}`", array_keys($data))) . ' = ?';

        return Database::execute(
            "UPDATE `" . static::$table . "` SET {$sets} WHERE `" . static::$pk . "` = ?",
            [...array_values($data), $id]
        );
    }

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
}
