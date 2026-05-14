<?php
require __DIR__ . '/../includes/auth.php';
require_login('teacher');

require __DIR__ . '/../database/connection.php';

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$userId = $_SESSION['user_id'] ?? null;
$teacher = null;

if ($userId) {
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
}

$teacherId = (int) ($teacher['id'] ?? 0);
$teacherName = trim(($teacher['first_name'] ?? 'John') . ' ' . ($teacher['last_name'] ?? 'Teacher'));

$courseCount = 0;
$studentCount = 0;
$attendanceCount = 0;

if ($teacherId) {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM classes WHERE teacher_id = ?');
    $stmt->bind_param('i', $teacherId);
    $stmt->execute();
    $courseCount = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $stmt = $conn->prepare(
        'SELECT COUNT(DISTINCT e.student_id) AS total
     FROM enrollments e
     JOIN classes cl ON cl.course_id = e.course_id
     WHERE cl.teacher_id = ?'
    );
    $stmt->bind_param('i', $teacherId);
    $stmt->execute();
    $studentCount = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $stmt = $conn->prepare(
        'SELECT COUNT(*) AS total
         FROM attendance a
         JOIN classes cl ON cl.id = a.class_id
         WHERE cl.teacher_id = ?'
    );
    $stmt->bind_param('i', $teacherId);
    $stmt->execute();
    $attendanceCount = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}

$courses = $conn->prepare(
    'SELECT cl.id AS class_id, cl.class_name, cl.room, c.course_code, c.course_name, s.semester_name, s.academic_year
     FROM classes cl
     JOIN courses c ON c.id = cl.course_id
     JOIN semesters s ON s.id = cl.semester_id
     WHERE cl.teacher_id = ?
     ORDER BY s.academic_year DESC, s.semester_name, cl.class_name'
);
$courses->bind_param('i', $teacherId);
$courses->execute();
$courseRows = $courses->get_result();

$students = $conn->prepare(
    'SELECT s.student_number, CONCAT(s.first_name, " ", s.last_name) AS student_name,
            c.course_code, cl.class_name, e.status
     FROM enrollments e
     JOIN students s ON s.id = e.student_id
     JOIN classes cl ON cl.course_id = e.course_id
     JOIN courses c ON c.id = cl.course_id
     WHERE cl.teacher_id = ?
     ORDER BY cl.class_name, s.first_name, s.last_name
     LIMIT 8'
);
$students->bind_param('i', $teacherId);
$students->execute();
$studentRows = $students->get_result();
?>
<?php
$pageTitle = 'Teacher Dashboard | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Teacher';
$activeRole = 'teacher';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
            <section class="hero">
                <div class="hero-card">
                    <p class="eyebrow">Teacher dashboard</p>
                    <h1>Hello, <?php echo esc($teacherName); ?>.</h1>
                    <p>Review your assigned courses, active enrollments, and attendance activity from the WIN localhost database.</p>
                </div>
                <aside class="panel profile-card">
                    <div class="profile-row"><span class="profile-label">Employee</span><span class="profile-value"><?php echo esc($teacher['employee_number'] ?? 'EMP'); ?></span></div>
                    <div class="profile-row"><span class="profile-label">Department</span><span class="profile-value"><?php echo esc($teacher['department'] ?? 'General'); ?></span></div>
                    <div class="profile-row"><span class="profile-label">Email</span><span class="profile-value"><?php echo esc($teacher['email'] ?? 'teacher@sms.com'); ?></span></div>
                </aside>
            </section>

            <section class="stats-grid" aria-label="Teacher statistics">
                <div class="stat-card"><span>My classes</span><strong><?php echo esc($courseCount); ?></strong></div>
                <div class="stat-card"><span>My students</span><strong><?php echo esc($studentCount); ?></strong></div>
                <div class="stat-card"><span>Attendance</span><strong><?php echo esc($attendanceCount); ?></strong></div>
                <div class="stat-card"><span>Status</span><strong>Live</strong></div>
            </section>

            <section class="content-grid">
                <div class="panel">
                    <div class="panel-title"><h2>Assigned classes</h2><span class="badge">2026</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Class</th><th>Unit</th><th>Semester</th><th>Room</th></tr></thead>
                            <tbody>
                                <?php if ($courseRows && $courseRows->num_rows): ?>
                                    <?php while ($course = $courseRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><a class="button button-small" href="<?php echo esc(app_url('teachers/teacher_classes.php?class_id=' . $course['class_id'])); ?>"><?php echo esc($course['class_name']); ?></a></td>
                                            <td><?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?></td>
                                            <td><?php echo esc($course['semester_name'] . ' ' . $course['academic_year']); ?></td>
                                            <td><?php echo esc($course['room']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No assigned classes found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title"><h2>Students</h2><span class="badge badge-green">Enrolled</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($studentRows && $studentRows->num_rows): ?>
                                    <?php while ($student = $studentRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($student['student_number']); ?></td>
                                            <td><?php echo esc($student['student_name']); ?></td>
                                            <td><?php echo esc($student['class_name']); ?></td>
                                            <td><span class="badge"><?php echo esc($student['status']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No enrolled students found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
