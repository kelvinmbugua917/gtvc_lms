<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Department;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\AttendanceSession;
use App\Models\Announcement;
use App\Models\FeeStructure;
use App\Models\StudentFeeAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\SystemSetting;

/**
 * Controller for Web Front-End Page Views
 */
class ViewController extends Controller
{
    /**
     * Home / Landing page redirect
     */
    public function home(Request $request): void
    {
        Session::start();
        $user = Session::get('user');
        if ($user) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }

    /**
     * Render Login View
     */
    public function loginView(Request $request): void
    {
        Session::start();
        if (Session::get('user')) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', [
            'pageTitle' => 'GTVC LMS - System Portal Login'
        ], 'layouts/guest');
    }

    /**
     * Role-Aware Dashboard Router
     */
    public function dashboard(Request $request): void
    {
        $user = AuthMiddleware::authenticate($request);
        $roles = array_map(fn($r) => is_array($r) ? ($r['name'] ?? '') : (string)$r, $user['roles'] ?? []);

        if (in_array('super_admin', $roles, true) || in_array('admin', $roles, true)) {
            $this->adminDashboard($request);
        } elseif (in_array('accountant', $roles, true) || in_array('bursar', $roles, true)) {
            $this->accountantDashboard($request);
        } elseif (in_array('hod', $roles, true)) {
            $this->hodDashboard($request);
        } elseif (in_array('lecturer', $roles, true) || in_array('trainer', $roles, true)) {
            $this->lecturerDashboard($request);
        } else {
            $this->studentDashboard($request);
        }
    }

    // =========================================================================
    // STUDENT VIEWS
    // =========================================================================

    public function studentDashboard(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/dashboard', [
            'pageTitle' => 'Student Dashboard | GTVC LMS',
            'user' => $user,
            'activeCoursesCount' => 5,
            'pendingAssignmentsCount' => 2,
            'attendanceRate' => 92,
            'feeClearanceStatus' => 'CLEARED'
        ]);
    }

    public function studentCourses(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/courses', [
            'pageTitle' => 'My Courses | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentMaterials(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/materials', [
            'pageTitle' => 'Learning Materials | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentAssignments(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/assignments', [
            'pageTitle' => 'Assignments & Submissions | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentQuizzes(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/quizzes', [
            'pageTitle' => 'Quizzes & CBT Exams | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentGrades(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/grades', [
            'pageTitle' => 'Academic Transcript & TVET CBET Grades | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentAttendance(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/attendance', [
            'pageTitle' => 'My Attendance & Workshop Logbook | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentAnnouncements(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/announcements', [
            'pageTitle' => 'Institutional Notices & Bulletins | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentProfile(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/profile', [
            'pageTitle' => 'My Profile & Account Security | GTVC LMS',
            'user' => $user
        ]);
    }

    public function studentFees(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['student'], $request);
        $this->view('student/fees', [
            'pageTitle' => 'Fee Account & Exam Clearance | GTVC LMS',
            'user' => $user
        ]);
    }

    // =========================================================================
    // LECTURER VIEWS
    // =========================================================================

    public function lecturerDashboard(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['lecturer', 'trainer', 'hod', 'admin'], $request);
        $this->view('lecturer/dashboard', [
            'pageTitle' => 'Trainer Dashboard | GTVC LMS',
            'user' => $user
        ]);
    }

    public function lecturerCourses(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['lecturer', 'trainer', 'hod', 'admin'], $request);
        $this->view('lecturer/courses', [
            'pageTitle' => 'Assigned Units & Class Roster | GTVC LMS',
            'user' => $user
        ]);
    }

    public function lecturerGradebook(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['lecturer', 'trainer', 'hod', 'admin'], $request);
        $this->view('lecturer/gradebook', [
            'pageTitle' => 'Course Gradebook & TVET CBET Marking | GTVC LMS',
            'user' => $user
        ]);
    }

    public function lecturerAttendance(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['lecturer', 'trainer', 'hod', 'admin'], $request);
        $this->view('lecturer/attendance', [
            'pageTitle' => 'Attendance Session Register | GTVC LMS',
            'user' => $user
        ]);
    }

    public function lecturerModules(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['lecturer', 'trainer', 'hod', 'admin'], $request);
        $this->view('lecturer/modules', [
            'pageTitle' => 'Course Modules & Content Builder | GTVC LMS',
            'user' => $user
        ]);
    }

    // =========================================================================
    // HOD VIEWS
    // =========================================================================

    public function hodDashboard(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['hod', 'admin', 'super_admin'], $request);
        $this->view('hod/dashboard', [
            'pageTitle' => 'Department Overview | GTVC LMS',
            'user' => $user
        ]);
    }

    public function hodStudents(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['hod', 'admin', 'super_admin'], $request);
        $this->view('hod/students', [
            'pageTitle' => 'Department Students & Risk Monitoring | GTVC LMS',
            'user' => $user
        ]);
    }

    public function hodLecturers(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['hod', 'admin', 'super_admin'], $request);
        $this->view('hod/lecturers', [
            'pageTitle' => 'Department Trainers & Teaching Loads | GTVC LMS',
            'user' => $user
        ]);
    }

    public function hodAnalytics(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['hod', 'admin', 'super_admin'], $request);
        $this->view('hod/analytics', [
            'pageTitle' => 'Academic Performance & TVET Audit Reports | GTVC LMS',
            'user' => $user
        ]);
    }

    // =========================================================================
    // ACCOUNTANT / FINANCE VIEWS
    // =========================================================================

    public function accountantDashboard(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/dashboard', [
            'pageTitle' => 'Bursar & Finance Dashboard | GTVC LMS',
            'user' => $user
        ]);
    }

    public function accountantFeeStructures(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/fee_structures', [
            'pageTitle' => 'Fee Structure Management | GTVC LMS',
            'user' => $user
        ]);
    }

    public function accountantStudentAccounts(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/student_accounts', [
            'pageTitle' => 'Student Accounts Ledger | GTVC LMS',
            'user' => $user
        ]);
    }

    public function accountantPayments(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/payments', [
            'pageTitle' => 'Payments & M-Pesa Verification | GTVC LMS',
            'user' => $user
        ]);
    }

    public function accountantInvoices(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/invoices', [
            'pageTitle' => 'Invoicing & Term Billing | GTVC LMS',
            'user' => $user
        ]);
    }

    public function accountantClearance(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['accountant', 'bursar', 'admin', 'super_admin'], $request);
        $this->view('accountant/clearance', [
            'pageTitle' => 'Exam Clearance Gatekeeper | GTVC LMS',
            'user' => $user
        ]);
    }

    // =========================================================================
    // ADMIN VIEWS
    // =========================================================================

    public function adminDashboard(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'super_admin'], $request);
        $this->view('admin/dashboard', [
            'pageTitle' => 'System Administration | GTVC LMS',
            'user' => $user
        ]);
    }

    public function adminUsers(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'super_admin'], $request);
        $this->view('admin/users', [
            'pageTitle' => 'User & Role Management | GTVC LMS',
            'user' => $user
        ]);
    }

    public function adminAcademic(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'super_admin'], $request);
        $this->view('admin/academic', [
            'pageTitle' => 'Academic Hierarchy Setup | GTVC LMS',
            'user' => $user
        ]);
    }

    public function adminSettings(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'super_admin'], $request);
        $this->view('admin/settings', [
            'pageTitle' => 'System Settings & Thresholds | GTVC LMS',
            'user' => $user
        ]);
    }

    public function adminAuditLogs(Request $request): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'super_admin'], $request);
        $this->view('admin/audit_logs', [
            'pageTitle' => 'System Audit Logs & Security Traces | GTVC LMS',
            'user' => $user
        ]);
    }
}
