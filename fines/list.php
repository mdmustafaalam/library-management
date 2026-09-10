<?php
// =====================================================
// fines/list.php
// Lists all fine records with status filter.
// Uses JOIN across fines, members, transactions, books.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$filter = $_GET['status'] ?? 'All';
$allowed = ['All', 'Pending', 'Partial', 'Paid'];

if (!in_array($filter, $allowed, true)) {
    $filter = 'All';
}

$sql = "SELECT f.*, m.member_code, m.name AS member_name,
               t.due_date, t.return_date, b.title
        FROM fines f
        INNER JOIN members m ON f.member_id = m.id
        INNER JOIN transactions t ON f.transaction_id = t.id
        INNER JOIN books b ON t.book_id = b.id";

$where = [];
if (!$isAdmin) {
    $memberId = (int)$_SESSION['user_id'];
    $where[] = "f.member_id = $memberId";
}
if ($filter !== 'All') {
    $where[] = "f.status = '" . $filter . "'";
}
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY f.id DESC";

$result = mysqli_query($conn, $sql);

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<h1 class="page-title">Fines</h1>

<!-- Filter tabs -->
<ul class="nav nav-pills mb-3 filter-pills">
    <?php foreach ($allowed as $st): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === $st ? 'active' : ''; ?>" href="list.php?status=<?php echo $st; ?>">
                <?php echo $st; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Overdue Days</th>
                        <th>Fine (₹)</th>
                        <th>Paid (₹)</th>
                        <th>Balance (₹)</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($f = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $overdueDays = (int)((strtotime($f['return_date']) - strtotime($f['due_date'])) / 86400);
                            $balance = (float)$f['amount'] - (float)$f['paid_amount'];
                            $badge = match ($f['status']) {
                                'Pending' => 'bg-warning text-dark',
                                'Partial' => 'bg-info',
                                'Paid'    => 'bg-success',
                                default   => 'bg-secondary'
                            };
                            ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars($f['member_code']); ?></span>
                                    <?php echo htmlspecialchars($f['member_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($f['title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($f['due_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($f['return_date'])); ?></td>
                                <td><?php echo $overdueDays; ?> day(s)</td>
                                <td>₹<?php echo number_format((float)$f['amount'], 2); ?></td>
                                <td>₹<?php echo number_format((float)$f['paid_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($balance, 2); ?></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $f['status']; ?></span></td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <?php if ($f['status'] !== 'Paid'): ?>
                                        <a href="payment.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-cash me-1"></i> Pay
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo $isAdmin ? 11 : 10; ?>" class="text-center text-muted py-4">No fines found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
