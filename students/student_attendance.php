<?php
require __DIR__ . '/../includes/auth.php';
require_login('student');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$studentId = (int) ($_SESSION['user_id'] ?? 0);
$selectedClassId = (int) ($_GET['class_id'] ?? 0);

$classes = $conn->prepare(
    "SELECT cl.id, cl.class_name, c.course_code, c.course_name, sem.semester_name, sem.academic_year,
            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
     FROM enrollments e
     JOIN classes cl ON cl.course_id = e.course_id
     JOIN courses c ON c.id = cl.course_id
     JOIN semesters sem ON sem.id = cl.semester_id
     JOIN teachers t ON t.id = cl.teacher_id
     WHERE e.student_id = ?
     ORDER BY sem.academic_year DESC, sem.semester_name, cl.class_name"
);
$classes->bind_param('i', $studentId);
$classes->execute();
$classRows = $classes->get_result();

$classInfo = null;
$saved = [];

if ($selectedClassId) {
    $stmt = $conn->prepare(
        "SELECT cl.*, c.course_code, c.course_name, sem.semester_name, sem.academic_year,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
         FROM enrollments e
         JOIN classes cl ON cl.course_id = e.course_id
         JOIN courses c ON c.id = cl.course_id
         JOIN semesters sem ON sem.id = cl.semester_id
         JOIN teachers t ON t.id = cl.teacher_id
         WHERE e.student_id = ? AND cl.id = ?"
    );
    $stmt->bind_param('ii', $studentId, $selectedClassId);
    $stmt->execute();
    $classInfo = $stmt->get_result()->fetch_assoc();

    if ($classInfo) {
        $stmt = $conn->prepare('SELECT week_number, status, marked_at FROM attendance WHERE student_id = ? AND class_id = ?');
        $stmt->bind_param('ii', $studentId, $selectedClassId);
        $stmt->execute();
        $rows = $stmt->get_result();
        while ($row = $rows->fetch_assoc()) {
            $saved[(int) $row['week_number']] = $row;
        }
    }
}

$pageTitle = 'My Attendance | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Student';
$activeRole = 'student_attendance';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2>My attendance</h2><span class="badge">Week 1-12</span></div>
                    <form method="get" class="form-grid">
                        <div class="form-field form-field-full">
                            <label>Select enrolled class</label>
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Choose class</option>
                                <?php while ($class = $classRows->fetch_assoc()): ?>
                                    <option value="<?php echo esc($class['id']); ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc($class['class_name'] . ' - ' . $class['course_code'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year'] . ' - ' . $class['teacher_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </section>

                <?php if ($classInfo): ?>
                    <section class="panel attendance-table" style="margin-top:20px">
                        <div class="panel-title"><h2><?php echo esc($classInfo['class_name']); ?> attendance</h2><span class="badge"><?php echo esc($classInfo['course_code']); ?></span></div>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>Student</th><?php for ($week = 1; $week <= 12; $week++): ?><th>W<?php echo $week; ?></th><?php endfor; ?></tr></thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo esc($_SESSION['name'] ?? 'Student'); ?></td>
                                        <?php for ($week = 1; $week <= 12; $week++): ?>
                                            <?php $status = $saved[$week]['status'] ?? ''; ?>
                                            <td><span class="attendance-select" data-status="<?php echo esc($status); ?>"><?php echo esc($status ? strtoupper(substr($status, 0, 1)) : '-'); ?></span></td>
                                        <?php endfor; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
