<?php
namespace App\Models;

use PDO;

/**
 * CID Suspect & Criminal Profile Model
 * Stores comprehensive suspect physical features, mannerisms, gait, posture,
 * last known whereabouts, aliases, and criminal background.
 */
class Suspect
{
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT s.*, u.full_name AS registered_by_name
            FROM suspects s
            LEFT JOIN users u ON s.created_by = u.id
            WHERE s.case_id = :case_id
            ORDER BY s.id DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM suspects WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO suspects (
                case_id, full_name, alias_name, nid_passport, age, gender, 
                suspect_status, physical_description, identifying_marks, 
                posture_mannerisms, last_known_location, photo_path, 
                prior_records, created_by, created_at
            ) VALUES (
                :case_id, :full_name, :alias_name, :nid_passport, :age, :gender, 
                :suspect_status, :physical_description, :identifying_marks, 
                :posture_mannerisms, :last_known_location, :photo_path, 
                :prior_records, :created_by, NOW()
            )
        ');

        $stmt->execute([
            'case_id' => (int)$data['case_id'],
            'full_name' => $data['full_name'],
            'alias_name' => $data['alias_name'] ?? null,
            'nid_passport' => $data['nid_passport'] ?? null,
            'age' => !empty($data['age']) ? (int)$data['age'] : null,
            'gender' => $data['gender'] ?? 'Male',
            'suspect_status' => $data['suspect_status'] ?? 'At Large',
            'physical_description' => $data['physical_description'] ?? null,
            'identifying_marks' => $data['identifying_marks'] ?? null,
            'posture_mannerisms' => $data['posture_mannerisms'] ?? null,
            'last_known_location' => $data['last_known_location'] ?? null,
            'photo_path' => $data['photo_path'] ?? null,
            'prior_records' => $data['prior_records'] ?? null,
            'created_by' => (int)$data['created_by']
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $fields = [
            'full_name = :full_name',
            'alias_name = :alias_name',
            'nid_passport = :nid_passport',
            'age = :age',
            'gender = :gender',
            'suspect_status = :suspect_status',
            'physical_description = :physical_description',
            'identifying_marks = :identifying_marks',
            'posture_mannerisms = :posture_mannerisms',
            'last_known_location = :last_known_location',
            'prior_records = :prior_records'
        ];

        $params = [
            'id' => $id,
            'full_name' => $data['full_name'],
            'alias_name' => $data['alias_name'] ?? null,
            'nid_passport' => $data['nid_passport'] ?? null,
            'age' => !empty($data['age']) ? (int)$data['age'] : null,
            'gender' => $data['gender'] ?? 'Male',
            'suspect_status' => $data['suspect_status'] ?? 'At Large',
            'physical_description' => $data['physical_description'] ?? null,
            'identifying_marks' => $data['identifying_marks'] ?? null,
            'posture_mannerisms' => $data['posture_mannerisms'] ?? null,
            'last_known_location' => $data['last_known_location'] ?? null,
            'prior_records' => $data['prior_records'] ?? null
        ];

        if (!empty($data['photo_path'])) {
            $fields[] = 'photo_path = :photo_path';
            $params['photo_path'] = $data['photo_path'];
        }

        $sql = 'UPDATE suspects SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM suspects WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
