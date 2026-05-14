<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM classes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Class deleted successfully.' : 'Could not delete class. Remove attendance sheet rows first.';
    }
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['class_name'] ?? '');
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        $semesterId = (int) ($_POST['semester_id'] ?? 0);
        $room = trim($_POST['room'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 30);

        if ($name === '' || !$courseId || !$teacherId || !$semesterId) {
            $error = 'Class name, unit, teacher, and semester are required.';
        } elseif ($id > 0) {
            $stmt = $conn->prepare('UPDATE classes SET class_name = ?, course_id = ?, teacher_id = ?, semester_id = ?, room = ?, capacity = ? WHERE id = ?');
            $stmt->bind_param('siiisii', $name, $courseId, $teacherId, $semesterId, $room, $capacity, $id);
            $message = $stmt->execute() ? 'Class updated successfully.' : 'Could not update class.';
        } else {
            $stmt = $conn->prepare('INSERT INTO classes (class_name, course_id, teacher_id, semester_id, room, capacity) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('siiisi', $name, $courseId, $teacherId, $semesterId, $room, $capacity);
            $message = $stmt->execute() ? 'Class created successfully.' : 'Could not create class.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM classes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$courses = $conn->query('SELECT id, course_code, course_name FROM courses ORDER BY course_code');
$teachers = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers ORDER BY first_name");
$semesters = $conn->query("SELECT id, semester_name, academic_year FROM semesters WHERE academic_year = 2026 ORDER BY semester_name");
$classes = $conn->query("SELECT cl.*, c.course_code, c.course_name, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name, s.semester_name, s.academic_year FROM classes cl JOIN courses c ON c.id = cl.course_id JOIN teachers t ON t.id = cl.teacher_id JOIN semesters s ON s.id = cl.semester_id ORDER BY s.academic_year DESC, s.semester_name, cl.class_name");

$pageTitle = 'Manage Classes | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'classes';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit class' : 'Create class'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_classes.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Class name</label><input name="class_name" value="<?php echo esc($edit['class_name'] ?? ''); ?>" placeholder="IT101-A" required></div>
                        <div class="form-field"><label>Capacity</label><input type="number" name="capacity" value="<?php echo esc($edit['capacity'] ?? '30'); ?>"></div>
                        <div class="form-field"><label>Unit</label><select name="course_id" required><option value="">Select unit</option><?php while ($course = $courses->fetch_assoc()): ?><option value="<?php echo esc($course['id']); ?>" <?php echo (string) ($edit['course_id'] ?? '') === (string) $course['id'] ? 'selected' : ''; ?>><?php echo esc($course['course_code'] . ' - ' . $course['course_name']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field"><label>Teacher</label><select name="teacher_id" required><option value="">Select teacher</option><?php while ($teacher = $teachers->fetch_assoc()): ?><option value="<?php echo esc($teacher['id']); ?>" <?php echo (string) ($edit['teacher_id'] ?? '') === (string) $teacher['id'] ? 'selected' : ''; ?>><?php echo esc($teacher['teacher_name']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field"><label>2026 semester</label><select name="semester_id" required><option value="">Select semester</option><?php while ($semester = $semesters->fetch_assoc()): ?><option value="<?php echo esc($semester['id']); ?>" <?php echo (string) ($edit['semester_id'] ?? '') === (string) $semester['id'] ? 'selected' : ''; ?>><?php echo esc($semester['semester_name'] . ' ' . $semester['academic_year']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field"><label>Room</label><input name="room" value="<?php echo esc($edit['room'] ?? ''); ?>"></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save class</button></div>
                    </form>
                </section>
                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Teacher class allocation</h2><span class="badge">2026</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Class</th><th>Unit</th><th>Semester</th><th>Teacher</th><th>Room</th><th>Actions</th></tr></thead><tbody>
                        <?php while ($class = $classes->fetch_assoc()): ?><tr>
                            <td><?php echo esc($class['class_name']); ?></td><td><?php echo esc($class['course_code'] . ' - ' . $class['course_name']); ?></td><td><?php echo esc($class['semester_name'] . ' ' . $class['academic_year']); ?></td><td><?php echo esc($class['teacher_name']); ?></td><td><?php echo esc($class['room']); ?></td>
                            <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/attendance_sheet.php?class_id=' . $class['id'])); ?>">Sheet</a><a class="button button-small" href="<?php echo esc(app_url('admin/manage_classes.php?edit=' . $class['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this class?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo esc($class['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                        </tr><?php endwhile; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
