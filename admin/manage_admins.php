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

        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            $error = 'You cannot delete your own admin account while logged in.';
        } else {
            $stmt = $conn->prepare('DELETE FROM admins WHERE id = ?');
            $stmt->bind_param('i', $id);
            $message = $stmt->execute() ? 'Admin deleted successfully.' : 'Could not delete admin.';
        }
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '') {
            $error = 'Name and email are required.';
        } elseif ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE admins SET name = ?, email = ?, password = ? WHERE id = ?');
                $stmt->bind_param('sssi', $name, $email, $hash, $id);
            } else {
                $stmt = $conn->prepare('UPDATE admins SET name = ?, email = ? WHERE id = ?');
                $stmt->bind_param('ssi', $name, $email, $id);
            }

            $message = $stmt->execute() ? 'Admin updated successfully.' : 'Could not update admin. Check duplicate email.';
        } else {
            if ($password === '') {
                $error = 'Password is required for a new admin.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $email, $hash);
                $message = $stmt->execute() ? 'Admin created successfully.' : 'Could not create admin. Check duplicate email.';
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM admins WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$admins = $conn->query('SELECT id, name, email, created_at FROM admins ORDER BY created_at DESC');

$pageTitle = 'Manage Admins | WIN';
$assetPrefix = '../';
$dashboardLabel = 'WIN Admin';
$activeRole = 'admins';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
require __DIR__ . '/../includes/sidebar.php';
?>
            <main class="dashboard-main">
                <section class="panel">
                    <div class="panel-title"><h2><?php echo $edit ? 'Edit admin' : 'Create admin'; ?></h2><a class="button" href="<?php echo esc(app_url('admin/manage_admins.php')); ?>">New</a></div>
                    <?php if ($message): ?><div class="message message-success"><?php echo esc($message); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="message message-error"><?php echo esc($error); ?></div><?php endif; ?>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?php echo esc($edit['id'] ?? 0); ?>">
                        <div class="form-field"><label>Name</label><input name="name" value="<?php echo esc($edit['name'] ?? ''); ?>" required></div>
                        <div class="form-field"><label>Email</label><input type="email" name="email" value="<?php echo esc($edit['email'] ?? ''); ?>" required></div>
                        <div class="form-field form-field-full"><label>Password <?php echo $edit ? '(leave blank to keep)' : ''; ?></label><input type="password" name="password" <?php echo $edit ? '' : 'required'; ?>></div>
                        <div class="form-actions"><button class="button button-primary" type="submit">Save admin</button></div>
                    </form>
                </section>

                <section class="panel" style="margin-top:20px">
                    <div class="panel-title"><h2>Admins</h2><span class="badge">CRUD</span></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php while ($admin = $admins->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo esc($admin['name']); ?></td>
                                        <td><?php echo esc($admin['email']); ?></td>
                                        <td><?php echo esc($admin['created_at']); ?></td>
                                        <td class="row-actions">
                                            <a class="button button-small" href="<?php echo esc(app_url('admin/manage_admins.php?edit=' . $admin['id'])); ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this admin?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo esc($admin['id']); ?>">
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
