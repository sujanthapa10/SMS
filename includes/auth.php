<?php
require_once __DIR__ . '/paths.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function dashboard_path_for_role(string $role, string $prefix = ''): string
{
    return match ($role) {
        'admin' => app_url('admin/admin_dashboard.php'),
        'teacher' => app_url('teachers/teacher_dashboard.php'),
        'student' => app_url('students/student_dashboard.php'),
        default => app_url('login.php'),
    };
}

function require_login(string $requiredRole, string $prefix = ''): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header('Location: ' . app_url('login.php'));
        exit;
    }

    if ($_SESSION['role'] !== $requiredRole) {
        header('Location: ' . dashboard_path_for_role($_SESSION['role'], $prefix));
        exit;
    }
}

function current_user_name(string $fallback = 'User'): string
{
    return $_SESSION['name'] ?? $fallback;
}
