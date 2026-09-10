<?php
// =====================================================
// admin/dashboard.php
// Shows overall statistics using SQL aggregate functions
// (COUNT, SUM) plus recent activity lists.
// =====================================================

require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';

// Update overdue transactions whenever the dashboard loads
mysqli_query($conn, "UPDATE transactions SET status = 'Overdue' WHERE status = 'Issued' AND due_date < CURDATE()");

// Helper for reading a single aggregate value
function scalar($conn, $sql)
{
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row ? array_values($row)[0] : 0;
}

$totalMembers    = scalar($conn, "SELECT COUNT(*) FROM members");
$totalTitles     = scalar($conn, "SELECT COUNT(*) FROM books");
$totalCopies     = scalar($conn, "SELECT SUM(total_copies) FROM books");
$availableCopies = scalar($conn, "SELECT SUM(available_copies) FROM books");
$issuedBooks     = scalar($conn, "SELECT COUNT(*) FROM transactions WHERE status IN ('Issued','Overdue')");
$overdueBooks    = scalar($conn, "SELECT COUNT(*) FROM transactions WHERE status = 'Overdue'");
$pendingReserves = scalar($conn, "SELECT COUNT(*) FROM reservations WHERE status = 'Pending'");
$pendingFines    = scalar($conn, "SELECT COALESCE(SUM(amount - paid_amount),0) FROM fines WHERE status != 'Paid'");

// Recently issued books (last 5)
$recent = mysqli_query($conn,
    "SELECT t.id, t.issue_date, t.due_date, t.status,
            m.member_code, m.name AS member_name,
            b.title
     FROM transactions t
     INNER JOIN members m ON t.member_id = m.id
     INNER JOIN books b ON t.book_id = b.id
     ORDER BY t.id DESC
     LIMIT 5"
);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <h1 class="page-title mb-0">Dashboard</h1>
    <div class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
</div>

<div class="row g-3">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$totalMembers; ?></div>
                    <div class="dash-label">Total Members</div>
                </div>
                <div class="dash-icon"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-purple">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$totalTitles; ?></div>
                    <div class="dash-label">Book Titles</div>
                </div>
                <div class="dash-icon"><i class="bi bi-book"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$totalCopies; ?></div>
                    <div class="dash-label">Total Copies</div>
                </div>
                <div class="dash-icon"><i class="bi bi-layers"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-info">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$availableCopies; ?></div>
                    <div class="dash-label">Available Copies</div>
                </div>
                <div class="dash-icon"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$issuedBooks; ?></div>
                    <div class="dash-label">Currently Issued</div>
                </div>
                <div class="dash-icon"><i class="bi bi-journal-arrow-up"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$overdueBooks; ?></div>
                    <div class="dash-label">Overdue Books</div>
                </div>
                <div class="dash-icon"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-orange">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number"><?php echo (int)$pendingReserves; ?></div>
                    <div class="dash-label">Pending Reservations</div>
                </div>
                <div class="dash-icon"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card dash-card bg-secondary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="dash-number">₹<?php echo number_format((float)$pendingFines, 2); ?></div>
                    <div class="dash-label">Pending Fine</div>
                </div>
                <div class="dash-icon"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Recently issued books -->
<div class="card mt-4">
    <div class="card-header">Recently Issued Books</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($recent)): ?>
                            <tr>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars($r['member_code']); ?></span>
                                    <?php echo htmlspecialchars($r['member_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($r['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($r['due_date'])); ?></td>
                                <td>
                                    <?php
                                    $status = $r['status'];
                                    $badge  = match ($status) {
                                        'Issued'  => 'bg-primary',
                                        'Overdue' => 'bg-danger',
                                        'Returned'=> 'bg-success',
                                        default   => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No issued books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
