<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Standard JSON Response Helper
 */
class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Berhasil', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Data berhasil dibuat'): never
    {
        self::success($data, $message, 201);
    }

    public static function error(
        int $status,
        string $errorCode,
        string $message,
        array $errors = []
    ): never {
        self::json([
            'success' => false,
            'error_code' => $errorCode,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public static function validationError(array $errors, string $message = 'Validasi gagal'): never
    {
        self::error(400, 'VALIDATION_ERROR', $message, $errors);
    }

    public static function unauthorized(string $message = 'Tidak terautentikasi'): never
    {
        self::error(401, 'UNAUTHORIZED', $message);
    }

    public static function forbidden(string $message = 'Akses ditolak'): never
    {
        self::error(403, 'FORBIDDEN', $message);
    }

    public static function notFound(string $message = 'Data tidak ditemukan'): never
    {
        self::error(404, 'NOT_FOUND', $message);
    }

    public static function serverError(string $message = 'Terjadi kesalahan server'): never
    {
        self::error(500, 'INTERNAL_SERVER_ERROR', $message);
    }

    public static function paginated(array $data, int $total, int $page, int $perPage): never
    {
        self::json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
                'has_next' => ($page * $perPage) < $total,
                'has_prev' => $page > 1,
            ],
        ]);
    }
}
