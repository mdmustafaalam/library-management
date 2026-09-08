<?php
// =====================================================
// reservations/add.php
// Create a new reservation (a member requests a book).
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int)($_POST['member_id'] ?? 0);
    $bookId   = (int)($_POST['book_id'] ?? 0);
    $resDate  = date('Y-m-d');

    if ($memberId <= 0 || !is_numeric($memberId)) $errors[] = 'Please select a valid member.';
    if ($bookId <= 0 || !is_numeric($bookId)) $errors[] = 'Please select a valid book.';

    if (empty($errors)) {
        // Check for duplicate pending reservation
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM reservations WHERE member_id = ? AND book_id = ? AND status = 'Pending'"
        );
        mysqli_stmt_bind_param($stmt, "ii", $memberId, $bookId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This member already has a pending reservation for this book.';
        } else {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO reservations (member_id, book_id, reservation_date, status)
                 VALUES (?, ?, ?, 'Pending')"
            );
            mysqli_stmt_bind_param($stmt, "iis", $memberId, $bookId, $resDate);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Reservation created successfully.";
                header("Location: list.php");
                exit;
            } else {
                $errors[] = 'Unable to create reservation. Please try again.';
            }
        }
    }
}

// Only Active members
$members = mysqli_query($conn, "SELECT * FROM members WHERE status = 'Active' ORDER BY name ASC");
$books   = mysqli_query($conn, "SELECT * FROM books ORDER BY title ASC");

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Add Reservation</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Reservations</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="add.php" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="member_id" class="form-label">Member <span class="text-danger">*</span></label>
                    <select class="form-select" id="member_id" name="member_id" required>
                        <option value="">-- Select Member --</option>
                        <?php while ($m = mysqli_fetch_assoc($members)): ?>
                            <option value="<?php echo $m['id']; ?>">
                                <?php echo htmlspecialchars($m['member_code'] . ' - ' . $m['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="book_id" class="form-label">Book <span class="text-danger">*</span></label>
                    <select class="form-select" id="book_id" name="book_id" required>
                        <option value="">-- Select Book --</option>
                        <?php while ($b = mysqli_fetch_assoc($books)): ?>
                            <option value="<?php echo $b['id']; ?>">
                                <?php echo htmlspecialchars($b['book_code'] . ' - ' . $b['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Create Reservation</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
