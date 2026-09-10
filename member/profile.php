<?php
// =====================================================
// member/profile.php
// Member profile - view and update own name, email,
// phone and address. Member code and status are read-only.
// =====================================================

require_once '../includes/auth.php';

// Members only - admins use admin/profile.php
if ($isAdmin) {
    header("Location: " . getBaseUrl() . "admin/profile.php");
    exit;
}

require_once '../config/db.php';
require_once '../config/helpers.php';

$memberId = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM members WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$member) {
    $_SESSION['success'] = "Member not found.";
    header("Location: ../logout.php");
    exit;
}

$errors  = [];
$name    = $member['name'];
$email   = $member['email'];
$phone   = $member['phone'];
$address = $member['address'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    // Check for duplicate email excluding current member
    if ($email !== '') {
        $stmt = mysqli_prepare($conn, "SELECT id FROM members WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $email, $memberId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This email is already used by another member.';
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn,
            "UPDATE members SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $address, $memberId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['success'] = "Profile updated successfully.";
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = 'Unable to update profile. Please try again.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}

$nameParts = preg_split('/\s+/', trim($member['name']));
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <h1 class="page-title mb-2">My Profile</h1>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card overflow-hidden">
            <div class="profile-cover"></div>
            <div class="card-body text-center pt-5">
                <div class="profile-avatar"><?php echo $initials; ?></div>
                <h4 class="mb-1"><?php echo htmlspecialchars($member['name']); ?></h4>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($member['email']); ?></p>
                <span class="badge bg-success mb-3"><i class="bi bi-person-check me-1"></i>Member</span>

                <hr class="my-3">

                <div class="row text-start small">
                    <div class="col-6 text-muted">Member Code</div>
                    <div class="col-6 text-end fw-semibold"><code><?php echo htmlspecialchars($member['member_code']); ?></code></div>
                    <div class="col-6 text-muted mt-2">Joined</div>
                    <div class="col-6 text-end mt-2 fw-semibold"><?php echo date('d M Y', strtotime($member['created_at'])); ?></div>
                    <div class="col-6 text-muted mt-2">Status</div>
                    <div class="col-6 text-end mt-2 fw-semibold"><?php echo htmlspecialchars($member['status']); ?></div>
                </div>
            </div>
        </div>
    </div>

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
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" class="form-control" id="address" name="address"
                                   value="<?php echo htmlspecialchars($address); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>