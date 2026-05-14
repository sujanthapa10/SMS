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
        $stmt = $conn->prepare('DELETE FROM semesters WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Semester deleted successfully.' : 'Could not delete semester. Remove related classes first.';
    }
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['semester_name'] ?? '');
        $year = (int) ($_POST['academic_year'] ?? 2026);
        $start = $_POST['start_date'] ?: null;
        $end = $_POST['end_date'] ?: null;

        if ($name === '' || !$year) {
            $error = 'Semester name and year are required.';
        } elseif ($id > 0) {
            $stmt = $conn->prepare('UPDATE semesters SET semester_name = ?, academic_year = ?, start_date = ?, end_date = ? WHERE id = ?');
            $stmt->bind_param('sissi', $name, $year, $start, $end, $id);
            $message = $stmt->execute() ? 'Semester updated successfully.' : 'Could not update semester.';
        } else {
            $stmt = $conn->prepare('INSERT INTO semesters (semester_name, academic_year, start_date, end_date) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('siss', $name, $year, $start, $end);
            $message = $stmt->execute() ? 'Semester created successfully.' : 'Could not create semester.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM semesters WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$semesters = $conn->query('SELECT * FROM semesters ORDER BY academic_year DESC, semester_name');

$pageTitle = 'Manage Semesters | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'semesters';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit semester' : 'Create semester'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_semesters.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Semester</label><input name="semester_name" value="<?php echo esc($edit['semester_name'] ?? 'Semester 1'); ?>" required></div>
                        <div class="form-field"><label>Year</label><input type="number" name="academic_year" value="<?php echo esc($edit['academic_year'] ?? '2026'); ?>" required></div>
                        <div class="form-field"><label>Start date</label><input type="date" name="start_date" value="<?php echo esc($edit['start_date'] ?? ''); ?>"></div>
                        <div class="form-field"><label>End date</label><input type="date" name="end_date" value="<?php echo esc($edit['end_date'] ?? ''); ?>"></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save semester</button></div>
                    </form>
                </section>
                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>2026 semesters</h2><span class="badge">Academic year</span></div>
                    <div class="table-wrap"><table><thead><tr><th>Semester</th><th>Year</th><th>Start</th><th>End</th><th>Actions</th></tr></thead><tbody>
                        <?php while ($semester = $semesters->fetch_assoc()): ?><tr>
                            <td><?php echo esc($semester['semester_name']); ?></td><td><?php echo esc($semester['academic_year']); ?></td><td><?php echo esc($semester['start_date']); ?></td><td><?php echo esc($semester['end_date']); ?></td>
                            <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/manage_semesters.php?edit=' . $semester['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this semester?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo esc($semester['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                        </tr><?php endwhile; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
