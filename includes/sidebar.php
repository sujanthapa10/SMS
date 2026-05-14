<?php
require_once __DIR__ . '/paths.php';

$activeRole = $activeRole ?? '';

$role = $_SESSION['role'] ?? $activeRole;

$sidebarLinks = match ($role) {
    'admin' => [
        'admin' => ['label' => 'Dashboard', 'href' => 'admin/admin_dashboard.php'],
        'admins' => ['label' => 'Admins', 'href' => 'admin/manage_admins.php'],
        'students' => ['label' => 'Students', 'href' => 'admin/manage_students.php'],
        'teachers' => ['label' => 'Teachers', 'href' => 'admin/manage_teachers.php'],
        'courses' => ['label' => 'Units', 'href' => 'admin/manage_courses.php'],
        'semesters' => ['label' => 'Semesters', 'href' => 'admin/manage_semesters.php'],
        'classes' => ['label' => 'Classes', 'href' => 'admin/manage_classes.php'],
        'attendance' => ['label' => 'Attendance', 'href' => 'admin/attendance_sheet.php'],
        'grades' => ['label' => 'Grades', 'href' => 'admin/manage_grades.php'],
    ],
    'teacher' => [
        'teacher' => ['label' => 'Dashboard', 'href' => 'teachers/teacher_dashboard.php'],
        'teacher_classes' => ['label' => 'My Classes', 'href' => 'teachers/teacher_classes.php'],
        'teacher_attendance' => ['label' => 'Attendance', 'href' => 'teachers/teacher_attendance.php'],
        'teacher_grades' => ['label' => 'Grades', 'href' => 'teachers/teacher_grades.php'],
    ],
    'student' => [
        'student' => ['label' => 'Dashboard', 'href' => 'students/student_dashboard.php'],
        'student_units' => ['label' => 'My Units', 'href' => 'students/student_units.php'],
        'student_attendance' => ['label' => 'Attendance', 'href' => 'students/student_attendance.php'],
        'student_grades' => ['label' => 'Grades', 'href' => 'students/student_grades.php'],
    ],
    default => [],
};
?>
        <div class="dashboard-layout">
            <aside class="dashboard-sidebar" aria-label="Dashboard navigation">
                <p class="sidebar-title">Portals</p>
                <nav class="sidebar-nav">
                    <?php foreach ($sidebarLinks as $role => $link): ?>
                        <a
                            class="<?php echo $activeRole === $role ? 'is-active' : ''; ?>"
                            href="<?php echo htmlspecialchars(app_url($link['href']), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>
