<?php
namespace App\Models;

use PDO;

/**
 * Evidence Model
 * Stores digital and physical evidence metadata and safe file attachments
 */
class Evidence
{
    /**
     * Get all evidence records for a specific case
     */
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT e.*, u.full_name AS uploaded_by_name 
            FROM evidence e
            LEFT JOIN users u ON e.uploaded_by = u.id
            WHERE e.case_id = :case_id
            ORDER BY e.collected_at DESC, e.id DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    /**
     * Find single evidence record by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT e.*, c.case_number, c.title AS case_title 
            FROM evidence e
            JOIN cases c ON e.case_id = c.id
            WHERE e.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Create evidence record
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO evidence (
                case_id, title, evidence_type, description, collected_at, 
                file_path, original_file_name, file_size, mime_type, uploaded_by, created_at
            ) VALUES (
                :case_id, :title, :evidence_type, :description, :collected_at, 
                :file_path, :original_file_name, :file_size, :mime_type, :uploaded_by, NOW()
            )
        ');

        $stmt->execute([
            'case_id' => $data['case_id'],
            'title' => $data['title'],
            'evidence_type' => $data['evidence_type'] ?? 'Document',
            'description' => $data['description'],
            'collected_at' => $data['collected_at'] ?? date('Y-m-d'),
            'file_path' => $data['file_path'] ?? null,
            'original_file_name' => $data['original_file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'mime_type' => $data['mime_type'] ?? null,
            'uploaded_by' => $data['uploaded_by']
        ]);

        return (int) $pdo->lastInsertId();
    }
}
