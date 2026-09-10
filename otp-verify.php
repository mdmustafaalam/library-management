<?php
// =====================================================
// otp-verify.php
// OTP verification - enter the 6-digit code to login.
// Auto-submits when 6 digits are entered or pasted.
// =====================================================

session_start();

// Must have come from otp-login.php
if (!isset($_SESSION['otp_email'])) {
    header("Location: login.php");
    exit;
}

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id']) || isset($_SESSION['user_type'])) {
    if (($_SESSION['user_type'] ?? '') === 'member') {
        header("Location: member/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;
}

require_once 'config/db.php';
require_once 'config/otp.php';

$email    = $_SESSION['otp_email'];
$userType = $_SESSION['otp_user_type'] ?? 'admin';
$maskedEmail = maskEmail($email);
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpInput = trim($_POST['otp'] ?? '');

    if ($otpInput === '') {
        $error = 'Please enter the OTP code.';
    } elseif (strlen($otpInput) !== 6 || !ctype_digit($otpInput)) {
        $error = 'OTP must be a 6-digit number.';
    } else {
        $result = validateOTP($email, $otpInput);

        if ($result['valid']) {
            $userType = $result['user_type'];
            $userId   = $result['user_id'];

            if ($userType === 'admin') {
                $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM admins WHERE id = ?");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM members WHERE id = ?");
            }
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $result2 = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result2);
            mysqli_stmt_close($stmt);

            if ($user) {
                $_SESSION['user_type']  = $userType;
                $_SESSION['user_id']    = $userId;
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                if ($userType === 'admin') {
                    $_SESSION['admin_id']    = $userId;
                    $_SESSION['admin_name']  = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                }

                unset($_SESSION['otp_email'], $_SESSION['otp_user_type']);

                if ($userType === 'member') {
                    header("Location: member/dashboard.php");
                } else {
                    header("Location: admin/dashboard.php");
                }
                exit;
            } else {
                $error = 'Account not found.';
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Library Management</title>
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
                <i class="bi bi-key"></i>
            </div>
            <h3>Verify OTP</h3>
            <p class="login-subtitle">Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($maskedEmail); ?></strong></p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="otp-verify.php" id="otpForm" novalidate>
                <div class="mb-4">
                    <label for="otp" class="form-label">OTP Code</label>
                    <input type="text" class="form-control otp-input" id="otp" name="otp"
                           placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                           inputmode="numeric" autocomplete="one-time-code" required autofocus>
                    <div class="form-text">Valid for 5 minutes</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login" id="verifyBtn">
                    <i class="bi bi-check-circle me-1"></i> Verify OTP
                </button>
            </form>

            <div class="login-footer">
                <p id="resend-section">
                    Didn't receive the code?
                    <a href="javascript:void(0)" id="resend-btn" onclick="resendOTP()">Resend OTP</a>
                    <span id="resend-timer" style="display:none;color:var(--text-muted);"></span>
                </p>
                <p class="mt-2"><a href="login.php" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Use a different email</a></p>
            </div>
        </div>
    </div>

    <script>
    var countdown = 60;
    var timerInterval = null;

    function startTimer() {
        var timerEl = document.getElementById('resend-timer');
        var btnEl = document.getElementById('resend-btn');
        btnEl.style.display = 'none';
        timerEl.style.display = 'inline';
        timerEl.textContent = 'Resend in ' + countdown + 's';

        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                clearInterval(timerInterval);
                timerEl.style.display = 'none';
                btnEl.style.display = 'inline';
                countdown = 60;
            } else {
                timerEl.textContent = 'Resend in ' + countdown + 's';
            }
        }, 1000);
    }

    startTimer();

    var otpInput = document.getElementById('otp');

    otpInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    otpInput.addEventListener('paste', function(e) {
        var self = this;
        setTimeout(function() {
            self.value = self.value.replace(/[^0-9]/g, '');
        }, 0);
    });

    function resendOTP() {
        fetch('otp-resend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                countdown = 60;
                startTimer();
                otpInput.value = '';
                otpInput.focus();
                var alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success py-2 mb-3';
                alertDiv.textContent = 'OTP resent successfully!';
                document.getElementById('otpForm').parentNode.insertBefore(alertDiv, document.getElementById('otpForm'));
                setTimeout(function() { alertDiv.remove(); }, 4000);
            } else {
                alert(data.error || 'Failed to resend OTP.');
            }
        })
        .catch(function() {
            alert('Network error. Please try again.');
        });
    }
    </script>
</body>
</html>
