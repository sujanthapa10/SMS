<?php
require __DIR__ . '/../includes/auth.php';
require_login('teacher');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$teacherId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';
$classId = (int) ($_GET['class_id'] ?? $_POST['class_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $classId) {
    $stmt = $conn->prepare('SELECT id FROM classes WHERE id = ? AND teacher_id = ? LIMIT 1');
    $stmt->bind_param('ii', $classId, $teacherId);
    $stmt->execute();
    $allowed = (bool) $stmt->get_result()->fetch_assoc();

    if ($allowed) {
        $attendance = $_POST['attendance'] ?? [];
        $stmt = $conn->prepare(
            'INSERT INTO attendance (class_id, student_id, week_number, status, marked_at)
             VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = CURRENT_TIMESTAMP'
        );

        foreach ($attendance as $studentId => $weeks) {
            foreach ($weeks as $week => $status) {
                $studentId = (int) $studentId;
                $week = (int) $week;
                $status = $status === '' ? null : $status;
                if ($studentId && $week >= 1 && $week <= 12) {
                    $stmt->bind_param('iiis', $classId, $studentId, $week, $status);
                    $stmt->execute();
                }
            }
        }
        $message = 'Attendance saved successfully.';
    }
}

$classes = $conn->prepare(
    "SELECT cl.id, cl.class_name, c.course_code, c.course_name, s.semester_name, s.academic_year
     FROM classes cl
     JOIN courses c ON c.id = cl.course_id
     JOIN semesters s ON s.id = cl.semester_id
     WHERE cl.teacher_id = ?
     ORDER BY s.academic_year DESC, s.semester_name, cl.class_name"
);
$classes->bind_param('i', $teacherId);
$classes->execute();
$classRows = $classes->get_result();

$classInfo = null;
$students = null;
$saved = [];

if ($classId) {
    $stmt = $conn->prepare(
        "SELECT cl.*, c.course_code, c.course_name, s.semester_name, s.academic_year
         FROM classes cl
         JOIN courses c ON c.id = cl.course_id
         JOIN semesters s ON s.id = cl.semester_id
         WHERE cl.id = ? AND cl.teacher_id = ?"
    );
    $stmt->bind_param('ii', $classId, $teacherId);
    $stmt->execute();
    $classInfo = $stmt->get_result()->fetch_assoc();

    if ($classInfo) {
        $stmt = $conn->prepare(
            "SELECT st.id, st.student_number, CONCAT(st.first_name, ' ', st.last_name) AS student_name
             FROM enrollments e
             JOIN students st ON st.id = e.student_id
             WHERE e.course_id = ?
             ORDER BY st.first_name, st.last_name"
        );
        $stmt->bind_param('i', $classInfo['course_id']);
        $stmt->execute();
        $students = $stmt->get_result();

        $stmt = $conn->prepare('SELECT student_id, week_number, status FROM attendance WHERE class_id = ?');
        $stmt->bind_param('i', $classId);
        $stmt->execute();
        $rows = $stmt->get_result();
        while ($row = $rows->fetch_assoc()) {
            $saved[(int) $row['student_id']][(int) $row['week_number']] = $row['status'];
        }
    }
}

$pageTitle = 'Teacher Attendance | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Teacher';
$activeRole = 'teacher_attendance';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2>Attendance</h2><span class="badge">Week 1-12</span></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <form method="get" class="form-grid">
                        <div class="form-field form-field-full">
                            <label>Select my class</label>
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Choose class</option>
                                <?php while ($class = $classRows->fetch_assoc()): ?>
                                    <option value="<?php echo esc($class['id']); ?>" <?php echo $classId === (int) $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc($class['class_name'] . ' - ' . $class['course_code'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </section>

                <?php if ($classInfo): ?>
                    <section class="panel attendance-table" style="margin-top:20px">
                        <div class="panel-title"><h2><?php echo esc($classInfo['class_name']); ?> attendance</h2><button class="button button-primary" form="attendance-form" type="submit">Save attendance</button></div>
                        <form id="attendance-form" method="post">
                            <input type="hidden" name="class_id" value="<?php echo esc($classId); ?>">
                            <div class="table-wrap">
                                <table>
                                    <thead><tr><th>Student</th><?php for ($week = 1; $week <= 12; $week++): ?><th>W<?php echo $week; ?></th><?php endfor; ?></tr></thead>
                                    <tbody>
                                        <?php if ($students && $students->num_rows): ?>
                                            <?php while ($student = $students->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo esc($student['student_number'] . ' - ' . $student['student_name']); ?></td>
                                                    <?php for ($week = 1; $week <= 12; $week++): ?>
                                                        <?php $value = $saved[(int) $student['id']][$week] ?? ''; ?>
                                                        <td><select class="attendance-select" data-status="<?php echo esc($value); ?>" onchange="this.dataset.status = this.value" name="attendance[<?php echo esc($student['id']); ?>][<?php echo $week; ?>]"><option value="" <?php echo $value === '' ? 'selected' : ''; ?>>-</option><?php foreach (['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'] as $status => $label): ?><option value="<?php echo esc($status); ?>" <?php echo $value === $status ? 'selected' : ''; ?>><?php echo esc($label); ?></option><?php endforeach; ?></select></td>
                                                    <?php endfor; ?>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="13">No students enrolled.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </section>
                <?php endif; ?>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
