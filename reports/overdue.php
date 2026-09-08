<?php
// =====================================================
// reports/overdue.php
// Shows all overdue books: issued/overdue transactions
// whose due date has already passed.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';
require_once '../config/fine-config.php';

// Update overdue status
mysqli_query($conn, "UPDATE transactions SET status = 'Overdue' WHERE status = 'Issued' AND due_date < CURDATE()");

$result = mysqli_query($conn,
    "SELECT t.*, m.member_code, m.name AS member_name,
            b.book_code, b.title, b.author
     FROM transactions t
     INNER JOIN members m ON t.member_id = m.id
     INNER JOIN books b ON t.book_id = b.id
     WHERE t.due_date < CURDATE()
       AND t.status IN ('Issued', 'Overdue')
     ORDER BY t.due_date ASC"
);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h1 class="page-title">Overdue Books</h1>

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
                        <th>Days Overdue</th>
                        <th>Estimated Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($t = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $overdueDays = (int)((strtotime(date('Y-m-d')) - strtotime($t['due_date'])) / 86400);
                            $fine = $overdueDays * $finePerDay;
                            ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><code><?php echo htmlspecialchars($t['member_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['member_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($t['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($t['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($t['due_date'])); ?></td>
                                <td><span class="badge bg-danger"><?php echo $overdueDays; ?> day(s)</span></td>
                                <td>₹<?php echo number_format($fine, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No overdue books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
