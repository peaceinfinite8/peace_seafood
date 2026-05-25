<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * General Helper Functions
 */
class Helper
{
    /**
     * Generate nota number: PS-YYMMDD-XXXX
     */
    public static function generateNotaNumber(int $idGudang): string
    {
        $date     = date('ymd');
        $prefix   = 'PS';

        // Count today's nota for this gudang
        $count = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM nota WHERE id_gudang = ? AND DATE(created_at) = CURDATE()",
            [$idGudang]
        );

        $seq = str_pad((string)(($count['cnt'] ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$date}-{$seq}";
    }

    /**
     * Generate titipan number: TT-YYMMDD-XXXX
     */
    public static function generateTitipanNumber(int $idGudang): string
    {
        $date = date('ymd');

        $count = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM titipan WHERE id_gudang = ? AND DATE(created_at) = CURDATE()",
            [$idGudang]
        );

        $seq = str_pad((string)(($count['cnt'] ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        return "TT-{$date}-{$seq}";
    }

    /**
     * Format currency: 1000000 → Rp 1.000.000
     */
    public static function formatCurrency(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format number with dot separator: 1000000 → 1.000.000
     */
    public static function formatNumber(int|float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Format date: 2025-05-17 → 17/05/2025
     */
    public static function formatDate(string $date): string
    {
        if (empty($date)) return '-';
        return date('d/m/Y', strtotime($date));
    }

    /**
     * Format datetime: 2025-05-17 10:30:00 → 17/05/2025 10:30
     */
    public static function formatDatetime(string $datetime): string
    {
        if (empty($datetime)) return '-';
        return date('d/m/Y H:i', strtotime($datetime));
    }

    /**
     * Get current datetime in WIB
     */
    public static function nowWib(): string
    {
        date_default_timezone_set('Asia/Jakarta');
        return date('Y-m-d H:i:s');
    }

    /**
     * Sanitize string input
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get pagination offset
     */
    public static function getOffset(int $page, int $perPage): int
    {
        return ($page - 1) * $perPage;
    }

    /**
     * Parse pagination params from request
     */
    public static function getPagination(): array
    {
        $page    = max(1, (int)($_GET['page']     ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        return [$page, $perPage, self::getOffset($page, $perPage)];
    }

    /**
     * Get request body as array (JSON or form data)
     */
    public static function getRequestBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

        return $_POST;
    }

    /**
     * Calculate jatuh tempo date
     */
    public static function calcJatuhTempo(int $idGudang, string $fromDate = ''): string
    {
        $days = (int)(Database::fetchOne(
            "SELECT nilai FROM settings WHERE id_gudang = ? AND kunci = 'jatuh_tempo_default_hari'",
            [$idGudang]
        )['nilai'] ?? 30);

        $base = $fromDate ?: date('Y-m-d');
        return date('Y-m-d', strtotime("+{$days} days", strtotime($base)));
    }

    /**
     * Get setting value for a gudang
     */
    public static function getSetting(int $idGudang, string $key, mixed $default = null): mixed
    {
        $row = Database::fetchOne(
            "SELECT nilai FROM settings WHERE id_gudang = ? AND kunci = ?",
            [$idGudang, $key]
        );
        return $row ? $row['nilai'] : $default;
    }

    /**
     * Check if stok is below minimum threshold
     */
    public static function isStokBelowMinimum(float $stokQty, float $stokMinimum): bool
    {
        return $stokQty < $stokMinimum;
    }

    /**
     * Relative time (2 menit lalu, 1 jam lalu, dll)
     */
    public static function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60)       return 'Baru saja';
        if ($diff < 3600)     return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400)    return floor($diff / 3600) . ' jam lalu';
        if ($diff < 2592000)  return floor($diff / 86400) . ' hari lalu';
        return self::formatDate(date('Y-m-d', strtotime($datetime)));
    }
}
