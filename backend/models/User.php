<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * User Identity and Access Model
 */
class User extends Model
{
    /**
     * Find user by unique email address
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::getDb()->prepare("
            SELECT * FROM `users` WHERE `email` = :email LIMIT 1
        ");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Find user by primary ID
     */
    public static function findById(int|string $id): ?array
    {
        $stmt = self::getDb()->prepare("
            SELECT * FROM `users` WHERE `id` = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Verify password hash against plain-text password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash plain-text password securely
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Update user last_login_at timestamp
     */
    public static function updateLastLogin(int|string $userId): void
    {
        $stmt = self::getDb()->prepare("
            UPDATE `users` SET `last_login_at` = NOW() WHERE `id` = :id
        ");
        $stmt->execute(['id' => $userId]);
    }

    /**
     * Retrieve assigned roles for a user
     */
    public static function getUserRoles(int|string $userId): array
    {
        $stmt = self::getDb()->prepare("
            SELECT r.id, r.name, r.description 
            FROM `roles` r
            INNER JOIN `user_roles` ur ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retrieve granular permissions assigned to user's roles
     */
    public static function getUserPermissions(int|string $userId): array
    {
        // First check if user is super_admin
        $roles = self::getUserRoles($userId);
        $roleNames = array_column($roles, 'name');

        if (in_array('super_admin', $roleNames, true)) {
            // Super admins possess all defined permissions automatically
            $stmt = self::getDb()->query("SELECT `name` FROM `permissions`");
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
        }

        $stmt = self::getDb()->prepare("
            SELECT DISTINCT p.name, p.module, p.description
            FROM `permissions` p
            INNER JOIN `role_permissions` rp ON p.id = rp.permission_id
            INNER JOIN `user_roles` ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    /**
     * Retrieve user department assignments
     */
    public static function getUserDepartments(int|string $userId): array
    {
        $stmt = self::getDb()->prepare("
            SELECT d.id, d.code, d.name, uda.is_head_of_department
            FROM `departments` d
            INNER JOIN `user_department_assignments` uda ON d.id = uda.department_id
            WHERE uda.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch student or staff profile depending on assigned roles
     */
    public static function getProfileDetails(int|string $userId, array $roleNames): ?array
    {
        if (in_array('student', $roleNames, true)) {
            $stmt = self::getDb()->prepare("
                SELECT sp.*, e.class_id, e.program_id, e.intake_id, e.status AS enrollment_status
                FROM `student_profiles` sp
                LEFT JOIN `student_enrollments` e ON sp.id = e.student_id
                WHERE sp.user_id = :user_id LIMIT 1
            ");
            $stmt->execute(['user_id' => $userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            return $profile ? array_merge($profile, ['type' => 'student']) : null;
        }

        if (array_intersect(['lecturer', 'admin', 'accountant', 'super_admin'], $roleNames)) {
            $stmt = self::getDb()->prepare("
                SELECT * FROM `staff_profiles` WHERE `user_id` = :user_id LIMIT 1
            ");
            $stmt->execute(['user_id' => $userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            return $profile ? array_merge($profile, ['type' => 'staff']) : null;
        }

        return null;
    }

    /**
     * Sanitize user array for API transmission (strips password hash)
     */
    public static function sanitizeUser(
        array $user,
        array $roles = [],
        array $permissions = [],
        array $departments = [],
        ?array $profile = null
    ): array {
        unset($user['password_hash']);

        return array_merge($user, [
            'roles'       => $roles,
            'permissions' => $permissions,
            'departments' => $departments,
            'profile'     => $profile,
        ]);
    }
}
