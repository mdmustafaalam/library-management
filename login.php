<?php
// =====================================================
// login.php
// OTP login page - enter email to receive a one-time password.
// =====================================================

session_start();

// If already logged in, go straight to dashboard
if (isset($_SESSION['admin_id']) || (isset($_SESSION['user_type']) && isset($_SESSION['user_id']))) {
    if (($_SESSION['user_type'] ?? '') === 'member') {
        header("Location: member/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;
}

require_once 'config/db.php';
require_once 'config/otp.php';
require_once 'config/mailer.php';

$error   = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $user = findUserByEmail($email);

        if (!$user['found']) {
            $error = 'No account found with this email address.';
        } elseif (!canResend($email)) {
            $error = 'Please wait before requesting a new OTP.';
        } else {
            $otp     = generateOTP();
            $stored  = storeOTP($user['user_type'], $user['user_id'], $email, $otp);

            if ($stored) {
                $sent = sendOTP($email, $otp, $user['name']);

                if ($sent) {
                    $_SESSION['otp_email']      = $email;
                    $_SESSION['otp_user_type']  = $user['user_type'];
                    header("Location: otp-verify.php");
                    exit;
                } else {
                    $error = 'Failed to send OTP email. Please try again.';
                }
            } else {
                $error = 'Something went wrong. Please try again.';
            }
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
            <p class="login-subtitle">Enter your email to receive a one-time password</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login">
                    <i class="bi bi-envelope-check me-1"></i> Send OTP
                </button>
            </form>
        </div>
    </div>
</body>
</html>
