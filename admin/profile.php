<?php
// =====================================================
// admin/profile.php
// View and update the logged-in admin's name and email.
// After updating, the session values are also refreshed.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$adminId = (int)$_SESSION['admin_id'];

// Fetch current details
$stmt = mysqli_prepare($conn, "SELECT id, name, email, created_at FROM admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$admin) {
    $_SESSION['success'] = "Admin not found.";
    header("Location: ../login.php");
    exit;
}

$errors = [];
$name  = $admin['name'];
$email = $admin['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE admins SET name = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $adminId);

        if (mysqli_stmt_execute($stmt)) {
            // Refresh session values
            $_SESSION['admin_name']  = $name;
            $_SESSION['admin_email'] = $email;
            $_SESSION['success'] = "Profile updated successfully.";
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = 'Unable to update profile (email may already be in use).';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}

// Initials for the avatar (e.g. "Aa" for "Aarav Alam")
$nameParts = preg_split('/\s+/', trim($admin['name']));
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <h1 class="page-title mb-2">My Profile</h1>
    <a href="../admin/change-password.php" class="btn btn-outline-primary mb-2"><i class="bi bi-shield-lock me-1"></i> Change Password</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="row g-3">
    <!-- Profile summary card -->
    <div class="col-lg-4">
        <div class="card overflow-hidden">
            <div class="profile-cover"></div>
            <div class="card-body text-center pt-5">
                <div class="profile-avatar"><?php echo $initials; ?></div>
                <h4 class="mb-1"><?php echo htmlspecialchars($admin['name']); ?></h4>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($admin['email']); ?></p>
                <span class="badge bg-primary mb-3"><i class="bi bi-shield-check me-1"></i>Administrator</span>

                <hr class="my-3">

                <div class="row text-start small">
                    <div class="col-6 text-muted">Account ID</div>
                    <div class="col-6 text-end fw-semibold">#<?php echo (int)$admin['id']; ?></div>
                    <div class="col-6 text-muted mt-2">Joined</div>
                    <div class="col-6 text-end mt-2 fw-semibold"><?php echo date('d M Y', strtotime($admin['created_at'])); ?></div>
                    <div class="col-6 text-muted mt-2">Role</div>
                    <div class="col-6 text-end mt-2 fw-semibold">Admin / Librarian</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update form card -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square me-1"></i> Update Profile
            </div>
            <div class="card-body">
                <form method="POST" action="profile.php" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-1"></i> Account Security
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">Password</div>
                        <div class="text-muted small">Use a strong password that you don't use for any other site.</div>
                    </div>
                    <a href="../admin/change-password.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-key me-1"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
