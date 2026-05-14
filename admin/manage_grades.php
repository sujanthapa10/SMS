<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');
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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_assessment') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM assessments WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Assessment deleted successfully.' : 'Could not delete assessment.';
    }

    if ($action === 'save_assessment') {
        $id = (int) ($_POST['id'] ?? 0);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $name = trim($_POST['assessment_name'] ?? '');
        $totalMarks = (float) ($_POST['total_marks'] ?? 100);
        $weight = $_POST['weight_percent'] === '' ? null : (float) $_POST['weight_percent'];

        if (!$courseId || $name === '' || $totalMarks <= 0) {
            $error = 'Unit, assessment name, and total marks are required.';
        } elseif ($id > 0) {
            $stmt = $conn->prepare('UPDATE assessments SET course_id = ?, assessment_name = ?, total_marks = ?, weight_percent = ? WHERE id = ?');
            $stmt->bind_param('isddi', $courseId, $name, $totalMarks, $weight, $id);
            $message = $stmt->execute() ? 'Assessment updated successfully.' : 'Could not update assessment. Check duplicates.';
        } else {
            $stmt = $conn->prepare('INSERT INTO assessments (course_id, assessment_name, total_marks, weight_percent) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isdd', $courseId, $name, $totalMarks, $weight);
            $message = $stmt->execute() ? 'Assessment created successfully.' : 'Could not create assessment. Check duplicates.';
        }
    }

    if ($action === 'delete_grade') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM grades WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Grade deleted successfully.' : 'Could not delete grade.';
    }

    if ($action === 'save_grade') {
        $id = (int) ($_POST['id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $assessmentId = (int) ($_POST['assessment_id'] ?? 0);
        $classId = (int) ($_POST['class_id'] ?? 0);
        $marks = (float) ($_POST['marks'] ?? 0);

        $stmt = $conn->prepare(
            'SELECT a.total_marks, a.course_id
             FROM assessments a
             JOIN enrollments e ON e.course_id = a.course_id
             WHERE e.student_id = ? AND a.id = ?
             LIMIT 1'
        );
        $stmt->bind_param('ii', $studentId, $assessmentId);
        $stmt->execute();
        $assessment = $stmt->get_result()->fetch_assoc();

        $classMatches = true;
        if ($classId && $assessment) {
            $stmt = $conn->prepare('SELECT id FROM classes WHERE id = ? AND course_id = ? LIMIT 1');
            $stmt->bind_param('ii', $classId, $assessment['course_id']);
            $stmt->execute();
            $classMatches = (bool) $stmt->get_result()->fetch_assoc();
        }

        if (!$studentId || !$assessmentId || !$classId || !$assessment || !$classMatches) {
            $error = 'Choose a valid class, enrolled student, and unit assessment.';
        } else {
            $totalMarks = (float) $assessment['total_marks'];
            $grade = grade_from_percent($totalMarks > 0 ? ($marks / $totalMarks) * 100 : 0);

            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE grades SET student_id = ?, assessment_id = ?, marks = ?, grade = ? WHERE id = ?');
                $stmt->bind_param('iidsi', $studentId, $assessmentId, $marks, $grade, $id);
                $message = $stmt->execute() ? 'Grade updated successfully.' : 'Could not update grade.';
            } else {
                $stmt = $conn->prepare('INSERT INTO grades (student_id, assessment_id, marks, grade) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks = VALUES(marks), grade = VALUES(grade)');
                $stmt->bind_param('iids', $studentId, $assessmentId, $marks, $grade);
                $message = $stmt->execute() ? 'Grade saved successfully.' : 'Could not save grade.';
            }
        }
    }
}

$editAssessment = null;
if (isset($_GET['edit_assessment'])) {
    $id = (int) $_GET['edit_assessment'];
    $stmt = $conn->prepare('SELECT * FROM assessments WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editAssessment = $stmt->get_result()->fetch_assoc();
}

$editGrade = null;
if (isset($_GET['edit_grade'])) {
    $id = (int) $_GET['edit_grade'];
    $stmt = $conn->prepare('SELECT g.*, a.course_id FROM grades g JOIN assessments a ON a.id = g.assessment_id WHERE g.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editGrade = $stmt->get_result()->fetch_assoc();
}

$selectedCourseId = (int) ($_GET['course_id'] ?? ($editGrade['course_id'] ?? 0));
$selectedClassId = (int) ($_GET['class_id'] ?? 0);
$selectedAssessmentId = (int) ($_GET['assessment_id'] ?? ($editGrade['assessment_id'] ?? 0));
$selectedStudentId = (int) ($_GET['student_id'] ?? ($editGrade['student_id'] ?? 0));

$assessmentCourses = $conn->query('SELECT id, course_code, course_name FROM courses ORDER BY course_code');
$filterCourses = $conn->query('SELECT id, course_code, course_name FROM courses ORDER BY course_code');

$classes = null;
if ($selectedCourseId) {
    $stmt = $conn->prepare(
        "SELECT cl.id, cl.class_name, s.semester_name, s.academic_year, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
         FROM classes cl
         JOIN semesters s ON s.id = cl.semester_id
         JOIN teachers t ON t.id = cl.teacher_id
         WHERE cl.course_id = ?
         ORDER BY s.academic_year DESC, s.semester_name, cl.class_name"
    );
    $stmt->bind_param('i', $selectedCourseId);
    $stmt->execute();
    $classes = $stmt->get_result();
}

$filteredAssessments = null;
if ($selectedCourseId) {
    $stmt = $conn->prepare('SELECT id, assessment_name, total_marks FROM assessments WHERE course_id = ? ORDER BY assessment_name');
    $stmt->bind_param('i', $selectedCourseId);
    $stmt->execute();
    $filteredAssessments = $stmt->get_result();
}

$studentCourseId = 0;
if ($selectedClassId) {
    $stmt = $conn->prepare('SELECT course_id FROM classes WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $selectedClassId);
    $stmt->execute();
    $classCourse = $stmt->get_result()->fetch_assoc();
    $studentCourseId = (int) ($classCourse['course_id'] ?? $selectedCourseId);
}

$filteredStudents = null;
if ($selectedClassId && $studentCourseId) {
    $stmt = $conn->prepare(
        "SELECT st.id, st.student_number, CONCAT(st.first_name, ' ', st.last_name) AS student_name
         FROM enrollments e
         JOIN students st ON st.id = e.student_id
         WHERE e.course_id = ? AND e.status = 'active'
         ORDER BY st.first_name, st.last_name"
    );
    $stmt->bind_param('i', $studentCourseId);
    $stmt->execute();
    $filteredStudents = $stmt->get_result();
}

$assessments = $conn->query(
    'SELECT a.*, c.course_code, c.course_name
     FROM assessments a
     JOIN courses c ON c.id = a.course_id
     ORDER BY c.course_code, a.assessment_name'
);
$grades = $conn->query(
    "SELECT g.*, a.assessment_name, a.total_marks, c.course_code,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name
     FROM grades g
     JOIN students s ON s.id = g.student_id
     JOIN assessments a ON a.id = g.assessment_id
     JOIN courses c ON c.id = a.course_id
     ORDER BY c.course_code, s.first_name, a.assessment_name"
);

$pageTitle = 'Manage Grades | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'grades';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $editAssessment ? 'Edit unit assessment' : 'Create unit assessment'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_grades.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save_assessment">
                        <input type="hidden" name="id" value="<?php echo esc($editAssessment['id'] ?? 0); ?>">
                        <div class="form-field"><label>Unit</label><select name="course_id" required><option value="">Select unit</option><?php while ($course = $assessmentCourses->fetch_assoc()): ?><option value="<?php echo esc($course['id']); ?>" <?php echo (string) ($editAssessment['course_id'] ?? '') === (string) $course['id'] ? 'selected' : ''; ?>><?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field"><label>Assessment</label><input name="assessment_name" value="<?php echo esc($editAssessment['assessment_name'] ?? 'Assignment 1'); ?>" required></div>
                        <div class="form-field"><label>Total marks</label><input type="number" step="0.01" name="total_marks" value="<?php echo esc($editAssessment['total_marks'] ?? '100'); ?>" required></div>
                        <div class="form-field"><label>Weight %</label><input type="number" step="0.01" name="weight_percent" value="<?php echo esc($editAssessment['weight_percent'] ?? ''); ?>"></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save assessment</button></div>
                    </form>
                </section>

                <section class="content-grid" style="margin-top:20px">
                    <div class="panel">
                        <div class="panel-title"><h2>Set grade</h2><span class="badge">Filtered</span></div>
                        <form method="get" class="form-grid" style="margin-bottom:14px">
                            <div class="form-field">
                                <label>Unit</label>
                                <select name="course_id" onchange="this.form.submit()">
                                    <option value="">Select unit</option>
                                    <?php while ($course = $filterCourses->fetch_assoc()): ?>
                                        <option value="<?php echo esc($course['id']); ?>" <?php echo $selectedCourseId === (int) $course['id'] ? 'selected' : ''; ?>>
                                            <?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Class</label>
                                <select name="class_id" onchange="this.form.submit()" <?php echo $selectedCourseId ? '' : 'disabled'; ?>>
                                    <option value="">Select class</option>
                                    <?php if ($classes): ?>
                                        <?php while ($class = $classes->fetch_assoc()): ?>
                                            <option value="<?php echo esc($class['id']); ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo esc($class['class_name'] . ' - ' . $class['semester_name'] . ' ' . $class['academic_year'] . ' - ' . $class['teacher_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Assessment</label>
                                <select name="assessment_id" onchange="this.form.submit()" <?php echo $selectedCourseId ? '' : 'disabled'; ?>>
                                    <option value="">Select assessment</option>
                                    <?php if ($filteredAssessments): ?>
                                        <?php while ($assessment = $filteredAssessments->fetch_assoc()): ?>
                                            <option value="<?php echo esc($assessment['id']); ?>" <?php echo $selectedAssessmentId === (int) $assessment['id'] ? 'selected' : ''; ?>>
                                                <?php echo esc($assessment['assessment_name'] . ' / ' . $assessment['total_marks']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-actions"><button class="button" type="submit">Apply filter</button></div>
                        </form>
                        <form method="post" class="form-grid">
                            <input type="hidden" name="action" value="save_grade">
                            <input type="hidden" name="id" value="<?php echo esc($editGrade['id'] ?? 0); ?>">
                            <input type="hidden" name="class_id" value="<?php echo esc($selectedClassId); ?>">
                            <input type="hidden" name="assessment_id" value="<?php echo esc($selectedAssessmentId); ?>">
                            <div class="form-field form-field-full">
                                <label>Student enrolled in selected class unit</label>
                                <select name="student_id" required <?php echo ($selectedClassId && $selectedAssessmentId) ? '' : 'disabled'; ?>>
                                    <option value="">Select student</option>
                                    <?php if ($filteredStudents): ?>
                                        <?php while ($student = $filteredStudents->fetch_assoc()): ?>
                                            <option value="<?php echo esc($student['id']); ?>" <?php echo $selectedStudentId === (int) $student['id'] ? 'selected' : ''; ?>>
                                                <?php echo esc($student['student_number'] . ' - ' . $student['student_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-field"><label>Marks</label><input type="number" step="0.01" name="marks" value="<?php echo esc($editGrade['marks'] ?? ''); ?>" required></div>
                            <div class="form-actions"><button class="button button-primary" type="submit">Save grade</button></div>
                        </form>
                    </div>

                    <div class="panel">
                        <div class="panel-title"><h2>Assessments</h2><span class="badge">Per unit</span></div>
                        <div class="table-wrap"><table><thead><tr><th>Unit</th><th>Assessment</th><th>Total</th><th>Actions</th></tr></thead><tbody>
                            <?php while ($assessment = $assessments->fetch_assoc()): ?><tr>
                                <td><?php echo esc($assessment['course_code']); ?></td><td><?php echo esc($assessment['assessment_name']); ?></td><td><?php echo esc($assessment['total_marks']); ?></td>
                                <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/manage_grades.php?edit_assessment=' . $assessment['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this assessment and its grades?');"><input type="hidden" name="action" value="delete_assessment"><input type="hidden" name="id" value="<?php echo esc($assessment['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                            </tr><?php endwhile; ?>
                        </tbody></table></div>
                    </div>
                </section>

                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Student grades</h2><span class="badge">Results</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Student</th><th>Unit</th><th>Assessment</th><th>Marks</th><th>Actions</th></tr></thead><tbody>
                        <?php while ($grade = $grades->fetch_assoc()): ?><tr>
                            <td><?php echo esc($grade['student_name']); ?></td><td><?php echo esc($grade['course_code']); ?></td><td><?php echo esc($grade['assessment_name']); ?></td><td><?php echo esc($grade['marks']); ?>/<?php echo esc($grade['total_marks']); ?> <span class="badge"><?php echo esc($grade['grade']); ?></span></td>
                            <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/manage_grades.php?edit_grade=' . $grade['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this grade?');"><input type="hidden" name="action" value="delete_grade"><input type="hidden" name="id" value="<?php echo esc($grade['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                        </tr><?php endwhile; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
