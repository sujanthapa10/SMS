<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$message = '';
$classId = (int) ($_GET['class_id'] ?? $_POST['class_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $classId) {
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

    $message = 'Attendance sheet saved successfully.';
}

$classes = $conn->query(
    "SELECT cl.id, cl.class_name, c.course_code, c.course_name, s.semester_name, s.academic_year,
            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
     FROM classes cl
     JOIN courses c ON c.id = cl.course_id
     JOIN semesters s ON s.id = cl.semester_id
     JOIN teachers t ON t.id = cl.teacher_id
     ORDER BY s.academic_year DESC, s.semester_name, cl.class_name"
);

$classInfo = null;
$students = null;
$saved = [];

if ($classId) {
    $stmt = $conn->prepare(
        "SELECT cl.*, c.course_code, c.course_name, s.semester_name, s.academic_year,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
         FROM classes cl
         JOIN courses c ON c.id = cl.course_id
         JOIN semesters s ON s.id = cl.semester_id
         JOIN teachers t ON t.id = cl.teacher_id
         WHERE cl.id = ?"
    );
    $stmt->bind_param('i', $classId);
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

$pageTitle = 'Attendance | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'attendance';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2>Class attendance</h2><span class="badge">Week 1-12</span></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <form method="get" class="form-grid">
                        <div class="form-field form-field-full">
                            <label>Select class</label>
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Choose class</option>
                                <?php while ($class = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo esc($class['id']); ?>" <?php echo $classId === (int) $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc($class['class_name'] . ' - ' . $class['course_code'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year'] . ' - ' . $class['teacher_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </section>

                <?php if ($classInfo): ?>
                    <section class="hero" style="margin-top:20px">
                        <div class="hero-card">
                            <p class="eyebrow">2026 class</p>
                            <h1><?php echo esc($classInfo['class_name']); ?></h1>
                            <p><?php echo esc($classInfo['course_code'] . ' - ' . $classInfo['course_name']); ?>, <?php echo esc($classInfo['semester_name'] . ' ' . $classInfo['academic_year']); ?>. Teacher: <?php echo esc($classInfo['teacher_name']); ?>.</p>
                        </div>
                        <aside class="panel profile-card">
                            <div class="profile-row"><span class="profile-label">Room</span><span class="profile-value"><?php echo esc($classInfo['room']); ?></span></div>
                            <div class="profile-row"><span class="profile-label">Capacity</span><span class="profile-value"><?php echo esc($classInfo['capacity']); ?></span></div>
                            <div class="profile-row"><span class="profile-label">Weeks</span><span class="profile-value">1 to 12</span></div>
                        </aside>
                    </section>

                    <section class="panel attendance-table">
                        <div class="panel-title"><h2>Attendance</h2><button class="button button-primary" form="attendance-form" type="submit">Save attendance</button></div>
                        <form id="attendance-form" method="post">
                            <input type="hidden" name="class_id" value="<?php echo esc($classId); ?>">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Student</th><?php for ($week = 1; $week <= 12; $week++): ?><th>W<?php echo $week; ?></th><?php endfor; ?></tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($students && $students->num_rows): ?>
                                            <?php while ($student = $students->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo esc($student['student_number'] . ' - ' . $student['student_name']); ?></td>
                                                    <?php for ($week = 1; $week <= 12; $week++): ?>
                                                        <?php $value = $saved[(int) $student['id']][$week] ?? ''; ?>
                                                        <td>
                                                            <select class="attendance-select" data-status="<?php echo esc($value); ?>" onchange="this.dataset.status = this.value" name="attendance[<?php echo esc($student['id']); ?>][<?php echo $week; ?>]">
                                                                <option value="" <?php echo $value === '' ? 'selected' : ''; ?>>-</option>
                                                                <?php foreach (['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'] as $status => $label): ?>
                                                                    <option value="<?php echo esc($status); ?>" <?php echo $value === $status ? 'selected' : ''; ?>><?php echo esc($label); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                    <?php endfor; ?>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="13">No students enrolled in this unit yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </section>
                <?php endif; ?>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
