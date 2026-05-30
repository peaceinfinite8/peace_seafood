<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Secure File Upload Handler
 */
class FileUpload
{
    private string $uploadDir;
    private array  $allowedMimes;
    private int    $maxSize;

    public function __construct(string $subDir = '')
    {
        $config = require __DIR__ . '/../../config/app.php';

        $this->uploadDir    = rtrim($config['upload']['path'], '/') . ($subDir ? '/' . $subDir : '');
        $this->maxSize      = $config['upload']['max_size'];
        $this->allowedMimes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'pdf'  => 'application/pdf',
        ];
    }

    /**
     * Upload a file and return the stored filename
     */
    public function upload(array $file): string
    {
        // Validate upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload gagal dengan kode error: ' . $file['error']);
        }

        // Validate size
        if ($file['size'] > $this->maxSize) {
            $maxMb = round($this->maxSize / 1048576, 1);
            throw new \InvalidArgumentException("Ukuran file terlalu besar. Maksimal {$maxMb}MB");
        }

        // Validate MIME type using finfo (not trusting client-provided type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowedMimes, true)) {
            throw new \InvalidArgumentException("Tipe file tidak diizinkan: {$mime}");
        }

        // Get extension from MIME
        $ext = array_search($mime, $this->allowedMimes, true);

        // Generate safe random filename
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $destination = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Gagal menyimpan file');
        }

        return $filename;
    }

    /**
     * Delete a previously uploaded file
     */
    public function delete(string $filename): bool
    {
        $path = $this->uploadDir . '/' . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Get public URL for a file
     */
    public function getUrl(string $filename, string $subDir = ''): string
    {
        $base = $_ENV['APP_URL'] ?? 'http://localhost';
        $path = '/uploads/' . ($subDir ? $subDir . '/' : '') . $filename;
        return $base . $path;
    }
}
