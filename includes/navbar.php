<?php
require_once __DIR__ . '/paths.php';

$dashboardLabel = $dashboardLabel ?? 'WIN Dashboard';
?>
        <header class="dashboard-header">
            <a class="brand" href="<?php echo htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <span class="brand-mark">
                    <img src="<?php echo htmlspecialchars(app_url('images/logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="WIN logo">
                </span>
                <span>
                    <strong><?php echo htmlspecialchars($dashboardLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small>Student Management Sytem</small>
                </span>
            </a>

            <div class="header-actions">
                <a class="button" href="<?php echo htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8'); ?>">Home</a>
                <a class="button button-primary" href="<?php echo htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Logout</a>
            </div>
        </header>
