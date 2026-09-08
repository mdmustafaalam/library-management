<?php
// =====================================================
// transactions/issued-books.php
// Lists all currently issued / overdue books.
// Uses JOIN across transactions, members, books.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

// Update overdue status whenever this page loads
mysqli_query($conn, "UPDATE transactions SET status = 'Overdue' WHERE status = 'Issued' AND due_date < CURDATE()");

$result = mysqli_query($conn,
    "SELECT t.*, m.member_code, m.name AS member_name, b.book_code, b.title
     FROM transactions t
     INNER JOIN members m ON t.member_id = m.id
     INNER JOIN books b ON t.book_id = b.id
     WHERE t.status IN ('Issued', 'Overdue')
     ORDER BY t.id DESC"
);

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title mb-3">Issued Books</h1>
    <a href="issue-book.php" class="btn btn-primary mb-3"><i class="bi bi-arrow-right-circle me-1"></i> Issue Book</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member Code</th>
                        <th>Member Name</th>
                        <th>Book Code</th>
                        <th>Book Name</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($t = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><code><?php echo htmlspecialchars($t['member_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['member_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($t['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($t['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($t['due_date'])); ?></td>
                                <td>
                                    <?php if ($t['status'] === 'Overdue'): ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Issued</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="return-book.php?id=<?php echo $t['id']; ?>"
                                       class="btn btn-sm btn-success" data-confirm="Return this book?">
                                        <i class="bi bi-arrow-return-left me-1"></i> Return
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No issued books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
