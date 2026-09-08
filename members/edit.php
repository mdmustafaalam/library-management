<?php
// =====================================================
// members/edit.php
// Edit an existing member (name, email, phone, address, status).
// URL: edit.php?id=5
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

// --- Validate the id from the URL ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$id = (int)$_GET['id'];

// --- Fetch the member ---
$stmt = mysqli_prepare($conn, "SELECT * FROM members WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

if (!$member) {
    $_SESSION['success'] = "Member not found.";
    header("Location: list.php");
    exit;
}

$errors = [];
$name    = $member['name'];
$email   = $member['email'];
$phone   = $member['phone'];
$address = $member['address'];
$status  = $member['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status  = $_POST['status'] ?? 'Active';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($status, ['Active', 'Inactive'])) {
        $errors[] = 'Invalid status value.';
    }

    // Check for duplicate email excluding the current member
    if ($email !== '') {
        $stmt = mysqli_prepare($conn, "SELECT id FROM members WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $email, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This email is already used by another member.';
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn,
            "UPDATE members SET name = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $address, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Member updated successfully.";
            header("Location: list.php");
            exit;
        } else {
            $errors[] = 'Unable to update member. Please try again.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Edit Member</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Member Code</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['member_code']); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                           value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Member</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
