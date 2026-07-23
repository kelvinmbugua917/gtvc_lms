<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Notification;
use App\Models\AuditLog;

class NotificationController extends Controller
{
    /**
     * Get list of notifications for currently logged in user
     */
    public function getNotifications(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);

        $unreadOnly = $request->get('unread_only') === '1' || $request->get('unread_only') === 'true';
        $type = $request->get('type');
        $limit = max(1, min(100, (int)($request->get('limit') ?? 50)));
        $offset = max(0, (int)($request->get('offset') ?? 0));

        $notifications = Notification::getUserNotifications($currentUser['id'], $unreadOnly, $type, $limit, $offset);
        $unreadCount = Notification::getUnreadCount($currentUser['id']);

        Response::json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'limit' => $limit,
            'offset' => $offset
        ], "User notifications retrieved");
    }

    /**
     * Get unread notification counter for header bell indicator
     */
    public function getUnreadCount(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $count = Notification::getUnreadCount($currentUser['id']);

        Response::json(['unread_count' => $count], "Unread notification count");
    }

    /**
     * Get single notification verifying ownership
     */
    public function getNotificationById(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $id = (int)($request->getParam('id') ?? 0);

        if ($id <= 0) {
            Response::error("Invalid notification ID", 400);
        }

        $notification = Notification::getNotificationById($id, $currentUser['id']);
        if (!$notification) {
            AuditLog::log($currentUser['id'], 'notification.unauthorized_access', null, null, ['notification_id' => $id]);
            Response::error("Notification not found or access denied", 404);
        }

        Response::json($notification, "Notification fetched successfully");
    }

    /**
     * Mark single notification as read (BOLA protected)
     */
    public function markAsRead(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $notification = Notification::getNotificationById($id, $currentUser['id']);

        if (!$notification) {
            Response::error("Notification not found or access denied", 404);
        }

        Notification::markAsRead($id, $currentUser['id']);

        Response::json(['id' => $id, 'is_read' => 1], "Notification marked as read");
    }

    /**
     * Mark single notification as unread
     */
    public function markAsUnread(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $notification = Notification::getNotificationById($id, $currentUser['id']);

        if (!$notification) {
            Response::error("Notification not found or access denied", 404);
        }

        Notification::markAsUnread($id, $currentUser['id']);

        Response::json(['id' => $id, 'is_read' => 0], "Notification marked as unread");
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        Notification::markAllAsRead($currentUser['id']);

        Response::json(['message' => 'All notifications marked as read'], "Notifications cleared");
    }

    /**
     * Delete/dismiss single notification
     */
    public function deleteNotification(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $notification = Notification::getNotificationById($id, $currentUser['id']);

        if (!$notification) {
            Response::error("Notification not found or access denied", 404);
        }

        Notification::deleteNotification($id, $currentUser['id']);

        Response::json(['id' => $id], "Notification deleted");
    }
}
