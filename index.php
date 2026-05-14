<?php
require_once __DIR__ . '/includes/paths.php';

$roles = [
    [
        'name' => 'Students',
        'summary' => 'View courses, attendance, results, and profile updates.',
        'href' => 'students/student_dashboard.php',
        'icon' => 'student',
    ],
    [
        'name' => 'Teachers',
        'summary' => 'Manage classes, student lists, and daily attendance.',
        'href' => 'teachers/teacher_dashboard.php',
        'icon' => 'teacher',
    ],
    [
        'name' => 'Admins',
        'summary' => 'Control students, teachers, courses, and reports.',
        'href' => 'admin/admin_dashboard.php',
        'icon' => 'admin',
    ],
];

$features = [
    'Role-based dashboards',
    'Attendance tracking',
    'Course management',
    'Student records',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WIN Student Management Sytem</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_url('css/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="<?php echo htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="WIN Student Management Sytem home">
                <span class="brand-mark">
                    <img src="<?php echo htmlspecialchars(app_url('images/logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="WIN logo">
                </span>
                <span>
                    <strong>WIN</strong>
                    <small>Student Management Sytem</small>
                </span>
            </a>

            <nav class="site-nav" aria-label="Main navigation">
                <a href="<?php echo htmlspecialchars(app_url('index.php#roles'), ENT_QUOTES, 'UTF-8'); ?>">Portals</a>
                <a href="<?php echo htmlspecialchars(app_url('index.php#features'), ENT_QUOTES, 'UTF-8'); ?>">Features</a>
                <a href="<?php echo htmlspecialchars(app_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>" class="nav-button">Login</a>
            </nav>
        </header>

        <main>
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero-content">
                    <p class="eyebrow">WIN Student Management Sytem</p>
                    <h1 id="hero-title">A smarter way to manage students, teachers, courses and attendance.</h1>
                    <p class="hero-copy">
                        A beautiful dashboard entry point for WIN, designed for fast access, clear records and confident daily academic work.
                    </p>

                    <div class="hero-actions">
                        <a class="primary-button" href="<?php echo htmlspecialchars(app_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>">Get Started</a>
                        <a class="secondary-button" href="<?php echo htmlspecialchars(app_url('index.php#roles'), ENT_QUOTES, 'UTF-8'); ?>">Choose Portal</a>
                    </div>
                </div>

                <aside class="hero-panel" aria-label="WIN overview">
                    <div class="panel-top">
                        <span class="status-dot"></span>
                        <span>WIN overview</span>
                    </div>
                    <div class="metric-grid">
                        <div>
                            <strong>3</strong>
                            <span>User roles</span>
                        </div>
                        <div>
                            <strong>24/7</strong>
                            <span>Access</span>
                        </div>
                        <div>
                            <strong>100%</strong>
                            <span>Responsive</span>
                        </div>
                        <div>
                            <strong>Fast</strong>
                            <span>Workflow</span>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="role-section" id="roles" aria-labelledby="roles-title">
                <div class="section-heading">
                    <p class="eyebrow">Portals</p>
                    <h2 id="roles-title">Select your workspace</h2>
                </div>

                <div class="role-grid">
                    <?php foreach ($roles as $role): ?>
                        <a class="role-card" href="<?php echo htmlspecialchars(app_url($role['href']), ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="role-icon role-icon-<?php echo htmlspecialchars($role['icon']); ?>" aria-hidden="true"></span>
                            <span class="role-text">
                                <strong><?php echo htmlspecialchars($role['name']); ?></strong>
                                <small><?php echo htmlspecialchars($role['summary']); ?></small>
                            </span>
                            <span class="card-arrow" aria-hidden="true">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="feature-section" id="features" aria-labelledby="features-title">
                <div>
                    <p class="eyebrow">What it handles</p>
                    <h2 id="features-title">Built for everyday WIN academic admin</h2>
                </div>

                <div class="feature-list">
                    <?php foreach ($features as $feature): ?>
                        <span><?php echo htmlspecialchars($feature); ?></span>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    

    <script src="<?php echo htmlspecialchars(app_url('js/script.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
