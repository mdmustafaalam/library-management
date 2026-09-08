<?php
// =====================================================
// members/add.php
// Add a new library member.
// - Auto-generates the member code (MEM001, MEM002...)
// - Validates input server-side
// - Inserts using a prepared statement
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$errors   = [];
$name     = '';
$email    = '';
$phone    = '';
$address  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Check for duplicate email
    if ($email !== '') {
        $stmt = mysqli_prepare($conn, "SELECT id FROM members WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This email is already used by another member.';
        }
    }

    if (empty($errors)) {
        $memberCode = generateMemberCode($conn);

        $stmt = mysqli_prepare($conn,
            "INSERT INTO members (member_code, name, email, phone, address, status)
             VALUES (?, ?, ?, ?, ?, 'Active')"
        );
        mysqli_stmt_bind_param($stmt, "sssss", $memberCode, $name, $email, $phone, $address);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Member added successfully. Code: " . $memberCode;
            header("Location: list.php");
            exit;
        } else {
            $errors[] = 'Unable to add member. Please try again.';
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

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Add Member</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="add.php" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?php echo htmlspecialchars($name); ?>" required>
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
                    <label class="form-label">Member Code</label>
                    <input type="text" class="form-control" value="Auto-generated" disabled>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add Member</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
