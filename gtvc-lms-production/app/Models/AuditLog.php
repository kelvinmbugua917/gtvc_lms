<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Audit Log Security Event Logger
 */
class AuditLog extends Model
{
    /**
     * Record a security audit event
     */
    public static function log(
        ?int $userId,
        string $action,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $details = []
    ): bool {
        try {
            $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            // Filter out sensitive data from details before writing
            unset($details['password'], $details['password_hash'], $details['token']);

            $stmt = self::getDb()->prepare("
                INSERT INTO `audit_logs` (`user_id`, `action`, `ip_address`, `user_agent`, `details_json`, `created_at`)
                VALUES (:user_id, :action, :ip_address, :user_agent, :details_json, NOW())
            ");

            return $stmt->execute([
                'user_id'      => $userId,
                'action'       => $action,
                'ip_address'   => substr($ipAddress, 0, 45),
                'user_agent'   => substr($userAgent, 0, 255),
                'details_json' => empty($details) ? null : json_encode($details, JSON_UNESCAPED_SLASHES),
            ]);
        } catch (\Throwable $e) {
            // Fail safely without breaking the request flow
            error_log("Failed to insert audit log: " . $e->getMessage());
            return false;
        }
    }
}
