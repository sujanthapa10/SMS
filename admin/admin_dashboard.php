<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');

require __DIR__ . '/../database/connection.php';

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function count_rows(mysqli $conn, string $table): int
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM {$table}");
    return (int) ($result->fetch_assoc()['total'] ?? 0);
}

$name = current_user_name('Admin User');
$stats = [
    ['label' => 'Students', 'value' => count_rows($conn, 'students')],
    ['label' => 'Teachers', 'value' => count_rows($conn, 'teachers')],
    ['label' => 'Units', 'value' => count_rows($conn, 'courses')],
    ['label' => 'Classes', 'value' => count_rows($conn, 'classes')],
];

$courses = $conn->query(
    "SELECT cl.class_name, c.course_code, c.course_name, s.semester_name, s.academic_year,
            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
     FROM classes cl
     JOIN courses c ON c.id = cl.course_id
     JOIN teachers t ON t.id = cl.teacher_id
     JOIN semesters s ON s.id = cl.semester_id
     ORDER BY s.academic_year DESC, s.semester_name, cl.class_name
     LIMIT 6"
);

$attendance = $conn->query(
    "SELECT a.week_number, a.status, c.course_code, cl.class_name,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name
     FROM attendance a
     JOIN students s ON s.id = a.student_id
     JOIN classes cl ON cl.id = a.class_id
     JOIN courses c ON c.id = cl.course_id
     ORDER BY a.marked_at DESC, a.id DESC
     LIMIT 6"
);
?>
<?php
$pageTitle = 'Admin Dashboard | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'admin';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
            <section class="hero">
                <div class="hero-card">
                    <p class="eyebrow">Admin dashboard</p>
                    <h1>Good day, <?php echo esc($name); ?>.</h1>
                    <p>Monitor the whole WIN academic system from one responsive dashboard connected to your localhost database.</p>
                </div>
                <aside class="panel profile-card">
                    <div class="profile-row"><span class="profile-label">Role</span><span class="profile-value">Administrator</span></div>
                    <div class="profile-row"><span class="profile-label">Database</span><span class="profile-value">sms</span></div>
                    <div class="profile-row"><span class="profile-label">Status</span><span class="profile-value">Connected</span></div>
                </aside>
            </section>

            <section class="stats-grid" aria-label="Admin statistics">
                <?php foreach ($stats as $stat): ?>
                    <div class="stat-card"><span><?php echo esc($stat['label']); ?></span><strong><?php echo esc($stat['value']); ?></strong></div>
                <?php endforeach; ?>
            </section>

            <section class="content-grid">
                <div class="panel">
                    <div class="panel-title"><h2>2026 class allocation</h2><span class="badge">Teachers</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Class</th><th>Unit</th><th>Semester</th><th>Teacher</th></tr></thead>
                            <tbody>
                                <?php if ($courses && $courses->num_rows): ?>
                                    <?php while ($course = $courses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($course['class_name']); ?></td>
                                            <td><?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?></td>
                                            <td><?php echo esc($course['semester_name'] . ' ' . $course['academic_year']); ?></td>
                                            <td><?php echo esc($course['teacher_name']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No classes found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title"><h2>Recent attendance</h2><span class="badge badge-green">Live</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Week</th><th>Student</th><th>Class</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($attendance && $attendance->num_rows): ?>
                                    <?php while ($row = $attendance->fetch_assoc()): ?>
                                        <tr>
                                            <td>Week <?php echo esc($row['week_number']); ?></td>
                                            <td><?php echo esc($row['student_name']); ?></td>
                                            <td><?php echo esc($row['class_name']); ?></td>
                                            <td><span class="badge"><?php echo esc($row['status']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No attendance records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
