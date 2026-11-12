<?php
namespace App\Models;

use PDO;

/**
 * Investigation Notes Model
 */
class InvestigationNote
{
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT n.*, u.full_name AS author_name
            FROM investigation_notes n
            LEFT JOIN users u ON n.created_by = u.id
            WHERE n.case_id = :case_id
            ORDER BY n.created_at DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO investigation_notes (case_id, note_text, created_by, created_at)
            VALUES (:case_id, :note_text, :created_by, NOW())
        ');
        $stmt->execute([
            'case_id' => $data['case_id'],
            'note_text' => $data['note_text'],
            'created_by' => $data['created_by']
        ]);
        return (int) $pdo->lastInsertId();
    }
}
