<?php
namespace App\Models;

use PDO;

/**
 * User Model
 * Prepared statements for users table operations
 */
class User
{
    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, full_name, email, role, is_active, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO users (full_name, email, password_hash, role, is_active, created_at)
            VALUES (:full_name, :email, :password_hash, :role, :is_active, NOW())
        ');

        $stmt->execute([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'investigator',
            'is_active' => $data['is_active'] ?? 1
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Update user profile
     */
    public static function updateProfile(int $id, string $fullName, string $email): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE users SET full_name = :full_name, email = :email, updated_at = NOW()
            WHERE id = :id
        ');
        return $stmt->execute([
            'id' => $id,
            'full_name' => $fullName,
            'email' => $email
        ]);
    }

    /**
     * Update user password / secret passcode
     */
    public static function updatePassword(int $id, string $newPasswordHash): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE users SET password_hash = :hash, has_set_passcode = 1, updated_at = NOW()
            WHERE id = :id
        ');
        return $stmt->execute([
            'id' => $id,
            'hash' => $newPasswordHash
        ]);
    }

    /**
     * Set officer confidential passcode
     */
    public static function setPasscode(int $id, string $passcode): bool
    {
        $hash = password_hash($passcode, PASSWORD_DEFAULT);
        return self::updatePassword($id, $hash);
    }

    /**
     * Get all active investigators & staff for assignment
     */
    public static function getAllStaff(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT id, full_name, email, role, is_active 
            FROM users 
            ORDER BY full_name ASC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Get all users (Admin view)
     */
    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT id, full_name, email, role, is_active, created_at 
            FROM users 
            ORDER BY id ASC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Update user role and status (Admin only)
     */
    public static function updateRoleAndStatus(int $id, string $role, int $isActive): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE users 
            SET role = :role, is_active = :is_active, updated_at = NOW()
            WHERE id = :id
        ');
        return $stmt->execute([
            'id' => $id,
            'role' => $role,
            'is_active' => $isActive
        ]);
    }
}
