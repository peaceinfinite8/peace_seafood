<?php

declare(strict_types=1);

namespace App\Utils;

class Cache
{
    private static string $cacheDir = '';

    private static function getCacheDir(): string
    {
        if (empty(self::$cacheDir)) {
            self::$cacheDir = CACHE_PATH;
        }
        return self::$cacheDir;
    }

    private static function getFilePath(string $key): string
    {
        return self::getCacheDir() . '/' . md5($key) . '.cache';
    }

    /**
     * Get cached value
     */
    public static function get(string $key): mixed
    {
        $file = self::getFilePath($key);
        if (!file_exists($file)) return null;

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Set cache value
     */
    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        $file = self::getFilePath($key);
        $data = [
            'value'   => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
        ];
        file_put_contents($file, serialize($data), LOCK_EX);
    }

    /**
     * Delete cache entry
     */
    public static function delete(string $key): void
    {
        $file = self::getFilePath($key);
        if (file_exists($file)) unlink($file);
    }

    /**
     * Clear all cache
     */
    public static function flush(): void
    {
        foreach (glob(self::getCacheDir() . '/*.cache') as $file) {
            unlink($file);
        }
    }

    /**
     * Remember: get from cache or compute and store
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}
