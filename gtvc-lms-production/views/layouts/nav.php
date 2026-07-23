<?php
$user = $currentUser ?? [];
$roles = array_map(fn($r) => is_array($r) ? ($r['name'] ?? '') : (string)$r, $user['roles'] ?? []);
$primaryRole = $roles[0] ?? 'student';

$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$baseUrl = \App\Core\View::baseUrl();
if (!empty($baseUrl) && str_starts_with($rawPath, $baseUrl)) {
    $currentPath = substr($rawPath, strlen($baseUrl));
} else {
    $currentPath = $rawPath;
}
if (str_starts_with($currentPath, '/index.php')) {
    $currentPath = substr($currentPath, 10);
}
$currentPath = '/' . trim($currentPath, '/');
?>

<div class="sidebar-header">
    <div class="logo-badge">G</div>
    <div style="flex: 1;">
        <div class="brand-title">Gilgil TVC</div>
        <div class="brand-subtitle">Learning Management System</div>
    </div>
    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close navigation menu">✕</button>
</div>

<nav class="sidebar-nav">
    <div class="nav-section-title">Navigation</div>
    
    <a href="<?= \App\Core\View::url('/dashboard') ?>" class="nav-link <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
        <span>📊</span> Dashboard
    </a>

    <?php if (in_array('student', $roles, true) && !in_array('admin', $roles, true)): ?>
        <a href="<?= \App\Core\View::url('/student/courses') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/courses') ? 'active' : '' ?>">
            <span>📚</span> My Courses
        </a>
        <a href="<?= \App\Core\View::url('/student/materials') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/materials') ? 'active' : '' ?>">
            <span>📄</span> Learning Materials
        </a>
        <a href="<?= \App\Core\View::url('/student/assignments') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/assignments') ? 'active' : '' ?>">
            <span>📝</span> Assignments
        </a>
        <a href="<?= \App\Core\View::url('/student/quizzes') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/quizzes') ? 'active' : '' ?>">
            <span>💡</span> Quizzes & Exams
        </a>
        <a href="<?= \App\Core\View::url('/student/grades') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/grades') ? 'active' : '' ?>">
            <span>🏆</span> CBET Grades
        </a>
        <a href="<?= \App\Core\View::url('/student/attendance') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/attendance') ? 'active' : '' ?>">
            <span>📋</span> Attendance Log
        </a>
        <a href="<?= \App\Core\View::url('/student/fees') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/fees') ? 'active' : '' ?>">
            <span>💳</span> Fee Clearance
        </a>
        <a href="<?= \App\Core\View::url('/student/announcements') ?>" class="nav-link <?= str_starts_with($currentPath, '/student/announcements') ? 'active' : '' ?>">
            <span>📢</span> Bulletins
        </a>
    <?php endif; ?>

    <?php if (in_array('lecturer', $roles, true) || in_array('trainer', $roles, true)): ?>
        <a href="<?= \App\Core\View::url('/lecturer/courses') ?>" class="nav-link <?= str_starts_with($currentPath, '/lecturer/courses') ? 'active' : '' ?>">
            <span>🎓</span> Assigned Units
        </a>
        <a href="<?= \App\Core\View::url('/lecturer/gradebook') ?>" class="nav-link <?= str_starts_with($currentPath, '/lecturer/gradebook') ? 'active' : '' ?>">
            <span>📊</span> Course Gradebook
        </a>
        <a href="<?= \App\Core\View::url('/lecturer/attendance') ?>" class="nav-link <?= str_starts_with($currentPath, '/lecturer/attendance') ? 'active' : '' ?>">
            <span>⏱️</span> Attendance Register
        </a>
        <a href="<?= \App\Core\View::url('/lecturer/modules') ?>" class="nav-link <?= str_starts_with($currentPath, '/lecturer/modules') ? 'active' : '' ?>">
            <span>🗂️</span> Content Builder
        </a>
    <?php endif; ?>

    <?php if (in_array('hod', $roles, true)): ?>
        <a href="<?= \App\Core\View::url('/hod/students') ?>" class="nav-link <?= str_starts_with($currentPath, '/hod/students') ? 'active' : '' ?>">
            <span>👥</span> Dept Students
        </a>
        <a href="<?= \App\Core\View::url('/hod/lecturers') ?>" class="nav-link <?= str_starts_with($currentPath, '/hod/lecturers') ? 'active' : '' ?>">
            <span>👨‍🏫</span> Dept Trainers
        </a>
        <a href="<?= \App\Core\View::url('/hod/analytics') ?>" class="nav-link <?= str_starts_with($currentPath, '/hod/analytics') ? 'active' : '' ?>">
            <span>📈</span> Academic Analytics
        </a>
    <?php endif; ?>

    <?php if (in_array('accountant', $roles, true) || in_array('bursar', $roles, true)): ?>
        <a href="<?= \App\Core\View::url('/accountant/fee-structures') ?>" class="nav-link <?= str_starts_with($currentPath, '/accountant/fee-structures') ? 'active' : '' ?>">
            <span>📄</span> Fee Structures
        </a>
        <a href="<?= \App\Core\View::url('/accountant/student-accounts') ?>" class="nav-link <?= str_starts_with($currentPath, '/accountant/student-accounts') ? 'active' : '' ?>">
            <span>📒</span> Student Accounts
        </a>
        <a href="<?= \App\Core\View::url('/accountant/payments') ?>" class="nav-link <?= str_starts_with($currentPath, '/accountant/payments') ? 'active' : '' ?>">
            <span>💸</span> M-Pesa Payments
        </a>
        <a href="<?= \App\Core\View::url('/accountant/invoices') ?>" class="nav-link <?= str_starts_with($currentPath, '/accountant/invoices') ? 'active' : '' ?>">
            <span>📑</span> Term Invoices
        </a>
        <a href="<?= \App\Core\View::url('/accountant/clearance') ?>" class="nav-link <?= str_starts_with($currentPath, '/accountant/clearance') ? 'active' : '' ?>">
            <span>🛡️</span> Exam Clearance
        </a>
    <?php endif; ?>

    <?php if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true)): ?>
        <div class="nav-section-title" style="margin-top: 1rem;">System Administration</div>
        <a href="<?= \App\Core\View::url('/admin/users') ?>" class="nav-link <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
            <span>👥</span> Users & Roles
        </a>
        <a href="<?= \App\Core\View::url('/admin/academic') ?>" class="nav-link <?= str_starts_with($currentPath, '/admin/academic') ? 'active' : '' ?>">
            <span>🏛️</span> Academic Hierarchy
        </a>
        <a href="<?= \App\Core\View::url('/admin/settings') ?>" class="nav-link <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
            <span>⚙️</span> System Settings
        </a>
        <a href="<?= \App\Core\View::url('/admin/audit-logs') ?>" class="nav-link <?= str_starts_with($currentPath, '/admin/audit-logs') ? 'active' : '' ?>">
            <span>📜</span> Security Audit Logs
        </a>
    <?php endif; ?>

    <div class="nav-section-title" style="margin-top: 1.5rem;">Account</div>
    <a href="<?= \App\Core\View::url('/student/profile') ?>" class="nav-link <?= $currentPath === '/student/profile' ? 'active' : '' ?>">
        <span>👤</span> My Profile
    </a>
    <a href="<?= \App\Core\View::url('/logout') ?>" class="nav-link" style="color: #f43f5e;">
        <span>🚪</span> Sign Out
    </a>
</nav>
