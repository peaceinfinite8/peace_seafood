<?php
// Convert PNG files from storage/uploads to public/assets/images/products as WEBP
$root = dirname(__DIR__);
$src = $root . '/storage/uploads';
$dst = $root . '/public/assets/images/products';
if (!is_dir($src)) {
    echo "Source directory not found: $src\n";
    exit(1);
}
if (!is_dir($dst)) {
    if (!mkdir($dst, 0755, true)) {
        echo "Failed to create destination directory: $dst\n";
        exit(1);
    }
}
$files = glob($src . '/*.png');
if (empty($files)) {
    echo "No PNG files found in $src\n";
    exit(0);
}
if (!function_exists('imagecreatefrompng')) {
    echo "GD extension missing or imagecreatefrompng not available.\n";
    exit(1);
}
if (!function_exists('imagewebp')) {
    echo "GD does not support imagewebp. Install/enable WEBP support in PHP GD.\n";
    exit(1);
}
$quality = 80; // WEBP quality
foreach ($files as $file) {
    $info = pathinfo($file);
    $out = $dst . '/' . $info['filename'] . '.webp';
    if (file_exists($out)) {
        echo "Skipped (exists): {$out}\n";
        continue;
    }
    $img = @imagecreatefrompng($file);
    if (!$img) {
        echo "Failed to read: {$file}\n";
        continue;
    }
    // Convert palette to true color and preserve alpha
    if (!imageistruecolor($img)) {
        $true = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagealphablending($true, false);
        imagesavealpha($true, true);
        imagecopy($true, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagedestroy($img);
        $img = $true;
    } else {
        imagealphablending($img, false);
        imagesavealpha($img, true);
    }
    $saved = imagewebp($img, $out, $quality);
    imagedestroy($img);
    if ($saved) {
        echo "Created: {$out}\n";
    } else {
        echo "Failed to create: {$out}\n";
    }
}
echo "Done.\n";
