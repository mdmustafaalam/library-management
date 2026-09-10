<?php
// =====================================================
// reservations/list.php
// List all reservations with JOIN across members + books.
// Provides Approve / Reject / Complete actions.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$filter = $_GET['status'] ?? 'All';
$allowed = ['All', 'Pending', 'Approved', 'Rejected', 'Completed'];
if (!in_array($filter, $allowed, true)) {
    $filter = 'All';
}

$sql = "SELECT r.*, m.member_code, m.name AS member_name,
               b.book_code, b.title, b.available_copies
        FROM reservations r
        INNER JOIN members m ON r.member_id = m.id
        INNER JOIN books b ON r.book_id = b.id";

$where = [];
if (!$isAdmin) {
    $memberId = (int)$_SESSION['user_id'];
    $where[] = "r.member_id = $memberId";
}
if ($filter !== 'All') {
    $where[] = "r.status = '" . $filter . "'";
}
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY r.id DESC";

$result = mysqli_query($conn, $sql);

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title mb-3">Reservations</h1>
    <?php if ($isAdmin): ?>
    <a href="add.php" class="btn btn-primary mb-3"><i class="bi bi-calendar-plus me-1"></i> Add Reservation</a>
    <?php endif; ?>
</div>

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
                        <th>Member Code</th>
                        <th>Member Name</th>
                        <th>Book Code</th>
                        <th>Book Name</th>
                        <th>Available</th>
                        <th>Reservation Date</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($r = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $badge = match ($r['status']) {
                                'Pending'   => 'bg-warning text-dark',
                                'Approved'  => 'bg-info',
                                'Rejected'  => 'bg-danger',
                                'Completed' => 'bg-success',
                                default     => 'bg-secondary'
                            };
                            $canApprove = ($r['status'] === 'Pending' && (int)$r['available_copies'] > 0);
                            ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><code><?php echo htmlspecialchars($r['member_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($r['member_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($r['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo (int)$r['available_copies']; ?></td>
                                <td><?php echo date('d M Y', strtotime($r['reservation_date'])); ?></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $r['status']; ?></span></td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <?php if ($canApprove): ?>
                                        <a href="approve.php?id=<?php echo $r['id']; ?>&action=approve"
                                           class="btn btn-sm btn-outline-success">Approve</a>
                                    <?php endif; ?>
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <a href="approve.php?id=<?php echo $r['id']; ?>&action=reject"
                                           class="btn btn-sm btn-outline-danger" data-confirm="Reject this reservation?">Reject</a>
                                    <?php endif; ?>
                                    <?php if ($r['status'] === 'Approved'): ?>
                                        <a href="approve.php?id=<?php echo $r['id']; ?>&action=complete"
                                           class="btn btn-sm btn-outline-primary" data-confirm="Mark as completed?">Complete</a>
                                    <?php elseif (!in_array($r['status'], ['Pending', 'Approved'])): ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No reservations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
