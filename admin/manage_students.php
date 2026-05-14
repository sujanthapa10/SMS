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
        $conn->begin_transaction();

        try {
            foreach (['attendance', 'grades', 'enrollments'] as $table) {
                $stmt = $conn->prepare("DELETE FROM {$table} WHERE student_id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
            }

            $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $conn->commit();
            $message = 'Student and related records deleted successfully.';
        } catch (Throwable $exception) {
            $conn->rollback();
            $error = 'Could not delete student. Please try again.';
        }
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $studentNumber = trim($_POST['student_number'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $dob = $_POST['date_of_birth'] ?: null;
        $gender = $_POST['gender'] ?: null;
        $address = trim($_POST['address'] ?? '');

        if ($studentNumber === '' || $firstName === '' || $lastName === '' || $email === '') {
            $error = 'Student number, name, and email are required.';
        } elseif ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE students SET student_number = ?, first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, date_of_birth = ?, gender = ?, address = ? WHERE id = ?');
                $stmt->bind_param('sssssssssi', $studentNumber, $firstName, $lastName, $email, $hash, $phone, $dob, $gender, $address, $id);
            } else {
                $stmt = $conn->prepare('UPDATE students SET student_number = ?, first_name = ?, last_name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ?, address = ? WHERE id = ?');
                $stmt->bind_param('ssssssssi', $studentNumber, $firstName, $lastName, $email, $phone, $dob, $gender, $address, $id);
            }
            $message = $stmt->execute() ? 'Student updated successfully.' : 'Could not update student. Check duplicate student number.';
        } else {
            if ($password === '') {
                $error = 'Password is required for a new student.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO students (student_number, first_name, last_name, email, password, phone, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sssssssss', $studentNumber, $firstName, $lastName, $email, $hash, $phone, $dob, $gender, $address);
                $message = $stmt->execute() ? 'Student created successfully.' : 'Could not create student. Check duplicate student number or email.';
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$students = $conn->query('SELECT * FROM students ORDER BY created_at DESC');

$pageTitle = 'Manage Students | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'students';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit student' : 'Create student'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_students.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Student number</label><input name="student_number" value="<?php echo esc($edit['student_number'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>First name</label><input name="first_name" value="<?php echo esc($edit['first_name'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Last name</label><input name="last_name" value="<?php echo esc($edit['last_name'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Email</label><input type="email" name="email" value="<?php echo esc($edit['email'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Password <?php echo $edit ? '(leave blank to keep)' : ''; ?></label><input type="password" name="password" <?php echo $edit ? '' : 'required'; ?>></div>
                        <div class="form-field"><label>Phone</label><input name="phone" value="<?php echo esc($edit['phone'] ?? ''); ?>"></div>
                        <div class="form-field"><label>Date of birth</label><input type="date" name="date_of_birth" value="<?php echo esc($edit['date_of_birth'] ?? ''); ?>"></div>
                        <div class="form-field">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">Select</option>
                                <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                                    <option value="<?php echo esc($gender); ?>" <?php echo ($edit['gender'] ?? '') === $gender ? 'selected' : ''; ?>><?php echo esc($gender); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field form-field-full"><label>Address</label><textarea name="address"><?php echo esc($edit['address'] ?? ''); ?></textarea></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save student</button></div>
                    </form>
                </section>

                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Students</h2><span class="badge">CRUD</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>No.</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo esc($student['student_number']); ?></td>
                                        <td><?php echo esc($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo esc($student['email']); ?></td>
                                        <td class="row-actions">
                                            <a class="button button-small" href="<?php echo esc(app_url('admin/manage_students.php?edit=' . $student['id'])); ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this student?');">
                                                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo esc($student['id']); ?>">
                                                <button class="button button-small button-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
