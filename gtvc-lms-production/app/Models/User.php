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

        if (array_intersect(['lecturer', 'trainer', 'hod', 'accountant', 'bursar', 'admin', 'super_admin', 'registrar', 'it_admin'], $roleNames)) {
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
     * Ensure all predefined roles exist in the database table
     */
    public static function ensureRolesExist(): array
    {
        $db = self::getDb();
        $rolesMap = [
            'super_admin' => 'System Administrator with unrestricted access',
            'admin'       => 'System Administrator',
            'hod'         => 'Head of Academic Department',
            'lecturer'    => 'Academic Lecturer / Trainer',
            'trainer'     => 'Technical Trainer',
            'accountant'  => 'Finance Administrator',
            'bursar'      => 'Institute Bursar',
            'registrar'   => 'Academic Registrar',
            'it_admin'    => 'ICT Systems Administrator',
            'student'     => 'Enrolled Trainee / Student',
        ];

        try {
            $stmt = $db->query("SELECT id, name FROM `roles`");
            $existing = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $existingMap = [];
            foreach ($existing as $r) {
                $existingMap[strtolower($r['name'])] = (int)$r['id'];
            }

            foreach ($rolesMap as $name => $desc) {
                if (!isset($existingMap[$name])) {
                    $ins = $db->prepare("INSERT INTO `roles` (`name`, `description`) VALUES (:name, :desc)");
                    $ins->execute(['name' => $name, 'desc' => $desc]);
                    $existingMap[$name] = (int)$db->lastInsertId();
                }
            }

            // Ensure default permissions exist for registrar and other roles
            $rolePermDefaults = [
                'registrar'   => [2, 3, 7], // user.manage, academic.manage, attendance.mark
                'it_admin'    => [1, 2],    // system.manage, user.manage
                'admin'       => [2, 3, 7],
                'hod'         => [4, 5, 7],
                'lecturer'    => [4, 5, 7],
                'trainer'     => [4, 5, 7],
                'accountant'  => [6],
                'bursar'      => [6],
            ];

            foreach ($rolePermDefaults as $roleName => $permIds) {
                if (!empty($existingMap[$roleName])) {
                    $rId = $existingMap[$roleName];
                    foreach ($permIds as $pId) {
                        $checkStmt = $db->prepare("SELECT 1 FROM `role_permissions` WHERE `role_id` = :rid AND `permission_id` = :pid LIMIT 1");
                        $checkStmt->execute(['rid' => $rId, 'pid' => $pId]);
                        if (!$checkStmt->fetch()) {
                            $insRp = $db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES (:rid, :pid)");
                            $insRp->execute(['rid' => $rId, 'pid' => $pId]);
                        }
                    }
                }
            }

            return $existingMap;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Automatically sync users with student_profiles and staff_profiles tables
     */
    public static function syncAllUserProfiles(): void
    {
        try {
            $db = self::getDb();
            self::ensureRolesExist();

            // 1. Sync Student Profiles
            $studentUsers = $db->query("
                SELECT DISTINCT u.id, u.registration_number, u.email
                FROM `users` u
                INNER JOIN `user_roles` ur ON u.id = ur.user_id
                INNER JOIN `roles` r ON ur.role_id = r.id
                WHERE r.name = 'student'
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($studentUsers as $su) {
                $check = $db->prepare("SELECT id FROM `student_profiles` WHERE user_id = :uid");
                $check->execute(['uid' => $su['id']]);
                if (!$check->fetch()) {
                    $indexNo = !empty($su['registration_number']) ? $su['registration_number'] : ('GTVC/' . sprintf('%04d', $su['id']));
                    $ins = $db->prepare("
                        INSERT INTO `student_profiles` (`user_id`, `index_number`, `gender`)
                        VALUES (:uid, :idx, 'male')
                    ");
                    $ins->execute(['uid' => $su['id'], 'idx' => $indexNo]);
                }
            }

            // 2. Sync Staff Profiles
            $staffUsers = $db->query("
                SELECT DISTINCT u.id, u.registration_number, u.email, r.name AS role_name
                FROM `users` u
                INNER JOIN `user_roles` ur ON u.id = ur.user_id
                INNER JOIN `roles` r ON ur.role_id = r.id
                WHERE r.name IN ('lecturer', 'trainer', 'hod', 'accountant', 'bursar', 'admin', 'super_admin', 'registrar', 'it_admin')
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($staffUsers as $st) {
                $check = $db->prepare("SELECT id FROM `staff_profiles` WHERE user_id = :uid");
                $check->execute(['uid' => $st['id']]);
                if (!$check->fetch()) {
                    $staffNo = !empty($st['registration_number']) ? $st['registration_number'] : ('STF/' . sprintf('%04d', $st['id']));
                    $designation = strtoupper(str_replace('_', ' ', $st['role_name']));
                    $ins = $db->prepare("
                        INSERT INTO `staff_profiles` (`user_id`, `staff_number`, `designation`)
                        VALUES (:uid, :stf, :des)
                    ");
                    $ins->execute(['uid' => $st['id'], 'stf' => $staffNo, 'des' => $designation]);
                }
            }
        } catch (\Throwable $e) {
            error_log("Failed to sync user profiles: " . $e->getMessage());
        }
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

    /**
     * Update user password hash
     */
    public static function updatePassword(int|string $userId, string $newPasswordHash): void
    {
        $stmt = self::getDb()->prepare("
            UPDATE `users` SET `password_hash` = :hash, `updated_at` = NOW() WHERE `id` = :id
        ");
        $stmt->execute(['hash' => $newPasswordHash, 'id' => $userId]);
    }
}
