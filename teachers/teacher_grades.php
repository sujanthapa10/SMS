<?php
require __DIR__ . '/../includes/auth.php';
require_login('teacher');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function grade_from_percent(float $percent): string
{
    if ($percent >= 85) return 'A';
    if ($percent >= 75) return 'B';
    if ($percent >= 65) return 'C';
    if ($percent >= 50) return 'D';
    return 'F';
}

$teacherId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $assessmentId = (int) ($_POST['assessment_id'] ?? 0);
    $classId = (int) ($_POST['class_id'] ?? 0);
    $marks = (float) ($_POST['marks'] ?? 0);

    $stmt = $conn->prepare(
        'SELECT a.total_marks, a.course_id
         FROM assessments a
         JOIN enrollments e ON e.course_id = a.course_id
         JOIN classes cl ON cl.course_id = a.course_id
         WHERE e.student_id = ? AND a.id = ? AND cl.id = ? AND cl.teacher_id = ?
         LIMIT 1'
    );
    $stmt->bind_param('iiii', $studentId, $assessmentId, $classId, $teacherId);
    $stmt->execute();
    $assessment = $stmt->get_result()->fetch_assoc();

    if (!$studentId || !$assessmentId || !$classId || !$assessment) {
        $error = 'Choose your class, assessment, and enrolled student.';
    } else {
        $totalMarks = (float) $assessment['total_marks'];
        $grade = grade_from_percent($totalMarks > 0 ? ($marks / $totalMarks) * 100 : 0);
        $stmt = $conn->prepare('INSERT INTO grades (student_id, assessment_id, marks, grade) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks = VALUES(marks), grade = VALUES(grade)');
        $stmt->bind_param('iids', $studentId, $assessmentId, $marks, $grade);
        $message = $stmt->execute() ? 'Grade saved successfully.' : 'Could not save grade.';
    }
}

$selectedCourseId = (int) ($_GET['course_id'] ?? 0);
$selectedClassId = (int) ($_GET['class_id'] ?? 0);
$selectedAssessmentId = (int) ($_GET['assessment_id'] ?? 0);
$selectedStudentId = (int) ($_GET['student_id'] ?? 0);

$courses = $conn->prepare(
    'SELECT DISTINCT c.id, c.course_code, c.course_name
     FROM classes cl
     JOIN courses c ON c.id = cl.course_id
     WHERE cl.teacher_id = ?
     ORDER BY c.course_code'
);
$courses->bind_param('i', $teacherId);
$courses->execute();
$courseRows = $courses->get_result();

$classes = null;
if ($selectedCourseId) {
    $stmt = $conn->prepare(
        "SELECT cl.id, cl.class_name, s.semester_name, s.academic_year
         FROM classes cl
         JOIN semesters s ON s.id = cl.semester_id
         WHERE cl.teacher_id = ? AND cl.course_id = ?
         ORDER BY s.academic_year DESC, s.semester_name, cl.class_name"
    );
    $stmt->bind_param('ii', $teacherId, $selectedCourseId);
    $stmt->execute();
    $classes = $stmt->get_result();
}

$assessments = null;
if ($selectedCourseId) {
    $stmt = $conn->prepare('SELECT id, assessment_name, total_marks FROM assessments WHERE course_id = ? ORDER BY assessment_name');
    $stmt->bind_param('i', $selectedCourseId);
    $stmt->execute();
    $assessments = $stmt->get_result();
}

$students = null;
if ($selectedClassId) {
    $stmt = $conn->prepare(
        "SELECT st.id, st.student_number, CONCAT(st.first_name, ' ', st.last_name) AS student_name
         FROM classes cl
         JOIN enrollments e ON e.course_id = cl.course_id
         JOIN students st ON st.id = e.student_id
         WHERE cl.id = ? AND cl.teacher_id = ?
         ORDER BY st.first_name, st.last_name"
    );
    $stmt->bind_param('ii', $selectedClassId, $teacherId);
    $stmt->execute();
    $students = $stmt->get_result();
}

$grades = $conn->prepare(
    "SELECT g.*, a.assessment_name, a.total_marks, c.course_code, cl.class_name,
            CONCAT(st.first_name, ' ', st.last_name) AS student_name
     FROM grades g
     JOIN students st ON st.id = g.student_id
     JOIN assessments a ON a.id = g.assessment_id
     JOIN courses c ON c.id = a.course_id
     JOIN classes cl ON cl.course_id = c.id
     WHERE cl.teacher_id = ?
     ORDER BY cl.class_name, st.first_name, a.assessment_name"
);
$grades->bind_param('i', $teacherId);
$grades->execute();
$gradeRows = $grades->get_result();

$pageTitle = 'Teacher Grades | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Teacher';
$activeRole = 'teacher_grades';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2>Set grades</h2><span class="badge">My classes only</span></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="get" class="form-grid" style="margin-bottom:14px">
                        <div class="form-field"><label>Unit</label><select name="course_id" onchange="this.form.submit()"><option value="">Select unit</option><?php while ($course = $courseRows->fetch_assoc()): ?><option value="<?php echo esc($course['id']); ?>" <?php echo $selectedCourseId === (int) $course['id'] ? 'selected' : ''; ?>><?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field"><label>Class</label><select name="class_id" onchange="this.form.submit()" <?php echo $selectedCourseId ? '' : 'disabled'; ?>><option value="">Select class</option><?php if ($classes): ?><?php while ($class = $classes->fetch_assoc()): ?><option value="<?php echo esc($class['id']); ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>><?php echo esc($class['class_name'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year']); ?></option><?php endwhile; ?><?php endif; ?></select></div>
                        <div class="form-field"><label>Assessment</label><select name="assessment_id" onchange="this.form.submit()" <?php echo $selectedCourseId ? '' : 'disabled'; ?>><option value="">Select assessment</option><?php if ($assessments): ?><?php while ($assessment = $assessments->fetch_assoc()): ?><option value="<?php echo esc($assessment['id']); ?>" <?php echo $selectedAssessmentId === (int) $assessment['id'] ? 'selected' : ''; ?>><?php echo esc($assessment['assessment_name'] . ' / ' . $assessment['total_marks']); ?></option><?php endwhile; ?><?php endif; ?></select></div>
                        <div class="form-actions"><button class="button" type="submit">Apply filter</button></div>
                    </form>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="class_id" value="<?php echo esc($selectedClassId); ?>">
                        <input type="hidden" name="assessment_id" value="<?php echo esc($selectedAssessmentId); ?>">
                        <div class="form-field form-field-full"><label>Student</label><select name="student_id" required <?php echo ($selectedClassId && $selectedAssessmentId) ? '' : 'disabled'; ?>><option value="">Select student</option><?php if ($students): ?><?php while ($student = $students->fetch_assoc()): ?><option value="<?php echo esc($student['id']); ?>" <?php echo $selectedStudentId === (int) $student['id'] ? 'selected' : ''; ?>><?php echo esc($student['student_number'] . ' - ' . $student['student_name']); ?></option><?php endwhile; ?><?php endif; ?></select></div>
                        <div class="form-field"><label>Marks</label><input type="number" step="0.01" name="marks" required></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save grade</button></div>
                    </form>
                </section>

                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>My class grades</h2><span class="badge">Results</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Class</th><th>Student</th><th>Unit</th><th>Assessment</th><th>Marks</th></tr></thead><tbody>
                        <?php if ($gradeRows && $gradeRows->num_rows): ?>
                            <?php while ($grade = $gradeRows->fetch_assoc()): ?><tr><td><?php echo esc($grade['class_name']); ?></td><td><?php echo esc($grade['student_name']); ?></td><td><?php echo esc($grade['course_code']); ?></td><td><?php echo esc($grade['assessment_name']); ?></td><td><?php echo esc($grade['marks']); ?>/<?php echo esc($grade['total_marks']); ?> <span class="badge"><?php echo esc($grade['grade']); ?></span></td></tr><?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No grades entered yet.</td></tr>
                        <?php endif; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
