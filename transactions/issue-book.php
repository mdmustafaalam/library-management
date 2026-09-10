<?php
// =====================================================
// transactions/issue-book.php
// Issue a book to a member.
// - Only Active members are shown
// - Only books with available_copies > 0 are shown
// - Default due date = issue date + 14 days
// - Uses a DATABASE TRANSACTION so that inserting the
//   transaction and decreasing copies happen together.
// =====================================================

require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
require_once '../config/helpers.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId  = (int)($_POST['member_id'] ?? 0);
    $bookId    = (int)($_POST['book_id'] ?? 0);
    $issueDate = $_POST['issue_date'] ?? '';
    $dueDate   = $_POST['due_date'] ?? '';

    // Step 1: Validate dates and IDs
    if ($memberId <= 0 || !is_numeric($memberId)) $errors[] = 'Please select a valid member.';
    if ($bookId <= 0 || !is_numeric($bookId)) $errors[] = 'Please select a valid book.';
    if ($issueDate === '' || $dueDate === '') $errors[] = 'Issue and due dates are required.';
    if ($dueDate < $issueDate) $errors[] = 'Due date cannot be before the issue date.';

    if (empty($errors)) {
        mysqli_begin_transaction($conn);

        try {
            // Step 2: Verify member exists and is Active
            $stmt = mysqli_prepare($conn, "SELECT * FROM members WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt, "i", $memberId);
            mysqli_stmt_execute($stmt);
            $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$member) { throw new Exception('Member not found.'); }
            if ($member['status'] !== 'Active') { throw new Exception('Member account is inactive.'); }

            // Step 3: Verify book exists and has available copies
            $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt, "i", $bookId);
            mysqli_stmt_execute($stmt);
            $book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$book) { throw new Exception('Book not found.'); }
            if ((int)$book['available_copies'] <= 0) { throw new Exception('No copies available for this book.'); }

            // Step 4: Insert transaction
            $stmt = mysqli_prepare($conn,
                "INSERT INTO transactions (member_id, book_id, issue_date, due_date, status)
                 VALUES (?, ?, ?, ?, 'Issued')"
            );
            mysqli_stmt_bind_param($stmt, "iiss", $memberId, $bookId, $issueDate, $dueDate);
            if (!mysqli_stmt_execute($stmt)) { throw new Exception('Unable to create transaction.'); }

            // Step 5: Decrease available copies
            $stmt = mysqli_prepare($conn,
                "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, "i", $bookId);
            if (!mysqli_stmt_execute($stmt)) { throw new Exception('Unable to update book copies.'); }

            mysqli_commit($conn);

            $_SESSION['success'] = "Book issued successfully.";
            header("Location: issued-books.php");
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}

// --- Load dropdown data ---
// Only Active members
$members = mysqli_query($conn, "SELECT * FROM members WHERE status = 'Active' ORDER BY name ASC");
// Only books with available copies
$books   = mysqli_query($conn, "SELECT * FROM books WHERE available_copies > 0 ORDER BY title ASC");

$today = date('Y-m-d');
$defaultDue = date('Y-m-d', strtotime($today . ' +14 days'));

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Issue Book</h1>
    <a href="issued-books.php" class="btn btn-secondary mb-3"><i class="bi bi-list-check me-1"></i> Issued Books</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="issue-book.php" novalidate>
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
                                <?php echo htmlspecialchars($b['book_code'] . ' - ' . $b['title'] . ' (' . (int)$b['available_copies'] . ' available)'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="issue_date" name="issue_date"
                           value="<?php echo $today; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span> (14 days default)</label>
                    <input type="date" class="form-control" id="due_date" name="due_date"
                           value="<?php echo $defaultDue; ?>" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i> Issue Book</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
