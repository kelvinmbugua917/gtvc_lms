<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;

class ViewController extends Controller
{
    private function renderPage(string $viewPath, string $pageTitle = 'Dashboard')
    {
        View::render($viewPath, ['pageTitle' => $pageTitle], 'layouts/main');
    }

    public function home() { View::render('auth/login', ['pageTitle' => 'Gilgil TVC LMS - Login'], 'layouts/guest'); }
    public function loginView() { View::render('auth/login', ['pageTitle' => 'Gilgil TVC LMS - Login'], 'layouts/guest'); }
    public function dashboard() { 
        $this->renderPage('student/dashboard', 'Student Portal Dashboard'); 
    }
    
    // Student
    public function studentCourses() { $this->renderPage('student/courses', 'My Enrolled Courses'); }
    public function studentMaterials() { $this->renderPage('student/materials', 'Learning Materials'); }
    public function studentAssignments() { $this->renderPage('student/assignments', 'Assignments & Assessments'); }
    public function studentQuizzes() { $this->renderPage('student/quizzes', 'Online Quizzes & CBT'); }
    public function studentGrades() { $this->renderPage('student/grades', 'Academic Transcripts & Grades'); }
    public function studentAttendance() { $this->renderPage('student/attendance', 'Attendance Records'); }
    public function studentAnnouncements() { $this->renderPage('student/announcements', 'Official Announcements'); }
    public function studentProfile() { $this->renderPage('student/profile', 'My Profile Settings'); }
    public function studentFees() { $this->renderPage('student/fees', 'Fee Balances & Accounts'); }
    
    // Lecturer
    public function lecturerCourses() { $this->renderPage('lecturer/courses', 'Assigned Courses & Units'); }
    public function lecturerGradebook() { $this->renderPage('lecturer/gradebook', 'Academic Gradebook Management'); }
    public function lecturerAttendance() { $this->renderPage('lecturer/attendance', 'Mark Class Attendance'); }
    public function lecturerModules() { $this->renderPage('lecturer/modules', 'Course Modules & Content'); }
    
    // HOD
    public function hodStudents() { $this->renderPage('hod/students', 'Department Students Registry'); }
    public function hodLecturers() { $this->renderPage('hod/lecturers', 'Department Lecturers Management'); }
    public function hodAnalytics() { $this->renderPage('hod/analytics', 'Departmental Performance Analytics'); }
    
    // Accountant
    public function accountantFeeStructures() { $this->renderPage('accountant/fee_structures', 'Manage Academic Fee Structures'); }
    public function accountantStudentAccounts() { $this->renderPage('accountant/student_accounts', 'Student Financial Accounts'); }
    public function accountantPayments() { $this->renderPage('accountant/payments', 'Record & Verify Payments'); }
    public function accountantInvoices() { $this->renderPage('accountant/invoices', 'Generate Fee Invoices'); }
    public function accountantClearance() { $this->renderPage('accountant/clearance', 'Financial Clearance'); }
    
    // Admin
    public function adminUsers() { $this->renderPage('admin/users', 'System Users & Role Management'); }
    public function adminAcademic() { $this->renderPage('admin/academic', 'Academic Calendar & Programs Setup'); }
    public function adminSettings() { $this->renderPage('admin/settings', 'Global System Configuration'); }
    public function adminAuditLogs() { $this->renderPage('admin/audit_logs', 'Security Audit Trail'); }
}
