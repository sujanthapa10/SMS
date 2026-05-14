<?php
require __DIR__ . '/../includes/auth.php';
require_login('admin');
require __DIR__ . '/../database/connection.php';

function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function nullable_int($value): ?int { return $value === '' ? null : (int) $value; }

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Unit deleted successfully.' : 'Could not delete unit. Remove classes, enrollments, attendance, or grades first.';
    }
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = trim($_POST['course_code'] ?? '');
        $name = trim($_POST['course_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacherId = nullable_int($_POST['teacher_id'] ?? '');

        if ($code === '' || $name === '') {
            $error = 'Unit code and unit name are required.';
        } elseif ($id > 0) {
            $stmt = $conn->prepare('UPDATE courses SET course_code = ?, course_name = ?, description = ?, teacher_id = ? WHERE id = ?');
            $stmt->bind_param('sssii', $code, $name, $description, $teacherId, $id);
            $message = $stmt->execute() ? 'Unit updated successfully.' : 'Could not update unit. Check duplicate unit code.';
        } else {
            $stmt = $conn->prepare('INSERT INTO courses (course_code, course_name, description, teacher_id) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $code, $name, $description, $teacherId);
            $message = $stmt->execute() ? 'Unit created successfully.' : 'Could not create unit. Check duplicate unit code.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$teachers = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM teachers ORDER BY first_name");
$courses = $conn->query("SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name FROM courses c LEFT JOIN teachers t ON t.id = c.teacher_id ORDER BY c.created_at DESC");

$pageTitle = 'Manage Units | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'courses';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit unit' : 'Create unit'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_courses.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Unit code</label><input name="course_code" value="<?php echo esc($edit['course_code'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Unit name</label><input name="course_name" value="<?php echo esc($edit['course_name'] ?? ''); ?>" required></div>
                        <div class="form-field form-field-full"><label>Default teacher</label><select name="teacher_id"><option value="">Unassigned</option><?php while ($teacher = $teachers->fetch_assoc()): ?><option value="<?php echo esc($teacher['id']); ?>" <?php echo (string) ($edit['teacher_id'] ?? '') === (string) $teacher['id'] ? 'selected' : ''; ?>><?php echo esc($teacher['name']); ?></option><?php endwhile; ?></select></div>
                        <div class="form-field form-field-full"><label>Description</label><textarea name="description"><?php echo esc($edit['description'] ?? ''); ?></textarea></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save unit</button></div>
                    </form>
                </section>
                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Units in courses</h2><span class="badge">CRUD</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Code</th><th>Unit</th><th>Default teacher</th><th>Actions</th></tr></thead><tbody>
                        <?php while ($course = $courses->fetch_assoc()): ?><tr>
                            <td><?php echo esc($course['course_code']); ?></td><td><?php echo esc($course['course_name']); ?></td><td><?php echo esc($course['teacher_name'] ?: 'Unassigned'); ?></td>
                            <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/manage_courses.php?edit=' . $course['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this unit?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo esc($course['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                        </tr><?php endwhile; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
