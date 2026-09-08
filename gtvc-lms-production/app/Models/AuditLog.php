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
        ?string $entityType = null,
        $entityId = null,
        array $details = []
    ): bool {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            if ($entityType !== null) {
                $details['entity_type'] = $entityType;
            }
            if ($entityId !== null) {
                $details['entity_id'] = $entityId;
            }

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

    /**
     * Retrieve recent security audit log events
     */
    public static function getRecentLogs(int $limit = 100): array
    {
        try {
            $stmt = self::getDb()->prepare("
                SELECT a.*, u.email, u.first_name, u.last_name 
                FROM `audit_logs` a
                LEFT JOIN `users` u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
