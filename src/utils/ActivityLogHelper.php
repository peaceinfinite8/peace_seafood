<?php

declare(strict_types=1);

namespace App\Utils;

use App\Middleware\AuthMiddleware;

class ActivityLogHelper
{
    /**
     * Catat log aktivitas mutasi data ke tabel activity_log
     */
    public static function log(string $action, string $tableName, int $recordId, mixed $before = null, mixed $after = null): void
    {
        try {
            // Dapatkan ID user yang login; pastikan > 0
            $idUser = (int) AuthMiddleware::getAuthUserId();
            if ($idUser <= 0) {
                $idUser = 1; // Default ke user ID 1 (system/admin) jika cron/background
            }

            // Bersihkan data array dari objek PDO agar json_encode lancar
            $beforeClean = is_array($before) ? self::cleanArray($before) : $before;
            $afterClean  = is_array($after) ? self::cleanArray($after) : $after;

            Database::insert('activity_log', [
                'id_user'      => $idUser,
                'action'       => strtoupper($action),
                'table_name'   => $tableName,
                'record_id'    => $recordId,
                'before_value' => $beforeClean ? json_encode($beforeClean) : null,
                'after_value'  => $afterClean ? json_encode($afterClean) : null,
            ]);
        } catch (\Throwable $e) {
            // Silent catch agar kegagalan logging tidak memblokir transaksi utama
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    /**
     * Helper untuk membuang index numerik dari fetch row
     */
    private static function cleanArray(array $arr): array
    {
        $res = [];
        foreach ($arr as $k => $v) {
            if (!is_numeric($k)) {
                $res[$k] = $v;
            }
        }
        return $res;
    }
}
