<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Models\CourseOffering;
use App\Models\LessonProgress;
use App\Models\AuditLog;

class QuizController
{
    /**
     * Get quizzes for a course offering
     */
    public function getQuizzes(Request $request, int $offeringId): void
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
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'course_offerings', $offeringId, ['reason' => 'Unenrolled student requested quizzes']);
                Response::error("Forbidden: You are not enrolled in this course offering", 403);
            }
            $quizzes = Quiz::getQuizzesByOffering($offeringId, true);
        } else {
            $quizzes = Quiz::getQuizzesByOffering($offeringId, false);
        }

        Response::json(['data' => $quizzes]);
    }

    /**
     * Get single quiz details
     */
    public function getQuiz(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $roleNames = array_column($currentUser['roles'], 'name');

        $quiz = Quiz::getQuizById($id);
        if (!$quiz) {
            Response::error("Quiz not found", 404);
        }

        $isStudent = in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true);

        if ($isStudent) {
            $studentProfileId = $currentUser['profile']['id'] ?? 0;
            if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, (int)$quiz['course_offering_id'])) {
                AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'quizzes', $id);
                Response::error("Forbidden: You are not enrolled in this course", 403);
            }

            // Student view hides answer keys until finished
            $questions = QuizQuestion::getQuestionsByQuiz($id, false);
            $attempts = QuizAttempt::getStudentAttemptsForQuiz($id, (int)$currentUser['id']);
            $quiz['attempts_taken'] = count($attempts);
            $quiz['my_attempts'] = $attempts;
            $quiz['questions'] = $questions;
        } else {
            // Lecturer views with answer keys
            $questions = QuizQuestion::getQuestionsByQuiz($id, true);
            $quiz['questions'] = $questions;
        }

        Response::json(['data' => $quiz]);
    }

    /**
     * Create quiz
     */
    public function createQuiz(Request $request, int $offeringId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $this->verifyLecturerOrAdminCourseAccess($currentUser, $offeringId, 'quiz.create');

        $body = $request->getBody();
        if (empty($body['title'])) {
            Response::error("Validation Error: 'title' is required", 422);
        }

        $quizId = Quiz::createQuiz([
            'course_offering_id' => $offeringId,
            'title' => $body['title'],
            'description' => $body['description'] ?? null,
            'instructions' => $body['instructions'] ?? null,
            'time_limit_minutes' => $body['time_limit_minutes'] ?? 30,
            'passing_percentage' => $body['passing_percentage'] ?? 50.0,
            'max_attempts' => $body['max_attempts'] ?? 1,
            'is_published' => $body['is_published'] ?? 0,
            'available_from' => $body['available_from'] ?? null,
            'available_until' => $body['available_until'] ?? null
        ]);

        AuditLog::log($currentUser['id'], 'quiz.create', 'quizzes', $quizId, ['title' => $body['title']]);

        Response::json([
            'message' => "Quiz created successfully",
            'data' => ['id' => $quizId]
        ], 201);
    }

    /**
     * Update quiz
     */
    public function updateQuiz(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $quiz = Quiz::getQuizById($id);
        if (!$quiz) {
            Response::error("Quiz not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$quiz['course_offering_id'], 'quiz.update');

        $body = $request->getBody();
        if (empty($body['title'])) {
            Response::error("Validation Error: 'title' is required", 422);
        }

        Quiz::updateQuiz($id, $body);

        AuditLog::log($currentUser['id'], 'quiz.update', 'quizzes', $id, ['title' => $body['title']]);

        Response::json(['message' => "Quiz updated successfully"]);
    }

    /**
     * Delete quiz
     */
    public function deleteQuiz(Request $request, int $id): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $quiz = Quiz::getQuizById($id);
        if (!$quiz) {
            Response::error("Quiz not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$quiz['course_offering_id'], 'quiz.delete');

        Quiz::deleteQuiz($id);

        AuditLog::log($currentUser['id'], 'quiz.delete', 'quizzes', $id, ['title' => $quiz['title']]);

        Response::json(['message' => "Quiz deleted successfully"]);
    }

    /**
     * Add question to quiz
     */
    public function addQuestion(Request $request, int $quizId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $quiz = Quiz::getQuizById($quizId);
        if (!$quiz) {
            Response::error("Quiz not found", 404);
        }

        $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$quiz['course_offering_id'], 'quiz.update');

        $body = $request->getBody();
        if (empty($body['question_text'])) {
            Response::error("Validation Error: 'question_text' is required", 422);
        }

        $questionId = QuizQuestion::createQuestion([
            'quiz_id' => $quizId,
            'question_text' => $body['question_text'],
            'question_type' => $body['question_type'] ?? 'multiple_choice',
            'marks' => $body['marks'] ?? 1.0,
            'sequence_order' => $body['sequence_order'] ?? 1
        ]);

        if (!empty($body['options']) && is_array($body['options'])) {
            foreach ($body['options'] as $idx => $opt) {
                if (!empty($opt['option_text'])) {
                    QuizOption::createOption([
                        'question_id' => $questionId,
                        'option_text' => $opt['option_text'],
                        'is_correct' => !empty($opt['is_correct']) ? 1 : 0,
                        'sequence_order' => $idx + 1
                    ]);
                }
            }
        }

        AuditLog::log($currentUser['id'], 'quiz_question.create', 'quiz_questions', $questionId, ['quiz_id' => $quizId]);

        Response::json([
            'message' => "Question added successfully",
            'data' => ['id' => $questionId]
        ], 201);
    }

    /**
     * Start a quiz attempt (Student)
     */
    public function startAttempt(Request $request, int $quizId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $quiz = Quiz::getQuizById($quizId);
        if (!$quiz) {
            Response::error("Quiz not found", 404);
        }

        if ($quiz['is_published'] != 1) {
            Response::error("Forbidden: Quiz is not currently published", 403);
        }

        // Enrollment check
        $studentProfileId = $currentUser['profile']['id'] ?? 0;
        if (!LessonProgress::isStudentEnrolledInCourseOffering((int)$studentProfileId, (int)$quiz['course_offering_id'])) {
            AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'quizzes', $quizId);
            Response::error("Forbidden: You are not enrolled in this course offering", 403);
        }

        // Check availability dates
        $now = time();
        if (!empty($quiz['available_from']) && $now < strtotime($quiz['available_from'])) {
            Response::error("Quiz Access Restricted: Quiz is not yet open", 422);
        }
        if (!empty($quiz['available_until']) && $now > strtotime($quiz['available_until'])) {
            Response::error("Quiz Access Restricted: Quiz availability period has expired", 422);
        }

        // Check max attempts
        $attempts = QuizAttempt::getStudentAttemptsForQuiz($quizId, (int)$currentUser['id']);
        if (count($attempts) >= (int)$quiz['max_attempts']) {
            Response::error("Attempt Limit Exceeded: You have reached the maximum allowed attempts ({$quiz['max_attempts']}) for this quiz", 422);
        }

        $attempt = QuizAttempt::startAttempt($quizId, (int)$currentUser['id']);

        // Fetch questions without answer keys
        $questions = QuizQuestion::getQuestionsByQuiz($quizId, false);
        $attempt['questions'] = $questions;

        AuditLog::log($currentUser['id'], 'quiz_attempt.start', 'quiz_attempts', (int)$attempt['id'], ['quiz_id' => $quizId]);

        Response::json([
            'message' => "Quiz attempt started",
            'data' => $attempt
        ], 201);
    }

    /**
     * Submit quiz attempt and perform server-side score evaluation
     */
    public function submitAttempt(Request $request, int $attemptId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $attempt = QuizAttempt::getAttemptById($attemptId);
        if (!$attempt) {
            Response::error("Attempt record not found", 404);
        }

        if ((int)$attempt['student_id'] !== (int)$currentUser['id']) {
            AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'quiz_attempts', $attemptId, ['reason' => 'Student attempted to submit another user\'s quiz attempt']);
            Response::error("Forbidden: You cannot submit another student's quiz attempt", 403);
        }

        if ($attempt['status'] === 'submitted') {
            Response::error("Attempt Already Submitted: This quiz attempt has already been submitted and finalized", 422);
        }

        $quiz = Quiz::getQuizById((int)$attempt['quiz_id']);
        if (!$quiz) {
            Response::error("Associated quiz not found", 404);
        }

        // Enforce server-side time limit check (+ 2 min grace buffer)
        $startedTs = strtotime($attempt['started_at']);
        $timeLimitSeconds = ((int)$quiz['time_limit_minutes'] * 60) + 120;
        if ((time() - $startedTs) > $timeLimitSeconds) {
            // Note: Auto-score what was submitted, but record time expiration
            AuditLog::log($currentUser['id'], 'quiz_attempt.time_exceeded', 'quiz_attempts', $attemptId);
        }

        $body = $request->getBody();
        $responses = $body['responses'] ?? [];

        // Server-side scoring evaluation
        $questions = QuizQuestion::getQuestionsByQuiz((int)$quiz['id'], true);
        $totalPossibleMarks = 0.0;
        $totalAchievedScore = 0.0;

        foreach ($questions as $q) {
            $questionId = (int)$q['id'];
            $questionMarks = (float)$q['marks'];
            $totalPossibleMarks += $questionMarks;

            $userAns = null;
            foreach ($responses as $r) {
                if ((int)($r['question_id'] ?? 0) === $questionId) {
                    $userAns = $r;
                    break;
                }
            }

            $selectedOptionId = isset($userAns['selected_option_id']) ? (int)$userAns['selected_option_id'] : null;
            $textResponse = $userAns['text_response'] ?? null;

            $marksAwarded = 0.0;
            $isCorrect = 0;

            if ($selectedOptionId !== null) {
                // Verify option is correct
                $options = QuizOption::getOptionsByQuestion($questionId, true);
                foreach ($options as $opt) {
                    if ((int)$opt['id'] === $selectedOptionId && (int)$opt['is_correct'] === 1) {
                        $isCorrect = 1;
                        $marksAwarded = $questionMarks;
                        break;
                    }
                }
            }

            $totalAchievedScore += $marksAwarded;

            QuizResponse::recordResponse($attemptId, $questionId, $selectedOptionId, $textResponse, $marksAwarded, $isCorrect);
        }

        QuizAttempt::finalizeAttempt($attemptId, $totalAchievedScore, $totalPossibleMarks, (float)$quiz['passing_percentage']);

        AuditLog::log($currentUser['id'], 'quiz_attempt.submit', 'quiz_attempts', $attemptId, [
            'score_achieved' => $totalAchievedScore,
            'total_possible' => $totalPossibleMarks
        ]);

        $updatedAttempt = QuizAttempt::getAttemptById($attemptId);

        Response::json([
            'message' => "Quiz submitted successfully",
            'data' => $updatedAttempt
        ]);
    }

    /**
     * Get attempt details with answers (if authorized / results allowed)
     */
    public function getAttemptDetails(Request $request, int $attemptId): void
    {
        $currentUser = AuthMiddleware::authenticate($request);
        $attempt = QuizAttempt::getAttemptById($attemptId);
        if (!$attempt) {
            Response::error("Attempt record not found", 404);
        }

        $roleNames = array_column($currentUser['roles'], 'name');
        $isStudent = in_array('student', $roleNames, true) && !in_array('super_admin', $roleNames, true) && !in_array('admin', $roleNames, true);

        if ($isStudent && (int)$attempt['student_id'] !== (int)$currentUser['id']) {
            AuditLog::log($currentUser['id'], 'auth.unauthorized_access', 'quiz_attempts', $attemptId);
            Response::error("Forbidden: You cannot view another student's quiz attempt", 403);
        }

        if (!$isStudent) {
            $this->verifyLecturerOrAdminCourseAccess($currentUser, (int)$attempt['course_offering_id'], 'quiz.grade');
        }

        $responses = QuizResponse::getResponsesByAttempt($attemptId);
        $attempt['responses'] = $responses;

        Response::json(['data' => $attempt]);
    }

    /**
     * Helper for lecturer / admin course scoping
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
