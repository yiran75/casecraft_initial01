<?php
namespace App\Models;

use PDO;

/**
 * Witness Statement Model
 */
class WitnessStatement
{
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT ws.*, u.full_name AS recorded_by_name
            FROM witness_statements ws
            LEFT JOIN users u ON ws.recorded_by = u.id
            WHERE ws.case_id = :case_id
            ORDER BY ws.recorded_at DESC, ws.id DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO witness_statements (case_id, witness_reference, statement_text, recorded_at, recorded_by, created_at)
            VALUES (:case_id, :witness_reference, :statement_text, :recorded_at, :recorded_by, NOW())
        ');
        $stmt->execute([
            'case_id' => $data['case_id'],
            'witness_reference' => $data['witness_reference'],
            'statement_text' => $data['statement_text'],
            'recorded_at' => $data['recorded_at'] ?? date('Y-m-d'),
            'recorded_by' => $data['recorded_by']
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM witness_statements WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE witness_statements SET
                witness_reference = :witness_reference,
                statement_text = :statement_text,
                recorded_at = :recorded_at
            WHERE id = :id
        ');
        return $stmt->execute([
            'id' => $id,
            'witness_reference' => $data['witness_reference'],
            'statement_text' => $data['statement_text'],
            'recorded_at' => $data['recorded_at']
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM witness_statements WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
