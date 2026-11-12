<?php
namespace App\Models;

use PDO;

/**
 * Activity Log Model
 */
class ActivityLog
{
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT al.*, u.full_name AS actor_name, u.role AS actor_role
            FROM case_activity_logs al
            LEFT JOIN users u ON al.actor_user_id = u.id
            WHERE al.case_id = :case_id
            ORDER BY al.created_at DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public static function getAllSystemLogs(int $limit = 50): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT al.*, c.case_number, c.title AS case_title, u.full_name AS actor_name, u.email AS actor_email, u.role AS actor_role
            FROM case_activity_logs al
            JOIN cases c ON al.case_id = c.id
            LEFT JOIN users u ON al.actor_user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT {$limit}
        ");
        return $stmt->fetchAll();
    }
}
