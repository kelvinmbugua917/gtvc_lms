<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\FileUpload;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\NotificationService;

class AnnouncementController extends Controller
{
    /**
     * List eligible announcements for current authenticated user
     */
    public function getAnnouncements(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);

        $filters = [
            'search' => $request->get('search'),
            'priority' => $request->get('priority'),
            'management_view' => $request->get('management_view') === '1' || $request->get('management_view') === 'true',
        ];

        // If management view requested, verify staff/admin privilege
        if ($filters['management_view']) {
            $roleNames = array_column($currentUser['roles'], 'name');
            $isStaff = !empty(array_intersect(['admin', 'super_admin', 'hod', 'lecturer', 'registrar'], $roleNames));
            if (!$isStaff) {
                Response::error("Forbidden: Management view requires staff elevated privileges", 403);
            }
        }

        $announcements = Announcement::getAnnouncementsForUser($currentUser, $filters);
        Response::json($announcements, "Announcements retrieved successfully");
    }

    /**
     * Get single announcement by ID
     */
    public function getAnnouncementById(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $id = (int)($request->getParam('id') ?? 0);

        if ($id <= 0) {
            Response::error("Invalid announcement ID", 400);
        }

        $announcement = Announcement::getAnnouncementById($id);
        if (!$announcement) {
            Response::error("Announcement not found", 404);
        }

        Response::json($announcement, "Announcement details fetched");
    }

    /**
     * Create a new targeted institutional or departmental announcement
     */
    public function createAnnouncement(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $roleNames = array_column($currentUser['roles'], 'name');
        $isAuthorized = !empty(array_intersect(['admin', 'super_admin', 'hod', 'lecturer', 'registrar'], $roleNames))
                        || in_array('announcement.create', $currentUser['permissions'], true);

        if (!$isAuthorized) {
            AuditLog::log($currentUser['id'], 'announcement.create_denied', null, null, ['reason' => 'Unauthorized role']);
            Response::error("Forbidden: Insufficient permissions to create announcements", 403);
        }

        $data = $request->getBody();
        if (empty($data['title']) || empty($data['content'])) {
            Response::error("Title and Content are required fields", 400);
        }

        // Handle attachment upload if present
        $attachmentPath = null;
        $attachmentName = null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $validation = FileUpload::validate($_FILES['attachment']);
            if (!$validation['valid']) {
                Response::error("Attachment Upload Error: " . $validation['error'], 400);
            }
            $attachmentPath = FileUpload::store($_FILES['attachment'], $validation);
            $attachmentName = $validation['original_filename'];
        }

        $isPublished = isset($data['is_published']) ? (int)$data['is_published'] : 1;

        $announcementData = [
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'priority' => $data['priority'] ?? 'normal',
            'target_role' => $data['target_role'] ?? 'all',
            'target_department_id' => !empty($data['target_department_id']) ? (int)$data['target_department_id'] : null,
            'target_program_id' => !empty($data['target_program_id']) ? (int)$data['target_program_id'] : null,
            'target_class_id' => !empty($data['target_class_id']) ? (int)$data['target_class_id'] : null,
            'target_course_offering_id' => !empty($data['target_course_offering_id']) ? (int)$data['target_course_offering_id'] : null,
            'author_user_id' => $currentUser['id'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'is_published' => $isPublished,
            'publish_at' => $data['publish_at'] ?? null,
            'expire_at' => $data['expire_at'] ?? null
        ];

        $announcementId = Announcement::createAnnouncement($announcementData);

        AuditLog::log($currentUser['id'], 'announcement.created', null, null, [
            'announcement_id' => $announcementId,
            'title' => $data['title'],
            'target_role' => $announcementData['target_role']
        ]);

        // Dispatch notifications if published immediately
        if ($isPublished === 1) {
            NotificationService::notifyAnnouncementPublished($announcementId, $announcementData);
        }

        Response::json([
            'id' => $announcementId,
            'message' => 'Announcement created successfully'
        ], 'Announcement created', 201);
    }

    /**
     * Update an existing announcement
     */
    public function updateAnnouncement(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $announcement = Announcement::getAnnouncementById($id);

        if (!$announcement) {
            Response::error("Announcement not found", 404);
        }

        $roleNames = array_column($currentUser['roles'], 'name');
        $isAuthor = ((int)$announcement['author_user_id'] === (int)$currentUser['id']);
        $isAdmin = !empty(array_intersect(['admin', 'super_admin'], $roleNames));

        if (!$isAuthor && !$isAdmin) {
            AuditLog::log($currentUser['id'], 'announcement.update_denied', null, null, ['announcement_id' => $id]);
            Response::error("Forbidden: You can only edit announcements created by yourself", 403);
        }

        $data = $request->getBody();
        if (empty($data['title']) || empty($data['content'])) {
            Response::error("Title and Content are required fields", 400);
        }

        // Attachment update check
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $validation = FileUpload::validate($_FILES['attachment']);
            if (!$validation['valid']) {
                Response::error("Attachment Upload Error: " . $validation['error'], 400);
            }
            $data['attachment_path'] = FileUpload::store($_FILES['attachment'], $validation);
            $data['attachment_name'] = $validation['original_filename'];
        }

        Announcement::updateAnnouncement($id, $data);

        AuditLog::log($currentUser['id'], 'announcement.updated', null, null, [
            'announcement_id' => $id,
            'title' => $data['title']
        ]);

        Response::json(['id' => $id], "Announcement updated successfully");
    }

    /**
     * Publish a draft announcement
     */
    public function publishAnnouncement(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $announcement = Announcement::getAnnouncementById($id);

        if (!$announcement) {
            Response::error("Announcement not found", 404);
        }

        Announcement::publishAnnouncement($id);
        
        NotificationService::notifyAnnouncementPublished($id, $announcement);

        AuditLog::log($currentUser['id'], 'announcement.published', null, null, ['announcement_id' => $id]);

        Response::json(['id' => $id], "Announcement published successfully");
    }

    /**
     * Archive an announcement
     */
    public function archiveAnnouncement(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $announcement = Announcement::getAnnouncementById($id);

        if (!$announcement) {
            Response::error("Announcement not found", 404);
        }

        Announcement::archiveAnnouncement($id);

        AuditLog::log($currentUser['id'], 'announcement.archived', null, null, ['announcement_id' => $id]);

        Response::json(['id' => $id], "Announcement archived successfully");
    }

    /**
     * Delete an announcement
     */
    public function deleteAnnouncement(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        CsrfMiddleware::validate($request);

        $id = (int)($request->getParam('id') ?? 0);
        $announcement = Announcement::getAnnouncementById($id);

        if (!$announcement) {
            Response::error("Announcement not found", 404);
        }

        $roleNames = array_column($currentUser['roles'], 'name');
        $isAuthor = ((int)$announcement['author_user_id'] === (int)$currentUser['id']);
        $isAdmin = !empty(array_intersect(['admin', 'super_admin'], $roleNames));

        if (!$isAuthor && !$isAdmin) {
            AuditLog::log($currentUser['id'], 'announcement.delete_denied', null, null, ['announcement_id' => $id]);
            Response::error("Forbidden: Cannot delete announcement", 403);
        }

        Announcement::deleteAnnouncement($id);

        AuditLog::log($currentUser['id'], 'announcement.deleted', null, null, ['announcement_id' => $id]);

        Response::json(['id' => $id], "Announcement deleted successfully");
    }

    /**
     * Download announcement attachment safely
     */
    public function downloadAttachment(Request $request): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $id = (int)($request->getParam('id') ?? 0);

        $announcement = Announcement::getAnnouncementById($id);
        if (!$announcement || empty($announcement['attachment_path'])) {
            Response::error("Attachment not found", 404);
        }

        FileUpload::streamDownload(
            $announcement['attachment_path'],
            $announcement['attachment_name'] ?? 'announcement_notice.pdf'
        );
    }
}
