<?php
namespace App\Models;

use PDO;

/**
 * CID Victim Profile Model
 * Records victim personal identifiers, injury/harm condition, financial damages,
 * trauma report, contact details, and witness protection status.
 */
class Victim
{
    public static function getByCaseId(int $caseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT v.*, u.full_name AS registered_by_name
            FROM victims v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.case_id = :case_id
            ORDER BY v.id DESC
        ');
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM victims WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO victims (
                case_id, full_name, nid_passport, age, gender, 
                contact_number, address, victim_condition, 
                injuries_loss_description, protection_status, created_by, created_at
            ) VALUES (
                :case_id, :full_name, :nid_passport, :age, :gender, 
                :contact_number, :address, :victim_condition, 
                :injuries_loss_description, :protection_status, :created_by, NOW()
            )
        ');

        $stmt->execute([
            'case_id' => (int)$data['case_id'],
            'full_name' => $data['full_name'],
            'nid_passport' => $data['nid_passport'] ?? null,
            'age' => !empty($data['age']) ? (int)$data['age'] : null,
            'gender' => $data['gender'] ?? 'Male',
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'victim_condition' => $data['victim_condition'] ?? 'Safe / Protected',
            'injuries_loss_description' => $data['injuries_loss_description'] ?? null,
            'protection_status' => $data['protection_status'] ?? null,
            'created_by' => (int)$data['created_by']
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE victims SET
                full_name = :full_name,
                nid_passport = :nid_passport,
                age = :age,
                gender = :gender,
                contact_number = :contact_number,
                address = :address,
                victim_condition = :victim_condition,
                injuries_loss_description = :injuries_loss_description,
                protection_status = :protection_status,
                updated_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'full_name' => $data['full_name'],
            'nid_passport' => $data['nid_passport'] ?? null,
            'age' => !empty($data['age']) ? (int)$data['age'] : null,
            'gender' => $data['gender'] ?? 'Male',
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'victim_condition' => $data['victim_condition'] ?? 'Safe / Protected',
            'injuries_loss_description' => $data['injuries_loss_description'] ?? null,
            'protection_status' => $data['protection_status'] ?? null
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM victims WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
