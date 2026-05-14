<?php
require_once __DIR__ . '/includes/paths.php';

session_start();

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ' . dashboard_path($_SESSION['role']));
    exit;
}

function dashboard_path(string $role): string
{
    return match ($role) {
        'admin' => app_url('admin/admin_dashboard.php'),
        'teacher' => app_url('teachers/teacher_dashboard.php'),
        'student' => app_url('students/student_dashboard.php'),
        default => app_url('index.php'),
    };
}

$error = '';
$notice = isset($_GET['logged_out']) ? 'You have been logged out successfully.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/database/connection.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $loginSources = [
            'admin' => "SELECT id, name, email, password FROM admins WHERE email = ? LIMIT 1",
            'teacher' => "SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, password FROM teachers WHERE email = ? LIMIT 1",
            'student' => "SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, password FROM students WHERE email = ? LIMIT 1",
        ];

        foreach ($loginSources as $role => $query) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $role;

                header('Location: ' . dashboard_path($role));
                exit;
            }
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WIN Student Management Sytem</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_url('css/auth.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-brand" aria-label="WIN login overview">
            <a class="brand" href="<?php echo htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Back to WIN home">
                <span class="brand-mark">
                    <img src="<?php echo htmlspecialchars(app_url('images/logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="WIN logo">
                </span>
                <span>
                    <strong>WIN</strong>
                    <small>Student Management Sytem</small>
                </span>
            </a>

            <div class="brand-copy">
                <p class="eyebrow">Secure portal access</p>
                <h1>Welcome back to WIN.</h1>
                <p>Sign in to continue managing students, teachers, courses, attendance, and academic records.</p>
            </div>

            <div class="auth-stats" aria-label="System highlights">
                <span>Admin</span>
                <span>Teacher</span>
                <span>Student</span>
            </div>
        </section>

        <section class="login-card" aria-labelledby="login-title">
            <div class="card-heading">
                <p class="eyebrow">Account login</p>
                <h2 id="login-title">Sign in</h2>
            </div>

            <?php if ($notice): ?>
                <div class="message message-success"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars(app_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>" method="post" class="login-form">
                <label for="email">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    placeholder="admin@sms.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    autocomplete="email"
                    required
                >

                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Login</button>
            </form>

            <a class="back-link" href="<?php echo htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8'); ?>">Back to home</a>
        </section>
    </main>

</body>
</html>
