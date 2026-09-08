<?php
// =====================================================
// members/history.php
// Shows a member's details and complete borrowing history.
// URL: history.php?id=5
// Uses INNER JOIN to combine transactions + books.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$id = (int)$_GET['id'];

// --- Fetch the member ---
$stmt = mysqli_prepare($conn, "SELECT * FROM members WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$member) {
    $_SESSION['success'] = "Member not found.";
    header("Location: list.php");
    exit;
}

// --- Fetch borrowing history using JOIN ---
$stmt = mysqli_prepare($conn,
    "SELECT t.*, b.book_code, b.title, b.author
     FROM transactions t
     INNER JOIN books b ON t.book_id = b.id
     WHERE t.member_id = ?
     ORDER BY t.id DESC"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$history = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title mb-3">Member History</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Members</a>
</div>

<!-- Member details card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person me-1"></i> Member Details
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Code:</strong> <code><?php echo htmlspecialchars($member['member_code']); ?></code></div>
            <div class="col-md-3"><strong>Name:</strong> <?php echo htmlspecialchars($member['name']); ?></div>
            <div class="col-md-3"><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></div>
            <div class="col-md-3">
                <strong>Status:</strong>
                <?php if ($member['status'] === 'Active'): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Borrowing history -->
<div class="card">
    <div class="card-header"><i class="bi bi-clock-history me-1"></i> Borrowing History</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Book Code</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($history) > 0): ?>
                        <?php while ($h = mysqli_fetch_assoc($history)): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($h['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($h['title']); ?></td>
                                <td><?php echo htmlspecialchars($h['author']); ?></td>
                                <td><?php echo date('d M Y', strtotime($h['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($h['due_date'])); ?></td>
                                <td><?php echo $h['return_date'] ? date('d M Y', strtotime($h['return_date'])) : '—'; ?></td>
                                <td>
                                    <?php
                                    $status = $h['status'];
                                    $badge = match ($status) {
                                        'Issued'   => 'bg-primary',
                                        'Overdue'  => 'bg-danger',
                                        'Returned' => 'bg-success',
                                        default    => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No borrowing history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
