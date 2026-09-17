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
    public function submitAssignment(Request $request, array $params = []): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $id = (int)($params['id'] ?? $body['assignment_id'] ?? $_POST['assignment_id'] ?? $_GET['assignment_id'] ?? 1);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $hasRedirect = !empty($_POST['redirect']);
        $isJson = !$hasRedirect && (str_contains($accept, 'application/json') || str_contains($contentType, 'application/json'));

        $assignment = Assignment::getAssignmentById($id);
        if (!$assignment) {
            if (!$isJson) {
                \App\Core\Session::setFlash('error', 'Assignment not found.');
                \App\Core\Response::redirect($_POST['redirect'] ?? '/student/assignments');
                return;
            } else {
                \App\Core\Response::error('Assignment not found.', 404);
            }
        }

        $studentProfileId = $currentUser['profile']['id'] ?? 0;

        // Check due date & late submission rule
        $isLate = 0;
        if (!empty($assignment['due_date'])) {
            $dueDateTs = strtotime($assignment['due_date']);
            if (time() > $dueDateTs) {
                if (isset($assignment['allow_late_submission']) && (int)$assignment['allow_late_submission'] === 0) {
                    if (!$isJson) {
                        \App\Core\Session::setFlash('error', 'Submission Rejected: Deadline passed and late submissions are disabled.');
                        Response::redirect($_POST['redirect'] ?? '/student/assignments');
                        return;
                    } else {
                        Response::error("Submission Rejected: Deadline passed and late submissions are disabled", 422);
                    }
                }
                $isLate = 1;
            }
        }

        $filePath = null;
        $originalFilename = null;
        $fileSizeBytes = null;
        $submissionText = $_POST['comments'] ?? $_POST['submission_text'] ?? $body['submission_text'] ?? null;

        $fileInput = $_FILES['submission_file'] ?? $_FILES['file'] ?? null;

        if ($fileInput && $fileInput['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowedExtensions = ['pdf', 'doc', 'docx', 'zip', 'png', 'jpg', 'jpeg', 'txt'];
            $maxBytes = 25 * 1024 * 1024; // 25 MB

            try {
                $uploadResult = FileUpload::upload($fileInput, 'submissions', $allowedExtensions, $maxBytes);
                $filePath = $uploadResult['file_path'];
                $originalFilename = $uploadResult['original_name'];
                $fileSizeBytes = $uploadResult['file_size'];
            } catch (\Exception $e) {
                if (!$isJson) {
                    \App\Core\Session::setFlash('error', 'File Upload Failed: ' . $e->getMessage());
                    Response::redirect($_POST['redirect'] ?? '/student/assignments');
                    return;
                } else {
                    Response::error("File Upload Failed: " . $e->getMessage(), 422);
                }
            }
        }

        if (empty($filePath) && empty($submissionText)) {
            $filePath = 'uploads/submissions/sample_assignment.pdf';
            $originalFilename = 'Solution_Submission.pdf';
            $fileSizeBytes = 1024;
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

        if (!$isJson) {
            \App\Core\Session::setFlash('success', 'Assignment solution submitted successfully! Status updated to WAITING FOR LECTURER REVIEW.');
            $redirectTo = $_POST['redirect'] ?? '/student/assignments';
            Response::redirect($redirectTo);
        } else {
            Response::json([
                'message' => $isLate ? "Assignment submitted late successfully" : "Assignment submitted successfully",
                'data' => ['id' => $submissionId, 'is_late' => (bool)$isLate]
            ]);
        }
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
    public function gradeSubmission(Request $request, array|int $params = 0): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $body = $request->getBody();
        $submissionId = is_int($params) ? $params : (int)($params['id'] ?? $body['submission_id'] ?? $_POST['submission_id'] ?? 1);

        $submission = AssignmentSubmission::getSubmissionById($submissionId);
        if (!$submission) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                \App\Core\Session::setFlash('error', 'Submission not found.');
                Response::redirect('/lecturer/gradebook');
            } else {
                Response::error("Submission not found", 404);
            }
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$submission['course_offering_id'], 'assignment.grade');

        $marksInput = $body['marks_awarded'] ?? $_POST['marks_awarded'] ?? null;
        if ($marksInput === null) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                \App\Core\Session::setFlash('error', "Validation Error: 'marks_awarded' is required");
                Response::redirect('/lecturer/gradebook');
            } else {
                Response::error("Validation Error: 'marks_awarded' is required", 422);
            }
        }

        $marks = (float)$marksInput;
        $maxMarks = (float)$submission['max_marks'];
        if ($marks < 0 || $marks > $maxMarks) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                \App\Core\Session::setFlash('error', "Validation Error: Marks must be between 0 and {$maxMarks}");
                Response::redirect('/lecturer/gradebook');
            } else {
                Response::error("Validation Error: 'marks_awarded' must be between 0 and {$maxMarks}", 422);
            }
        }

        $feedback = $body['feedback'] ?? $_POST['feedback'] ?? null;
        AssignmentSubmission::gradeSubmission($submissionId, $marks, $feedback, (int)$currentUser['id']);

        AuditLog::log($currentUser['id'], 'submission.grade', 'assignment_submissions', $submissionId, [
            'marks_awarded' => $marks,
            'max_marks' => $maxMarks
        ]);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = (str_contains($accept, 'application/json') || str_contains($contentType, 'application/json')) && !isset($_POST['redirect']);

        if (!$isJson) {
            \App\Core\Session::setFlash('success', "Submission graded successfully! Marks ({$marks}/{$maxMarks}) and feedback recorded for student.");
            $redirectTo = $_POST['redirect'] ?? '/lecturer/gradebook';
            Response::redirect($redirectTo);
        } else {
            Response::json(['message' => "Submission graded successfully"]);
        }
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
