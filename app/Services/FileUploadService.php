<?php
namespace App\Services;

/**
 * File Upload Security Service
 * Validates extension, MIME type, file size, generates random filenames,
 * and handles secure authenticated streaming of evidence files.
 */
class FileUploadService
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

    /**
     * Process evidence file upload securely
     */
    public static function uploadEvidenceFile(array $file): array
    {
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'file_path' => null, 'original_name' => null, 'size' => null, 'mime' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload failed with system code: ' . $file['error']];
        }

        // 1. Check size limit
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed limit of 5 MB.'];
        }

        // 2. Validate file extension
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return ['success' => false, 'error' => 'Invalid file extension. Only PDF, JPG, and PNG formats are permitted for evidence.'];
        }

        // 3. Validate real MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            return ['success' => false, 'error' => "Disallowed MIME type ({$realMime}). Permitted: PDF, JPEG, PNG."];
        }

        // 4. Generate random, unguessable filename
        $randomFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadDir = dirname(__DIR__, 2) . '/storage/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destinationPath = $uploadDir . '/' . $randomFileName;

        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['success' => false, 'error' => 'Could not save file to storage directory.'];
        }

        return [
            'success' => true,
            'file_path' => $randomFileName,
            'original_name' => $originalName,
            'size' => (int)$file['size'],
            'mime' => $realMime
        ];
    }

    /**
     * Stream file securely to authenticated client
     */
    public static function streamDownload(string $fileName, string $downloadName, string $mimeType): void
    {
        $filePath = dirname(__DIR__, 2) . '/storage/uploads/' . basename($fileName);

        if (!file_exists($filePath)) {
            http_response_code(404);
            die("Error: Evidence file not found on disk.");
        }

        // Set headers for secure file transfer
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}
