<?php
namespace App\Services;

use App\Models\Database;
use App\Helpers\Session;

/**
 * Activity Logging Service
 * Records audit logs for all major system actions
 */
class ActivityLogger
{
    public static function log(int $caseId, string $actionType, string $description, ?int $actorId = null): void
    {
        try {
            $pdo = Database::getConnection();
            if ($actorId === null) {
                $user = Session::get('user');
                $actorId = $user ? (int)$user['id'] : 1;
            }

            $stmt = $pdo->prepare('
                INSERT INTO case_activity_logs (case_id, actor_user_id, action_type, description, created_at)
                VALUES (:case_id, :actor_id, :action_type, :description, NOW())
            ');
            $stmt->execute([
                'case_id' => $caseId,
                'actor_id' => $actorId,
                'action_type' => $actionType,
                'description' => $description
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to write activity log: " . $e->getMessage());
        }
    }
}
