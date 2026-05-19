<?php

declare(strict_types=1);

namespace App\Utils;

class Formatter
{
    /**
     * Format number as Indonesian Rupiah
     */
    public static function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format number with thousand separator
     */
    public static function number(float $num, int $decimals = 2): string
    {
        return number_format($num, $decimals, ',', '.');
    }

    /**
     * Format date to Indonesian format
     */
    public static function date(string $date, string $format = 'd M Y'): string
    {
        if (empty($date)) return '-';
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function datetime(string $datetime): string
    {
        if (empty($datetime)) return '-';
        return date('d M Y H:i', strtotime($datetime));
    }

    /**
     * Format weight in kg
     */
    public static function weight(float $kg): string
    {
        return self::number($kg) . ' kg';
    }

    /**
     * Format percentage
     */
    public static function percent(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals) . '%';
    }
}
