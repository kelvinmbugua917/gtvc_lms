<?php

declare(strict_types=1);

// Gilgil TVC LMS - Front Controller

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/LoginAttempt.php';
require_once __DIR__ . '/../models/AcademicYear.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/ClassCohort.php';
require_once __DIR__ . '/../models/Unit.php';
require_once __DIR__ . '/../models/CourseOffering.php';
require_once __DIR__ . '/../models/CourseModule.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/LearningMaterial.php';
require_once __DIR__ . '/../models/LessonProgress.php';
require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../models/AssignmentSubmission.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/QuizQuestion.php';
require_once __DIR__ . '/../models/QuizOption.php';
require_once __DIR__ . '/../models/QuizAttempt.php';
require_once __DIR__ . '/../models/QuizResponse.php';
require_once __DIR__ . '/../models/StudentCourseGrade.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/AttendanceSession.php';
require_once __DIR__ . '/../models/AttendanceRecord.php';
require_once __DIR__ . '/../models/Announcement.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AcademicController.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/EnrollmentController.php';
require_once __DIR__ . '/../controllers/CourseModuleController.php';
require_once __DIR__ . '/../controllers/LessonController.php';
require_once __DIR__ . '/../controllers/LearningMaterialController.php';
require_once __DIR__ . '/../controllers/LessonProgressController.php';
require_once __DIR__ . '/../controllers/AssignmentController.php';
require_once __DIR__ . '/../controllers/QuizController.php';
require_once __DIR__ . '/../controllers/GradebookController.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../controllers/AttendanceReportController.php';
require_once __DIR__ . '/../controllers/AnnouncementController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

use App\Config\AppConfig;
use App\Core\Response;
use App\Core\Request;
use App\Core\Router;
use App\Controllers\HealthController;
use App\Controllers\AuthController;
use App\Controllers\AcademicController;
use App\Controllers\StudentController;
use App\Controllers\EnrollmentController;
use App\Controllers\CourseModuleController;
use App\Controllers\LessonController;
use App\Controllers\LearningMaterialController;
use App\Controllers\LessonProgressController;
use App\Controllers\AssignmentController;
use App\Controllers\QuizController;
use App\Controllers\GradebookController;
use App\Controllers\AttendanceController;
use App\Controllers\AttendanceReportController;
use App\Controllers\AnnouncementController;
use App\Controllers\NotificationController;

// Register global exception and error handlers
set_exception_handler([Response::class, 'handleException']);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Load environment configuration
AppConfig::loadEnv(__DIR__ . '/../.env');

// Initialize Router
$router = new Router();

// Health Check Routes
$router->get('/api/v1/health', [HealthController::class, 'check']);
$router->get('/health', [HealthController::class, 'check']);

// Authentication & RBAC Routes
$router->get('/api/v1/auth/csrf', [AuthController::class, 'csrfToken']);
$router->post('/api/v1/auth/login', [AuthController::class, 'login']);
$router->post('/api/v1/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/v1/auth/me', [AuthController::class, 'me']);

// Phase 6A Academic Structure Routes
$router->get('/api/v1/academic-years', [AcademicController::class, 'getAcademicYears']);
$router->get('/api/v1/intakes', [AcademicController::class, 'getIntakes']);
$router->get('/api/v1/departments', [AcademicController::class, 'getDepartments']);
$router->get('/api/v1/programs', [AcademicController::class, 'getPrograms']);
$router->get('/api/v1/classes', [AcademicController::class, 'getClasses']);
$router->get('/api/v1/units', [AcademicController::class, 'getUnits']);
$router->get('/api/v1/course-offerings', [AcademicController::class, 'getCourseOfferings']);

// Phase 6A Student Management Routes
$router->get('/api/v1/students', [StudentController::class, 'getStudents']);
$router->get('/api/v1/students/{id}', [StudentController::class, 'getStudentById']);
$router->post('/api/v1/students', [StudentController::class, 'createStudent']);

// Phase 6A Enrollment Workflow Routes
$router->get('/api/v1/enrollments', [EnrollmentController::class, 'getEnrollments']);
$router->post('/api/v1/enrollments', [EnrollmentController::class, 'createEnrollment']);
$router->put('/api/v1/enrollments/{id}', [EnrollmentController::class, 'updateEnrollment']);

// Phase 6B Course Modules Routes
$router->get('/api/v1/course-offerings/{id}/modules', [CourseModuleController::class, 'getModules']);
$router->post('/api/v1/course-offerings/{id}/modules', [CourseModuleController::class, 'createModule']);
$router->put('/api/v1/modules/{id}', [CourseModuleController::class, 'updateModule']);
$router->delete('/api/v1/modules/{id}', [CourseModuleController::class, 'deleteModule']);
$router->post('/api/v1/course-offerings/{id}/modules/reorder', [CourseModuleController::class, 'reorderModules']);

// Phase 6B Lessons Routes
$router->get('/api/v1/modules/{id}/lessons', [LessonController::class, 'getLessons']);
$router->post('/api/v1/modules/{id}/lessons', [LessonController::class, 'createLesson']);
$router->get('/api/v1/lessons/{id}', [LessonController::class, 'getLessonDetails']);
$router->put('/api/v1/lessons/{id}', [LessonController::class, 'updateLesson']);
$router->delete('/api/v1/lessons/{id}', [LessonController::class, 'deleteLesson']);
$router->post('/api/v1/modules/{id}/lessons/reorder', [LessonController::class, 'reorderLessons']);

// Phase 6B Learning Materials Routes
$router->get('/api/v1/lessons/{id}/materials', [LearningMaterialController::class, 'getMaterials']);
$router->post('/api/v1/lessons/{id}/materials', [LearningMaterialController::class, 'createMaterial']);
$router->delete('/api/v1/materials/{id}', [LearningMaterialController::class, 'deleteMaterial']);
$router->get('/api/v1/materials/{id}/download', [LearningMaterialController::class, 'downloadMaterial']);

// Phase 6B Student Progress Routes
$router->get('/api/v1/lessons/{id}/progress', [LessonProgressController::class, 'getLessonProgress']);
$router->put('/api/v1/lessons/{id}/progress', [LessonProgressController::class, 'saveLessonProgress']);
$router->get('/api/v1/course-offerings/{id}/progress', [LessonProgressController::class, 'getCourseProgress']);
$router->get('/api/v1/course-offerings/{id}/progress-overview', [LessonProgressController::class, 'getCourseProgressOverview']);

// Phase 6C Assignment Routes
$router->get('/api/v1/course-offerings/{id}/assignments', [AssignmentController::class, 'getAssignments']);
$router->post('/api/v1/course-offerings/{id}/assignments', [AssignmentController::class, 'createAssignment']);
$router->get('/api/v1/assignments/{id}', [AssignmentController::class, 'getAssignment']);
$router->put('/api/v1/assignments/{id}', [AssignmentController::class, 'updateAssignment']);
$router->delete('/api/v1/assignments/{id}', [AssignmentController::class, 'deleteAssignment']);
$router->post('/api/v1/assignments/{id}/submit', [AssignmentController::class, 'submitAssignment']);
$router->get('/api/v1/assignments/{id}/submissions', [AssignmentController::class, 'getSubmissions']);
$router->get('/api/v1/submissions/{id}/download', [AssignmentController::class, 'downloadSubmission']);
$router->put('/api/v1/submissions/{id}/grade', [AssignmentController::class, 'gradeSubmission']);

// Phase 6C Quiz Routes
$router->get('/api/v1/course-offerings/{id}/quizzes', [QuizController::class, 'getQuizzes']);
$router->post('/api/v1/course-offerings/{id}/quizzes', [QuizController::class, 'createQuiz']);
$router->get('/api/v1/quizzes/{id}', [QuizController::class, 'getQuiz']);
$router->put('/api/v1/quizzes/{id}', [QuizController::class, 'updateQuiz']);
$router->delete('/api/v1/quizzes/{id}', [QuizController::class, 'deleteQuiz']);
$router->post('/api/v1/quizzes/{id}/questions', [QuizController::class, 'addQuestion']);
$router->post('/api/v1/quizzes/{id}/start', [QuizController::class, 'startAttempt']);
$router->post('/api/v1/quiz-attempts/{id}/submit', [QuizController::class, 'submitAttempt']);
$router->get('/api/v1/quiz-attempts/{id}', [QuizController::class, 'getAttemptDetails']);

// Phase 6C Gradebook & Student Results Routes
$router->get('/api/v1/course-offerings/{id}/grades', [GradebookController::class, 'getCourseGrades']);
$router->put('/api/v1/course-offerings/{id}/grades', [GradebookController::class, 'saveGrade']);
$router->post('/api/v1/course-offerings/{id}/grades/publish', [GradebookController::class, 'publishGrades']);
$router->get('/api/v1/student/grades', [GradebookController::class, 'getStudentGrades']);

// Phase 6D Attendance & Practical Workshop Tracking Routes
$router->get('/api/v1/attendance/sessions', [AttendanceController::class, 'getSessions']);
$router->post('/api/v1/attendance/sessions', [AttendanceController::class, 'createSession']);
$router->get('/api/v1/attendance/sessions/{id}', [AttendanceController::class, 'getSessionById']);
$router->put('/api/v1/attendance/sessions/{id}', [AttendanceController::class, 'updateSession']);
$router->delete('/api/v1/attendance/sessions/{id}', [AttendanceController::class, 'deleteSession']);
$router->post('/api/v1/attendance/sessions/{id}/records', [AttendanceController::class, 'saveSessionRecords']);
$router->get('/api/v1/attendance/me', [AttendanceController::class, 'getMyAttendance']);

// Phase 6D Attendance Reports Routes
$router->get('/api/v1/attendance/course/{id}/matrix', [AttendanceReportController::class, 'getCourseMatrix']);
$router->get('/api/v1/attendance/department/report', [AttendanceReportController::class, 'getDepartmentReport']);
$router->get('/api/v1/attendance/student/{id}/report', [AttendanceReportController::class, 'getStudentReport']);

// Phase 6E Announcements & Communication Routes
$router->get('/api/v1/announcements', [AnnouncementController::class, 'getAnnouncements']);
$router->get('/api/v1/announcements/{id}', [AnnouncementController::class, 'getAnnouncementById']);
$router->post('/api/v1/announcements', [AnnouncementController::class, 'createAnnouncement']);
$router->put('/api/v1/announcements/{id}', [AnnouncementController::class, 'updateAnnouncement']);
$router->delete('/api/v1/announcements/{id}', [AnnouncementController::class, 'deleteAnnouncement']);
$router->post('/api/v1/announcements/{id}/publish', [AnnouncementController::class, 'publishAnnouncement']);
$router->post('/api/v1/announcements/{id}/archive', [AnnouncementController::class, 'archiveAnnouncement']);
$router->get('/api/v1/announcements/{id}/download', [AnnouncementController::class, 'downloadAttachment']);

// Phase 6E Notifications Routes
$router->get('/api/v1/notifications', [NotificationController::class, 'getNotifications']);
$router->get('/api/v1/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
$router->get('/api/v1/notifications/{id}', [NotificationController::class, 'getNotificationById']);
$router->put('/api/v1/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
$router->put('/api/v1/notifications/{id}/unread', [NotificationController::class, 'markAsUnread']);
$router->post('/api/v1/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
$router->delete('/api/v1/notifications/{id}', [NotificationController::class, 'deleteNotification']);

// Default Root Route
$router->get('/', function (Request $request) {
    Response::json([
        'name' => 'Gilgil Technical and Vocational College LMS API',
        'status' => 'active',
        'auth_endpoints' => [
            'login'  => 'POST /api/v1/auth/login',
            'logout' => 'POST /api/v1/auth/logout',
            'me'     => 'GET /api/v1/auth/me',
        ],
        'docs' => '/api/v1/health',
    ], 'Welcome to Gilgil TVC LMS Backend API');
});

// Dispatch HTTP Request
$router->dispatch(new Request());
