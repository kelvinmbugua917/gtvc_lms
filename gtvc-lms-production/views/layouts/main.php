<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Core\View::e($pageTitle ?? 'GTVC LMS') ?></title>
    <link rel="stylesheet" href="<?= \App\Core\View::url('/assets/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <!-- Mobile Sidebar Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="app-sidebar" id="appSidebar">
            <?php require __DIR__ . '/nav.php'; ?>
        </aside>

        <!-- Main Wrapper -->
        <main class="app-main">
            <!-- Topbar Header -->
            <header class="app-topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation menu">☰</button>
                    <h1 class="topbar-title"><?= \App\Core\View::e($pageTitle ?? 'Dashboard') ?></h1>
                </div>

                <div class="topbar-right">
                    <?php if (!empty($currentUser)): ?>
                        <div class="user-profile-badge">
                            <div class="user-avatar">
                                <?= strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="user-name">
                                    <?= \App\Core\View::e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?>
                                </div>
                            </div>
                            <span class="user-role-chip">
                                <?= \App\Core\View::e(is_array($currentUser['roles'] ?? null) ? ($currentUser['roles'][0]['name'] ?? $currentUser['roles'][0] ?? 'User') : 'User') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Main Page Content -->
            <div class="content-container">
                <!-- Flash Notification Banners -->
                <?php if (!empty($flashMessage)): ?>
                    <div class="alert alert-success">
                        <span>✅ <?= \App\Core\View::e($flashMessage) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️ <?= \App\Core\View::e($flashError) ?></span>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= \App\Core\View::url('/assets/js/app.js') ?>"></script>
</body>
</html>
