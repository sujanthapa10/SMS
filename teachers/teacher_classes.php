<?php
require __DIR__ . '/../includes/auth.php';
require_login('teacher');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$teacherId = (int) ($_SESSION['user_id'] ?? 0);
$selectedClassId = (int) ($_GET['class_id'] ?? 0);

$classes = $conn->prepare(
    "SELECT cl.*, c.course_code, c.course_name, c.description, s.semester_name, s.academic_year
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
if ($selectedClassId) {
    $stmt = $conn->prepare(
        "SELECT cl.*, c.course_code, c.course_name, c.description, s.semester_name, s.academic_year
         FROM classes cl
         JOIN courses c ON c.id = cl.course_id
         JOIN semesters s ON s.id = cl.semester_id
         WHERE cl.id = ? AND cl.teacher_id = ?"
    );
    $stmt->bind_param('ii', $selectedClassId, $teacherId);
    $stmt->execute();
    $classInfo = $stmt->get_result()->fetch_assoc();

    if ($classInfo) {
        $stmt = $conn->prepare(
            "SELECT st.student_number, CONCAT(st.first_name, ' ', st.last_name) AS student_name, st.email, e.status
             FROM enrollments e
             JOIN students st ON st.id = e.student_id
             WHERE e.course_id = ?
             ORDER BY st.first_name, st.last_name"
        );
        $stmt->bind_param('i', $classInfo['course_id']);
        $stmt->execute();
        $students = $stmt->get_result();
    }
}

$pageTitle = 'My Classes | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Teacher';
$activeRole = 'teacher_classes';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2>My classes</h2><span class="badge">Teacher only</span></div>
                    <form method="get" class="form-grid">
                        <div class="form-field form-field-full">
                            <label>Select class</label>
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Choose class</option>
                                <?php while ($class = $classRows->fetch_assoc()): ?>
                                    <option value="<?php echo esc($class['id']); ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc($class['class_name'] . ' - ' . $class['course_code'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </section>

                <?php if ($classInfo): ?>
                    <section class="hero" style="margin-top:20px">
                        <div class="hero-card">
                            <p class="eyebrow">Class unit</p>
                            <h1><?php echo esc($classInfo['class_name']); ?></h1>
                            <p><?php echo esc($classInfo['course_code'] . ' - ' . $classInfo['course_name']); ?>, <?php echo esc($classInfo['semester_name'] . ' ' . $classInfo['academic_year']); ?>.</p>
                        </div>
                        <aside class="panel profile-card">
                            <div class="profile-row"><span class="profile-label">Room</span><span class="profile-value"><?php echo esc($classInfo['room']); ?></span></div>
                            <div class="profile-row"><span class="profile-label">Capacity</span><span class="profile-value"><?php echo esc($classInfo['capacity']); ?></span></div>
                            <div class="profile-row"><span class="profile-label">Students</span><span class="profile-value"><?php echo esc($students ? $students->num_rows : 0); ?></span></div>
                        </aside>
                    </section>

                    <section class="panel">
                        <div class="panel-title"><h2>Students in class</h2><span class="badge badge-green">Enrolled</span></div>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php if ($students && $students->num_rows): ?>
                                        <?php while ($student = $students->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo esc($student['student_number']); ?></td>
                                                <td><?php echo esc($student['student_name']); ?></td>
                                                <td><?php echo esc($student['email']); ?></td>
                                                <td><span class="badge"><?php echo esc($student['status']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4">No students enrolled.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
