<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    /**
     * Get user notifications with optional unread & type filtering
     */
    public static function getUserNotifications(
        int $userId,
        bool $unreadOnly = false,
        ?string $type = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $sql = "SELECT id, user_id, type, title, message, priority,
                       related_entity_type, related_entity_id, is_read, read_at, created_at
                FROM notifications
                WHERE user_id = :user_id";

        $params = ['user_id' => $userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        if (!empty($type) && $type !== 'all') {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY is_read ASC, created_at DESC LIMIT :limit OFFSET :offset";

        // PDO limit/offset binding
        $stmt = self::getDb()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        if ($unreadOnly) {
            // no additional bind required
        }
        if (!empty($type) && $type !== 'all') {
            $stmt->bindValue(':type', $type, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get accurate count of unread notifications for a user
     */
    public static function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS total FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $row = self::fetchOne($sql, ['user_id' => $userId]);
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Fetch single notification verifying ownership
     */
    public static function getNotificationById(int $id, int $userId): ?array
    {
        $sql = "SELECT id, user_id, type, title, message, priority,
                       related_entity_type, related_entity_id, is_read, read_at, created_at
                FROM notifications
                WHERE id = :id AND user_id = :user_id";

        return self::fetchOne($sql, ['id' => $id, 'user_id' => $userId]);
    }

    /**
     * Mark single notification as read (BOLA protected by user_id)
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :user_id";
        self::execute($sql, ['id' => $id, 'user_id' => $userId]);
        return true;
    }

    /**
     * Mark single notification as unread
     */
    public static function markAsUnread(int $id, int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 0, read_at = NULL WHERE id = :id AND user_id = :user_id";
        self::execute($sql, ['id' => $id, 'user_id' => $userId]);
        return true;
    }

    /**
     * Mark all notifications as read for current user
     */
    public static function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = :user_id AND is_read = 0";
        self::execute($sql, ['user_id' => $userId]);
        return true;
    }

    /**
     * Delete/dismiss single notification (BOLA protected)
     */
    public static function deleteNotification(int $id, int $userId): bool
    {
        $sql = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
        self::execute($sql, ['id' => $id, 'user_id' => $userId]);
        return true;
    }

    /**
     * Create single notification
     */
    public static function createNotification(array $data): int
    {
        $sql = "INSERT INTO notifications
                (user_id, type, title, message, priority, related_entity_type, related_entity_id, is_read, created_at)
                VALUES
                (:user_id, :type, :title, :message, :priority, :related_entity_type, :related_entity_id, 0, NOW())";

        return self::execute($sql, [
            'user_id' => (int)$data['user_id'],
            'type' => $data['type'] ?? 'system',
            'title' => trim($data['title']),
            'message' => trim($data['message']),
            'priority' => $data['priority'] ?? 'normal',
            'related_entity_type' => $data['related_entity_type'] ?? null,
            'related_entity_id' => !empty($data['related_entity_id']) ? (int)$data['related_entity_id'] : null,
        ]);
    }

    /**
     * Dispatch notification to multiple recipient user IDs (batch operation)
     */
    public static function createBatchNotifications(array $recipientUserIds, array $data): int
    {
        $count = 0;
        foreach ($recipientUserIds as $recipientId) {
            $data['user_id'] = (int)$recipientId;
            if (self::createNotification($data) > 0) {
                $count++;
            }
        }
        return $count;
    }
}
