<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\FileUpload;
use App\Middleware\AuthMiddleware;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class AssignmentController
{
    /**
     * Get assignments for a course offering
     */
    public function getAssignments(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, $offeringId)) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['reason' => 'Student not enrolled in course offering']);
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
            $assignments = Assignment::getAssignmentsByOffering($offeringId, true);
        } else {
            $assignments = Assignment::getAssignmentsByOffering($offeringId, false);
        }

        Response::json(['data' => $assignments]);
    }

    /**
     * Get single assignment details
     */
    public function getAssignment(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            Response::error("Assignment not found", 404);
        }

        if (in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true)) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, (int)$assignment['course_offering_id'])) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'assignments', $id, ['reason' => 'Student not enrolled in assignment course']);
                Response::error("Forbidden: You are not enrolled in this course", 403);
            }
            // Attach student's submission status if exists
            $mySubmission = AssignmentSubmission::getStudentSubmission($id, (int)$currentUser['id']);
            $assignment['my_submission'] = $mySubmission;
        }

        Response::json(['data' => $assignment]);
    }

    /**
     * Create assignment
     */
    public function createAssignment(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'assignment.create');

        $body = $request->getBody();
        if (empty($body['title'])) {
            Response::error("Validation Error: 'title' is required", 422);
        }

        $assignmentId = Assignment::createAssignment([
            'course_offering_id' => $offeringId,
            'title' => $body['title'],
            'description' => $body['description'] ?? null,
            'instructions' => $body['instructions'] ?? null,
            'max_marks' => $body['max_marks'] ?? 100,
            'is_published' => $body['is_published'] ?? 0,
            'release_date' => $body['release_date'] ?? null,
            'due_date' => $body['due_date'] ?? null,
            'allow_late_submission' => $body['allow_late_submission'] ?? 1
        ]);

        AuditLog::log($currentUser['id'], 'assignment.create', 'assignments', $assignmentId, ['title' => $body['title'], 'offering_id' => $offeringId]);

        Response::json([
            'message' => "Assignment created successfully",
            'data' => ['id' => $assignmentId]
        ], 201);
    }

    /**
     * Update assignment
     */
    public function updateAssignment(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            Response::error("Assignment not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$assignment['course_offering_id'], 'assignment.update');

        $body = $request->getBody();
        if (empty($body['title'])) {
            Response::error("Validation Error: 'title' is required", 422);
        }

        Assignment::updateAssignment($id, $body);

        AuditLog::log($currentUser['id'], 'assignment.update', 'assignments', $id, ['title' => $body['title']]);

        Response::json(['message' => "Assignment updated successfully"]);
    }

    /**
     * Delete assignment
     */
    public function deleteAssignment(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            Response::error("Assignment not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$assignment['course_offering_id'], 'assignment.delete');

        Assignment::deleteAssignment($id);

        AuditLog::log($currentUser['id'], 'assignment.delete', 'assignments', $id, ['title' => $assignment['title']]);

        Response::json(['message' => "Assignment deleted successfully"]);
    }

    /**
     * Student submits assignment (file upload / text submission)
     */
    public function submitAssignment(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            Response::error("Assignment not found", 404);
        }

        if ($assignment['is_published'] != 1) {
            Response::error("Forbidden: Assignment is not published yet", 403);
        }

        $studentProfileId = $currentUser['profile']['id'] ?? 0;
        if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, (int)$assignment['course_offering_id'])) {
            AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'assignments', $id, ['reason' => 'Unenrolled student attempted submission']);
            Response::error("Forbidden: You are not enrolled in this course", 403);
        }

        // Check due date & late submission rule
        $isLate = 0;
        if (!empty($assignment['due_date'])) {
            $dueDateTs = strtotime($assignment['due_date']);
            if (time() > $dueDateTs) {
                if ($assignment['allow_late_submission'] == 0) {
                    Response::error("Submission Rejected: Deadline passed and late submissions are disabled for this assignment", 422);
                }
                $isLate = 1;
            }
        }

        $filePath = null;
        $originalFilename = null;
        $fileSizeBytes = null;
        $submissionText = $_POST['submission_text'] ?? null;

        if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowedExtensions = ['pdf', 'doc', 'docx', 'zip', 'png', 'jpg', 'txt'];
            $maxBytes = 25 * 1024 * 1024; // 25 MB

            try {
                $uploadResult = FileUpload::upload($_FILES['file'], 'submissions', $allowedExtensions, $maxBytes);
                $filePath = $uploadResult['file_path'];
                $originalFilename = $uploadResult['original_name'];
                $fileSizeBytes = $uploadResult['file_size'];
            } catch (\Exception $e) {
                Response::error("File Upload Failed: " . $e->getMessage(), 422);
            }
        }

        if (empty($filePath) && empty($submissionText)) {
            Response::error("Validation Error: Either a file attachment or submission text must be provided", 422);
        }

        $submissionId = AssignmentSubmission::saveSubmission([
            'assignment_id' => $id,
            'student_id' => $currentUser['id'],
            'file_path' => $filePath,
            'original_filename' => $originalFilename,
            'file_size_bytes' => $fileSizeBytes,
            'submission_text' => $submissionText,
            'is_late' => $isLate
        ]);

        AuditLog::log($currentUser['id'], 'submission.upload', 'assignment_submissions', $submissionId, [
            'assignment_id' => $id,
            'is_late' => $isLate
        ]);

        Response::json([
            'message' => $isLate ? "Assignment submitted late successfully" : "Assignment submitted successfully",
            'data' => ['id' => $submissionId, 'is_late' => (bool)$isLate]
        ]);
    }

    /**
     * Lecturer/HOD/Admin views all submissions for an assignment
     */
    public function getSubmissions(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            Response::error("Assignment not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$assignment['course_offering_id'], 'assignment.grade');

        $submissions = AssignmentSubmission::getSubmissionsByAssignment($id);
        Response::json(['data' => $submissions]);
    }

    /**
     * Download submission file securely
     */
    public function downloadSubmission(Request $request, int $submissionId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $submission = AssignmentSubmission::getSubmissionById($submissionId);
        if (!$submission) {
            Response::error("Submission not found", 404);
        }

        $roleNames = array_column($currentUser['roles'], 'name');
        $isOwnerStudent = in_array('student', $roleNames, true) && (int)$submission['student_id'] === (int)$currentUser['id'];
        
        if (!$isOwnerStudent) {
            $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$submission['course_offering_id'], 'assignment.grade');
        }

        if (empty($submission['file_path'])) {
            Response::error("No file attached to this submission", 404);
        }

        $fullPath = FileUpload::getStoragePath($submission['file_path']);
        if (!file_exists($fullPath)) {
            Response::error("Storage File Not Found", 404);
        }

        AuditLog::log($currentUser['id'], 'submission.download', 'assignment_submissions', $submissionId);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($submission['original_filename'] ?? $submission['file_path']) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit();
    }

    /**
     * Grade an assignment submission
     */
    public function gradeSubmission(Request $request, int $submissionId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $submission = AssignmentSubmission::getSubmissionById($submissionId);
        if (!$submission) {
            Response::error("Submission not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$submission['course_offering_id'], 'assignment.grade');

        $body = $request->getBody();
        if (!isset($body['marks_awarded'])) {
            Response::error("Validation Error: 'marks_awarded' is required", 422);
        }

        $marks = (float)$body['marks_awarded'];
        $maxMarks = (float)$submission['max_marks'];
        if ($marks < 0 || $marks > $maxMarks) {
            Response::error("Validation Error: 'marks_awarded' must be between 0 and {$maxMarks}", 422);
        }

        AssignmentSubmission::gradeSubmission($submissionId, $marks, $body['feedback'] ?? null, (int)$currentUser['id']);

        AuditLog::log($currentUser['id'], 'submission.grade', 'assignment_submissions', $submissionId, [
            'marks_awarded' => $marks,
            'max_marks' => $maxMarks
        ]);

        Response::json(['message' => "Submission graded successfully"]);
    }

    /**
     * Verify lecturer or admin course access helper
     */
    private function verifyLecturerOrAdminCourseAccess(array $currentUser, int $offeringId, string $permission): void
    {
        $roleNames = array_column($currentUser['roles'], 'name');
        if (in_array('super_admin', $roleNames, true) || in_array('admin', $roleNames, true)) {
            return;
        }

        $offering = CourseOffering::getOfferingById($offeringId);
        if (!$offering) {
            Response::error("Course offering not found", 404);
        }

        if (in_array('lecturer', $roleNames, true)) {
            if ((int)$offering['primary_lecturer_id'] !== (int)$currentUser['id']) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['permission' => $permission]);
                Response::error("Forbidden: You are not assigned as lecturer for this course offering", 403);
            }
            return;
        }

        if (in_array('hod', $roleNames, true)) {
            $userDeptId = $currentUser['department_id'] ?? 0;
            if ((int)$offering['department_id'] !== (int)$userDeptId) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['permission' => $permission]);
                Response::error("Forbidden: Course offering belongs to another department", 403);
            }
            return;
        }

        Response::error("Forbidden: Insufficient privileges", 403);
    }
}
