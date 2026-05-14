<?php
require __DIR__ . '/../includes/auth.php';
require_login('student');

require __DIR__ . '/../database/connection.php';

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$userId = $_SESSION['user_id'] ?? null;
$student = null;

if ($userId) {
    $stmt = $conn->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

$studentId = (int) ($student['id'] ?? 0);
$studentName = trim(($student['first_name'] ?? 'Alice') . ' ' . ($student['last_name'] ?? 'Student'));

$courseCount = 0;
$attendanceCount = 0;
$gradeCount = 0;
$average = 0;

if ($studentId) {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM enrollments WHERE student_id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $courseCount = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM attendance WHERE student_id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $attendanceCount = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $stmt = $conn->prepare(
        'SELECT COUNT(*) AS total, AVG((g.marks / a.total_marks) * 100) AS avg_score
         FROM grades g
         JOIN assessments a ON a.id = g.assessment_id
         WHERE g.student_id = ?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $gradeStats = $stmt->get_result()->fetch_assoc();
    $gradeCount = (int) ($gradeStats['total'] ?? 0);
    $average = round((float) ($gradeStats['avg_score'] ?? 0));
}

$courses = $conn->prepare(
    'SELECT c.course_code, c.course_name, e.status, cl.class_name, cl.room, sem.semester_name, sem.academic_year,
            CONCAT(t.first_name, " ", t.last_name) AS teacher_name
     FROM enrollments e
     JOIN courses c ON c.id = e.course_id
     LEFT JOIN classes cl ON cl.course_id = c.id
     LEFT JOIN teachers t ON t.id = cl.teacher_id
     LEFT JOIN semesters sem ON sem.id = cl.semester_id
     WHERE e.student_id = ?
     ORDER BY c.course_name'
);
$courses->bind_param('i', $studentId);
$courses->execute();
$courseRows = $courses->get_result();

$grades = $conn->prepare(
    'SELECT a.assessment_name, g.marks, a.total_marks, g.grade, c.course_code
     FROM grades g
     JOIN assessments a ON a.id = g.assessment_id
     JOIN courses c ON c.id = a.course_id
     WHERE g.student_id = ?
     ORDER BY g.created_at DESC'
);
$grades->bind_param('i', $studentId);
$grades->execute();
$gradeRows = $grades->get_result();
?>
<?php
$pageTitle = 'Student Dashboard | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Student';
$activeRole = 'student';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
            <section class="hero">
                <div class="hero-card">
                    <p class="eyebrow">Student dashboard</p>
                    <h1>Welcome, <?php echo esc($studentName); ?>.</h1>
                    <p>Track your WIN courses, teachers, attendance, and latest grades from your localhost database records.</p>
                </div>
                <aside class="panel profile-card">
                    <div class="profile-row"><span class="profile-label">Student ID</span><span class="profile-value"><?php echo esc($student['student_number'] ?? 'STU'); ?></span></div>
                    <div class="profile-row"><span class="profile-label">Email</span><span class="profile-value"><?php echo esc($student['email'] ?? 'student@sms.com'); ?></span></div>
                    <div class="profile-row"><span class="profile-label">Location</span><span class="profile-value"><?php echo esc($student['address'] ?? 'Sydney, Australia'); ?></span></div>
                </aside>
            </section>

            <section class="stats-grid" aria-label="Student statistics">
                <div class="stat-card"><span>Courses</span><strong><?php echo esc($courseCount); ?></strong></div>
                <div class="stat-card"><span>Attendance</span><strong><?php echo esc($attendanceCount); ?></strong></div>
                <div class="stat-card"><span>Grades</span><strong><?php echo esc($gradeCount); ?></strong></div>
                <div class="stat-card"><span>Average</span><strong><?php echo esc($average); ?>%</strong></div>
            </section>

            <section class="content-grid">
                <div class="panel">
                    <div class="panel-title"><h2>My courses</h2><span class="badge">Active</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Code</th><th>Unit</th><th>Class</th><th>Teacher</th></tr></thead>
                            <tbody>
                                <?php if ($courseRows && $courseRows->num_rows): ?>
                                    <?php while ($course = $courseRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($course['course_code']); ?></td>
                                            <td><?php echo esc($course['course_name']); ?></td>
                                            <td><?php echo esc($course['class_name'] ?: 'TBA'); ?></td>
                                            <td><?php echo esc($course['teacher_name'] ?: 'Unassigned'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No enrolled courses found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title"><h2>Latest grades</h2><span class="badge badge-green">Results</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Assessment</th><th>Marks</th><th>Grade</th></tr></thead>
                            <tbody>
                                <?php if ($gradeRows && $gradeRows->num_rows): ?>
                                    <?php while ($grade = $gradeRows->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo esc($grade['assessment_name']); ?></td>
                                            <td><?php echo esc($grade['marks']); ?>/<?php echo esc($grade['total_marks']); ?></td>
                                            <td><span class="badge"><?php echo esc($grade['grade']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3">No grades found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
