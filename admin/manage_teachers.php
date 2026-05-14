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
        $stmt = $conn->prepare('DELETE FROM teachers WHERE id = ?');
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? 'Teacher deleted successfully.' : 'Could not delete teacher. Reassign related courses first.';
    }
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $employeeNumber = trim($_POST['employee_number'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if ($employeeNumber === '' || $firstName === '' || $lastName === '' || $email === '') {
            $error = 'Employee number, name, and email are required.';
        } elseif ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE teachers SET employee_number = ?, first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, department = ? WHERE id = ?');
                $stmt->bind_param('sssssssi', $employeeNumber, $firstName, $lastName, $email, $hash, $phone, $department, $id);
            } else {
                $stmt = $conn->prepare('UPDATE teachers SET employee_number = ?, first_name = ?, last_name = ?, email = ?, phone = ?, department = ? WHERE id = ?');
                $stmt->bind_param('ssssssi', $employeeNumber, $firstName, $lastName, $email, $phone, $department, $id);
            }
            $message = $stmt->execute() ? 'Teacher updated successfully.' : 'Could not update teacher. Check duplicate employee number or email.';
        } else {
            if ($password === '') {
                $error = 'Password is required for a new teacher.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO teachers (employee_number, first_name, last_name, email, password, phone, department) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sssssss', $employeeNumber, $firstName, $lastName, $email, $hash, $phone, $department);
                $message = $stmt->execute() ? 'Teacher created successfully.' : 'Could not create teacher. Check duplicate employee number or email.';
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$teachers = $conn->query('SELECT * FROM teachers ORDER BY created_at DESC');

$pageTitle = 'Manage Teachers | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'teachers';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit teacher' : 'Create teacher'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_teachers.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Employee number</label><input name="employee_number" value="<?php echo esc($edit['employee_number'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>First name</label><input name="first_name" value="<?php echo esc($edit['first_name'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Last name</label><input name="last_name" value="<?php echo esc($edit['last_name'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Email</label><input type="email" name="email" value="<?php echo esc($edit['email'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Password <?php echo $edit ? '(leave blank to keep)' : ''; ?></label><input type="password" name="password" <?php echo $edit ? '' : 'required'; ?>></div>
                        <div class="form-field"><label>Phone</label><input name="phone" value="<?php echo esc($edit['phone'] ?? ''); ?>"></div>
                        <div class="form-field form-field-full"><label>Department</label><input name="department" value="<?php echo esc($edit['department'] ?? ''); ?>"></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save teacher</button></div>
                    </form>
                </section>
                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Teachers</h2><span class="badge">CRUD</span></div>
                    <div class="table-wrap"><table><thead><tr><th>No.</th><th>Name</th><th>Department</th><th>Actions</th></tr></thead><tbody>
                        <?php while ($teacher = $teachers->fetch_assoc()): ?><tr>
                            <td><?php echo esc($teacher['employee_number']); ?></td><td><?php echo esc($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td><td><?php echo esc($teacher['department']); ?></td>
                            <td class="row-actions"><a class="button button-small" href="<?php echo esc(app_url('admin/manage_teachers.php?edit=' . $teacher['id'])); ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this teacher?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo esc($teacher['id']); ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></td>
                        </tr><?php endwhile; ?>
                    </tbody></table></div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
