<?php

declare(strict_types=1);

/**
 * Gilgil Technical and Vocational College (GTVC) LMS - Production Front Controller
 * Apache/Nginx Front Controller Entry Point (PHP 8.x + MySQL / MariaDB)
 */

use App\Config\AppConfig;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

// PSR-4 Autoloader for App Namespace
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Environment Configuration
AppConfig::loadEnv(__DIR__ . '/../.env');

// Start Secure Session Management
Session::start();

// Initialize HTTP Router
$router = new Router();

// =============================================================================
// WEB PAGE VIEWS (Server-Rendered Frontend)
// =============================================================================

$router->get('/', [App\Controllers\ViewController::class, 'home']);
$router->get('/login', [App\Controllers\ViewController::class, 'loginView']);
$router->get('/dashboard', [App\Controllers\ViewController::class, 'dashboard']);

// Student Portal Routes
$router->get('/student/courses', [App\Controllers\ViewController::class, 'studentCourses']);
$router->get('/student/materials', [App\Controllers\ViewController::class, 'studentMaterials']);
$router->get('/student/assignments', [App\Controllers\ViewController::class, 'studentAssignments']);
$router->get('/student/quizzes', [App\Controllers\ViewController::class, 'studentQuizzes']);
$router->get('/student/grades', [App\Controllers\ViewController::class, 'studentGrades']);
$router->get('/student/attendance', [App\Controllers\ViewController::class, 'studentAttendance']);
$router->get('/student/announcements', [App\Controllers\ViewController::class, 'studentAnnouncements']);
$router->get('/student/profile', [App\Controllers\ViewController::class, 'studentProfile']);
$router->get('/student/fees', [App\Controllers\ViewController::class, 'studentFees']);

// Lecturer / Trainer Routes
$router->get('/lecturer/courses', [App\Controllers\ViewController::class, 'lecturerCourses']);
$router->get('/lecturer/gradebook', [App\Controllers\ViewController::class, 'lecturerGradebook']);
$router->get('/lecturer/attendance', [App\Controllers\ViewController::class, 'lecturerAttendance']);
$router->get('/lecturer/modules', [App\Controllers\ViewController::class, 'lecturerModules']);

// HOD Routes
$router->get('/hod/students', [App\Controllers\ViewController::class, 'hodStudents']);
$router->get('/hod/lecturers', [App\Controllers\ViewController::class, 'hodLecturers']);
$router->get('/hod/analytics', [App\Controllers\ViewController::class, 'hodAnalytics']);

// Accountant / Finance Routes
$router->get('/accountant/fee-structures', [App\Controllers\ViewController::class, 'accountantFeeStructures']);
$router->get('/accountant/student-accounts', [App\Controllers\ViewController::class, 'accountantStudentAccounts']);
$router->get('/accountant/payments', [App\Controllers\ViewController::class, 'accountantPayments']);
$router->get('/accountant/invoices', [App\Controllers\ViewController::class, 'accountantInvoices']);
$router->get('/accountant/clearance', [App\Controllers\ViewController::class, 'accountantClearance']);

// Administrator Routes
$router->get('/admin/users', [App\Controllers\ViewController::class, 'adminUsers']);
$router->get('/admin/academic', [App\Controllers\ViewController::class, 'adminAcademic']);
$router->get('/admin/settings', [App\Controllers\ViewController::class, 'adminSettings']);
$router->get('/admin/audit-logs', [App\Controllers\ViewController::class, 'adminAuditLogs']);

// =============================================================================
// API REST ENDPOINTS
// =============================================================================

// Auth API
$router->post('/api/v1/auth/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/api/v1/auth/logout', [App\Controllers\AuthController::class, 'logout']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);
$router->get('/api/v1/auth/me', [App\Controllers\AuthController::class, 'me']);

// Core Entities API
$router->get('/api/v1/users', [App\Controllers\UserController::class, 'index']);
$router->get('/api/v1/academic/departments', [App\Controllers\AcademicController::class, 'departments']);
$router->get('/api/v1/students', [App\Controllers\StudentController::class, 'index']);
$router->get('/api/v1/learning/materials', [App\Controllers\LearningController::class, 'materials']);
$router->get('/api/v1/assignments', [App\Controllers\AssessmentController::class, 'assignments']);
$router->get('/api/v1/finance/summary', [App\Controllers\FinanceController::class, 'summary']);
$router->get('/api/v1/admin/audit-logs', [App\Controllers\AdminController::class, 'auditLogs']);

// Dispatch Request
$request = new Request();
$router->dispatch($request);
