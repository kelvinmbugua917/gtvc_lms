<?php
$user = $currentUser ?? [];
$roles = array_map(fn($r) => is_array($r) ? ($r['name'] ?? '') : (string)$r, $user['roles'] ?? []);
$primaryRole = $roles[0] ?? 'student';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>

<div class="sidebar-header">
    <div class="logo-badge">G</div>
    <div>
        <div class="brand-title">Gilgil TVC</div>
        <div class="brand-subtitle">Learning Management System</div>
    </div>
</div>

<nav class="sidebar-nav">
    <div class="nav-section-title">Navigation</div>
    
    <a href="/dashboard" class="nav-link <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
        <span>📊</span> Dashboard
    </a>

    <?php if (in_array('student', $roles, true) && !in_array('admin', $roles, true)): ?>
        <a href="/student/courses" class="nav-link <?= str_starts_with($currentPath, '/student/courses') ? 'active' : '' ?>">
            <span>📚</span> My Courses
        </a>
        <a href="/student/materials" class="nav-link <?= str_starts_with($currentPath, '/student/materials') ? 'active' : '' ?>">
            <span>📄</span> Learning Materials
        </a>
        <a href="/student/assignments" class="nav-link <?= str_starts_with($currentPath, '/student/assignments') ? 'active' : '' ?>">
            <span>📝</span> Assignments
        </a>
        <a href="/student/quizzes" class="nav-link <?= str_starts_with($currentPath, '/student/quizzes') ? 'active' : '' ?>">
            <span>💡</span> Quizzes & Exams
        </a>
        <a href="/student/grades" class="nav-link <?= str_starts_with($currentPath, '/student/grades') ? 'active' : '' ?>">
            <span>🏆</span> CBET Grades
        </a>
        <a href="/student/attendance" class="nav-link <?= str_starts_with($currentPath, '/student/attendance') ? 'active' : '' ?>">
            <span>📋</span> Attendance Log
        </a>
        <a href="/student/fees" class="nav-link <?= str_starts_with($currentPath, '/student/fees') ? 'active' : '' ?>">
            <span>💳</span> Fee Clearance
        </a>
        <a href="/student/announcements" class="nav-link <?= str_starts_with($currentPath, '/student/announcements') ? 'active' : '' ?>">
            <span>📢</span> Bulletins
        </a>
    <?php endif; ?>

    <?php if (in_array('lecturer', $roles, true) || in_array('trainer', $roles, true)): ?>
        <a href="/lecturer/courses" class="nav-link <?= str_starts_with($currentPath, '/lecturer/courses') ? 'active' : '' ?>">
            <span>🎓</span> Assigned Units
        </a>
        <a href="/lecturer/gradebook" class="nav-link <?= str_starts_with($currentPath, '/lecturer/gradebook') ? 'active' : '' ?>">
            <span>📊</span> Course Gradebook
        </a>
        <a href="/lecturer/attendance" class="nav-link <?= str_starts_with($currentPath, '/lecturer/attendance') ? 'active' : '' ?>">
            <span>⏱️</span> Attendance Register
        </a>
        <a href="/lecturer/modules" class="nav-link <?= str_starts_with($currentPath, '/lecturer/modules') ? 'active' : '' ?>">
            <span>🗂️</span> Content Builder
        </a>
    <?php endif; ?>

    <?php if (in_array('hod', $roles, true)): ?>
        <a href="/hod/students" class="nav-link <?= str_starts_with($currentPath, '/hod/students') ? 'active' : '' ?>">
            <span>👥</span> Dept Students
        </a>
        <a href="/hod/lecturers" class="nav-link <?= str_starts_with($currentPath, '/hod/lecturers') ? 'active' : '' ?>">
            <span>👨‍🏫</span> Dept Trainers
        </a>
        <a href="/hod/analytics" class="nav-link <?= str_starts_with($currentPath, '/hod/analytics') ? 'active' : '' ?>">
            <span>📈</span> Academic Analytics
        </a>
    <?php endif; ?>

    <?php if (in_array('accountant', $roles, true) || in_array('bursar', $roles, true)): ?>
        <a href="/accountant/fee-structures" class="nav-link <?= str_starts_with($currentPath, '/accountant/fee-structures') ? 'active' : '' ?>">
            <span>📄</span> Fee Structures
        </a>
        <a href="/accountant/student-accounts" class="nav-link <?= str_starts_with($currentPath, '/accountant/student-accounts') ? 'active' : '' ?>">
            <span>📒</span> Student Accounts
        </a>
        <a href="/accountant/payments" class="nav-link <?= str_starts_with($currentPath, '/accountant/payments') ? 'active' : '' ?>">
            <span>💸</span> M-Pesa Payments
        </a>
        <a href="/accountant/invoices" class="nav-link <?= str_starts_with($currentPath, '/accountant/invoices') ? 'active' : '' ?>">
            <span>📑</span> Term Invoices
        </a>
        <a href="/accountant/clearance" class="nav-link <?= str_starts_with($currentPath, '/accountant/clearance') ? 'active' : '' ?>">
            <span>🛡️</span> Exam Clearance
        </a>
    <?php endif; ?>

    <?php if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true)): ?>
        <div class="nav-section-title" style="margin-top: 1rem;">System Administration</div>
        <a href="/admin/users" class="nav-link <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
            <span>👥</span> Users & Roles
        </a>
        <a href="/admin/academic" class="nav-link <?= str_starts_with($currentPath, '/admin/academic') ? 'active' : '' ?>">
            <span>🏛️</span> Academic Hierarchy
        </a>
        <a href="/admin/settings" class="nav-link <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
            <span>⚙️</span> System Settings
        </a>
        <a href="/admin/audit-logs" class="nav-link <?= str_starts_with($currentPath, '/admin/audit-logs') ? 'active' : '' ?>">
            <span>📜</span> Security Audit Logs
        </a>
    <?php endif; ?>

    <div class="nav-section-title" style="margin-top: 1.5rem;">Account</div>
    <a href="/student/profile" class="nav-link <?= $currentPath === '/student/profile' ? 'active' : '' ?>">
        <span>👤</span> My Profile
    </a>
    <a href="/logout" class="nav-link" style="color: #f43f5e;">
        <span>🚪</span> Sign Out
    </a>
</nav>
