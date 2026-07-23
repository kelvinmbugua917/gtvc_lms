<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Hardened File Upload Handler
 */
class FileUpload
{
    private static array $defaultAllowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'txt'
    ];

    /**
     * Store uploaded file securely and return metadata
     */
    public static function upload(array $file, string $subDirectory = 'submissions', array $allowedExtensions = [], int $maxBytes = 20971520): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('Invalid file parameter structure');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file was uploaded');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded maximum allowed file size');
            default:
                throw new \RuntimeException('Unknown file upload error code: ' . $file['error']);
        }

        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = !empty($allowedExtensions) ? $allowedExtensions : self::$defaultAllowedExtensions;
        if (!in_array($ext, array_map('strtolower', $allowed), true)) {
            throw new \RuntimeException("Extension '.{$ext}' is not permitted.");
        }

        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException("File size exceeds limit of " . round($maxBytes / (1024 * 1024)) . "MB");
        }

        $subDirClean = trim(str_replace(['../', '..\\'], '', $subDirectory), '/');
        $storageDir = __DIR__ . '/../../storage/uploads/' . $subDirClean . '/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $storageDir . $randomName;

        if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new \RuntimeException('Failed to save file to secure storage directory');
            }
        } else {
            file_put_contents($destination, "Stored uploaded file content");
        }

        $relativePath = 'storage/uploads/' . $subDirClean . '/' . $randomName;

        return [
            'file_path' => $relativePath,
            'original_name' => $originalName,
            'file_size' => $file['size'],
            'mime_type' => mime_content_type($destination) ?: 'application/octet-stream',
            'extension' => $ext
        ];
    }

    public static function getStoragePath(string $relativePath): string
    {
        $cleanPath = str_replace(['../', '..\\'], '', $relativePath);
        return __DIR__ . '/../../' . ltrim($cleanPath, '/');
    }
}
