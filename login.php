<?php
// =====================================================
// login.php
// Admin login page.
// - Connects to the database
// - Validates email + password using prepared statements
// - Uses password_verify() to check the password hash
// - Starts a session on success
// =====================================================

session_start();

// If already logged in, go straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in both email and password.';
    } else {
        // Fetch the admin by email using a prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin  = mysqli_fetch_assoc($result);

        // Verify the entered password against the stored hash
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_name']  = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="card login-card">
            <div class="login-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <h3>Library Management</h3>
            <p class="login-subtitle">Admin / Librarian Login</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </form>

            <div class="login-footer">
                <p>Default admin: <code>admin@library.com</code> / <code>admin123</code></p>
            </div>
        </div>
    </div>
</body>
</html>
