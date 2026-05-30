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
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Berhasil', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Data berhasil dibuat'): never
    {
        self::success($data, $message, 201);
    }

    public static function error(...$args): never
    {
        // Backwards compatible:
        // Old calls: Response::error(string $message, int $status = 422, array $errors = [])
        // New calls: Response::error(int $status, string $errorCode, string $message, array $errors = [])

        if (isset($args[0]) && is_int($args[0])) {
            // new signature
            $status = $args[0];
            $errorCode = $args[1] ?? 'ERROR';
            $message = $args[2] ?? '';
            $errors = $args[3] ?? [];
        } else {
            // old signature
            $message = $args[0] ?? '';
            $status = $args[1] ?? 422;
            $errors = $args[2] ?? [];
            $errorCode = 'ERROR';
        }

        self::json([
            'success'    => false,
            'error_code' => $errorCode,
            'message'    => $message,
            'errors'     => $errors,
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
            'data'    => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => (int) ceil($total / $perPage),
                'has_next'     => ($page * $perPage) < $total,
                'has_prev'     => $page > 1,
            ],
        ]);
    }
}
