<?php
// =====================================================
// member/dashboard.php
// Member dashboard - shows borrowed books, fines, etc.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';

$memberId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Member';

// Fetch member stats
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM transactions WHERE member_id = ? AND status = 'Issued'");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$issued = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM transactions WHERE member_id = ? AND status = 'Overdue'");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$overdue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(amount - paid_amount), 0) as total FROM fines WHERE member_id = ? AND status != 'Paid'");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$fines = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM reservations WHERE member_id = ? AND status = 'Pending'");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$reservations = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

// Fetch recent transactions
$stmt = mysqli_prepare($conn, "SELECT t.*, b.title, b.book_code FROM transactions t JOIN books b ON t.book_id = b.id WHERE t.member_id = ? ORDER BY t.created_at DESC LIMIT 5");
mysqli_stmt_bind_param($stmt, "i", $memberId);
mysqli_stmt_execute($stmt);
$recentTransactions = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($userName); ?></h1>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card dash-card bg-primary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dash-icon"><i class="bi bi-book"></i></div>
                <div>
                    <div class="dash-number"><?php echo $issued; ?></div>
                    <div class="dash-label">Books Issued</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dash-card bg-danger">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dash-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="dash-number"><?php echo $overdue; ?></div>
                    <div class="dash-label">Overdue</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dash-card bg-warning">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dash-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="dash-number">₹<?php echo number_format($fines, 2); ?></div>
                    <div class="dash-label">Pending Fines</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dash-card bg-info">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dash-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="dash-number"><?php echo $reservations; ?></div>
                    <div class="dash-label">Reservations</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2"></i>Recent Transactions</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Code</th>
                        <th>Title</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recentTransactions) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($recentTransactions)): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($t['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($t['issue_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($t['due_date'])); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($t['status']) {
                                        'Issued'   => 'bg-primary',
                                        'Returned' => 'bg-success',
                                        'Overdue'  => 'bg-danger',
                                        default    => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $t['status']; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No transactions yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
