<?php
require __DIR__ . '/../includes/auth.php';
require_login('student');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$studentId = (int) ($_SESSION['user_id'] ?? 0);

$units = $conn->prepare(
    "SELECT c.course_code, c.course_name, c.description, e.status,
            cl.class_name, cl.room, cl.capacity, sem.semester_name, sem.academic_year,
            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name, t.email AS teacher_email
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN classes cl ON cl.course_id = c.id
     LEFT JOIN semesters sem ON sem.id = cl.semester_id
     LEFT JOIN teachers t ON t.id = cl.teacher_id
     WHERE e.student_id = ?
     ORDER BY sem.academic_year DESC, sem.semester_name, c.course_code"
);
$units->bind_param('i', $studentId);
$units->execute();
$unitRows = $units->get_result();

$pageTitle = 'My Units | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Student';
$activeRole = 'student_units';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="hero">
                    <div class="hero-card">
                        <p class="eyebrow">My units</p>
                        <h1>Enrolled units and classes.</h1>
                        <p>View the classes, rooms, semesters, and teachers connected to your current enrolments.</p>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-title"><h2>Current enrolments</h2><span class="badge">Active units</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Unit</th><th>Class</th><th>Semester</th><th>Teacher</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($unitRows && $unitRows->num_rows): ?>
                                    <?php while ($unit = $unitRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($unit['course_code'] . ' - ' . $unit['course_name']); ?></td>
                                            <td><?php echo esc(($unit['class_name'] ?: 'TBA') . ($unit['room'] ? ' / ' . $unit['room'] : '')); ?></td>
                                            <td><?php echo esc(trim(($unit['semester_name'] ?? '') . ' ' . ($unit['academic_year'] ?? '')) ?: 'TBA'); ?></td>
                                            <td><?php echo esc($unit['teacher_name'] ?: 'Unassigned'); ?></td>
                                            <td><span class="badge"><?php echo esc($unit['status']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No enrolled units found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
