<?php
// =====================================================
// admin/change-password.php
// Change the logged-in admin's password.
// This file is included from /admin/, one folder deep.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$adminId = (int)$_SESSION['admin_id'];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Fetch the current stored hash
    $stmt = mysqli_prepare($conn, "SELECT password FROM admins WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $adminId);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Step 1: Verify current password
    if (!$admin || !password_verify($current, $admin['password'])) {
        $errors[] = 'Current password is incorrect.';
    }
    // Step 2: New/confirm matching
    if ($newPass !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    }
    // Step 3: Require reasonable length
    if (strlen($newPass) < 4) {
        $errors[] = 'New password must be at least 4 characters long.';
    }

    if (empty($errors)) {
        // Step 5: Hash new password and update
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $newHash, $adminId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Password changed successfully.";
            header("Location: change-password.php");
            exit;
        } else {
            $errors[] = 'Unable to change password. Please try again.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <h1 class="page-title mb-2">Change Password</h1>
    <a href="../admin/profile.php" class="btn btn-outline-secondary mb-2"><i class="bi bi-arrow-left me-1"></i> Back to Profile</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock me-1"></i> Update Your Password
            </div>
            <div class="card-body">
                <form method="POST" action="change-password.php" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" placeholder="Enter your current password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" placeholder="Enter a new password" required>
                        </div>
                        <div class="form-text">Minimum 4 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" placeholder="Re-enter the new password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-check me-1"></i> Password Tips
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Use at least <strong>4 characters</strong> — longer is always better.</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Mix uppercase, lowercase, numbers and symbols.</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Avoid using your name, email or "1234" sequences.</span>
                    </li>
                    <li class="d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Don't reuse a password you use on other websites.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
