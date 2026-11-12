<?php
namespace App\Models;

use PDO;

/**
 * Case Assignment Model
 * Connects Users (Investigators) and Cases (ManyToMany)
 */
class CaseAssignment
{
    /**
     * Get all users assigned to a case
     */
    public static function getAssignedUsers(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT u.id, u.full_name, u.email, u.role, ca.assigned_at, assigner.full_name AS assigned_by_name
            FROM case_assignments ca
            JOIN users u ON ca.user_id = u.id
            LEFT JOIN users assigner ON ca.assigned_by = assigner.id
            WHERE ca.case_id = :case_id
            ORDER BY u.full_name ASC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    /**
     * Assign a user to a case
     */
    public static function assign(int $caseId, int $userId, int $assignedBy): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT IGNORE INTO case_assignments (case_id, user_id, assigned_by, assigned_at)
            VALUES (:case_id, :user_id, :assigned_by, NOW())
        ');
        return $stmt->execute([
            'case_id' => $caseId,
            'user_id' => $userId,
            'assigned_by' => $assignedBy
        ]);
    }

    /**
     * Sync multiple assignees for a case
     */
    public static function sync(int $caseId, array $userIds, int $assignedBy): void
    {
        $pdo = Database::getConnection();

        // Remove existing assignments
        $delStmt = $pdo->prepare('DELETE FROM case_assignments WHERE case_id = :case_id');
        $delStmt->execute(['case_id' => $caseId]);

        // Add new assignments
        $insStmt = $pdo->prepare('
            INSERT INTO case_assignments (case_id, user_id, assigned_by, assigned_at)
            VALUES (:case_id, :user_id, :assigned_by, NOW())
        ');

        foreach ($userIds as $uid) {
            $insStmt->execute([
                'case_id' => $caseId,
                'user_id' => (int)$uid,
                'assigned_by' => $assignedBy
            ]);
        }
    }

    /**
     * Check if a specific user is assigned to a case
     */
    public static function isUserAssigned(int $caseId, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM case_assignments WHERE case_id = :case_id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['case_id' => $caseId, 'user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
