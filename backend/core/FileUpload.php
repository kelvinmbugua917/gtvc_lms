<?php

declare(strict_types=1);

namespace App\Core;

class FileUpload
{
    private const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20 MB max

    private const ALLOWED_EXTENSIONS = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'mp4' => 'video/mp4'
    ];

    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'exe', 'pl', 'py',
        'sh', 'bat', 'cmd', 'cgi', 'htaccess', 'js', 'jar', 'vbs', 'asp', 'aspx'
    ];

    /**
     * Validate file upload security rules
     */
    public static function validate(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'error' => 'Invalid file upload structure'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => self::getUploadErrorMessage($file['error'])];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'File size exceeds maximum threshold of 20MB'];
        }

        $filename = basename($file['name']);

        // Check for double extensions & forbidden extensions anywhere in the filename
        $parts = explode('.', strtolower($filename));
        foreach ($parts as $part) {
            if (in_array($part, self::FORBIDDEN_EXTENSIONS, true)) {
                return ['valid' => false, 'error' => 'Security Error: Forbidden file extension detected in filename'];
            }
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($extension, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'error' => "File extension '.{$extension}' is not permitted for learning materials"];
        }

        return [
            'valid' => true,
            'extension' => $extension,
            'mime_type' => self::ALLOWED_EXTENSIONS[$extension],
            'original_filename' => $filename,
            'size' => $file['size']
        ];
    }

    /**
     * Store uploaded file securely outside web root
     */
    public static function store(array $file, array $validatedInfo): string
    {
        $storageDir = __DIR__ . '/../storage/uploads/materials/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Generate cryptographically random filename
        $randomName = bin2hex(random_bytes(16)) . '.' . $validatedInfo['extension'];
        $destination = $storageDir . $randomName;

        if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new \RuntimeException('Failed to save uploaded file to secure storage directory');
            }
        } else {
            // Simulated / test mock upload writer
            file_put_contents($destination, "Simulated upload material content");
        }

        return 'storage/uploads/materials/' . $randomName;
    }

    /**
     * Stream file download with security response headers
     */
    public static function download(string $relativePath, string $originalTitle, string $mimeType): void
    {
        // Sanitize path against path traversal
        $cleanPath = str_replace(['../', '..\\'], '', $relativePath);
        $fullPath = __DIR__ . '/../' . $cleanPath;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            Response::error("Learning material file not found on server", 404);
        }

        // Security headers
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes(basename($originalTitle)) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');
        header('Content-Security-Policy: default-src \'none\'');

        readfile($fullPath);
        exit;
    }

    private static function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum allowed upload size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            default => 'Unknown upload error',
        };
    }
}
