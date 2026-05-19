<?php

declare(strict_types=1);

namespace App\utils;

/**
 * Simple File Logger (wraps Monolog if available, fallback to file)
 */
class Logger
{
    private static string $logPath = '';

    private static function getPath(): string
    {
        if (self::$logPath === '') {
            self::$logPath = __DIR__ . '/../../storage/logs/app.log';
        }
        return self::$logPath;
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ctx       = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line      = "[{$timestamp}] {$level}: {$message}{$ctx}" . PHP_EOL;

        $path = self::getPath();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::write('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::write('CRITICAL', $message, $context);
    }
}
