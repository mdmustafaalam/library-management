<?php
// =====================================================
// reports/fine-report.php
// Shows fine totals (generated, collected, outstanding)
// plus detailed fine records.
// Optional filters: start date, end date, status.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

// --- Read optional filters ---
$start   = $_GET['start'] ?? '';
$end     = $_GET['end'] ?? '';
$status  = $_GET['status'] ?? '';
$where   = [];
$params  = [];
$types   = '';

if ($start !== '') { $where[] = "f.created_at >= ?"; $params[] = $start . ' 00:00:00'; $types .= 's'; }
if ($end   !== '') { $where[] = "f.created_at <= ?"; $params[] = $end . ' 23:59:59'; $types .= 's'; }
if ($status !== '' && in_array($status, ['Pending', 'Partial', 'Paid'], true)) {
    $where[] = "f.status = ?"; $params[] = $status; $types .= 's';
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// --- Totals ---
$totStmt = mysqli_prepare($conn,
    "SELECT COALESCE(SUM(amount),0) AS total_generated,
            COALESCE(SUM(paid_amount),0) AS total_collected
     FROM fines f" . $whereSql
);
if ($params) {
    mysqli_stmt_bind_param($totStmt, $types, ...$params);
}
mysqli_stmt_execute($totStmt);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($totStmt));

$totalGenerated = (float)$totals['total_generated'];
$totalCollected = (float)$totals['total_collected'];
$outstanding    = $totalGenerated - $totalCollected;

// --- Detailed records ---
$detStmt = mysqli_prepare($conn,
    "SELECT f.*, m.member_code, m.name AS member_name, b.title, b.book_code
     FROM fines f
     INNER JOIN members m ON f.member_id = m.id
     INNER JOIN transactions t ON f.transaction_id = t.id
     INNER JOIN books b ON t.book_id = b.id" . $whereSql . "
     ORDER BY f.id DESC"
);
if ($params) {
    mysqli_stmt_bind_param($detStmt, $types, ...$params);
}
mysqli_stmt_execute($detStmt);
$detail = mysqli_stmt_get_result($detStmt);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h1 class="page-title">Fine Report</h1>

<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dash-card bg-purple">
            <div class="card-body">
                <div class="dash-number">₹<?php echo number_format($totalGenerated, 2); ?></div>
                <div class="dash-label">Total Fine Generated</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dash-card bg-success">
            <div class="card-body">
                <div class="dash-number">₹<?php echo number_format($totalCollected, 2); ?></div>
                <div class="dash-label">Total Fine Collected</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dash-card bg-danger">
            <div class="card-body">
                <div class="dash-number">₹<?php echo number_format($outstanding, 2); ?></div>
                <div class="dash-label">Outstanding Fine</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="fine-report.php" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="date" class="form-control" name="start" value="<?php echo htmlspecialchars($start); ?>">
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" name="end" value="<?php echo htmlspecialchars($end); ?>">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Partial" <?php echo $status === 'Partial' ? 'selected' : ''; ?>>Partial</option>
            <option value="Paid" <?php echo $status === 'Paid' ? 'selected' : ''; ?>>Paid</option>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i> Apply Filter</button>
    </div>
</form>

<!-- Detailed records -->
<div class="card">
    <div class="card-header">Detailed Fine Records</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Fine (₹)</th>
                        <th>Paid (₹)</th>
                        <th>Balance (₹)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($detail) > 0): ?>
                        <?php $sn = 1; while ($f = mysqli_fetch_assoc($detail)): ?>
                            <?php
                            $bal = (float)$f['amount'] - (float)$f['paid_amount'];
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
                                <td>₹<?php echo number_format((float)$f['amount'], 2); ?></td>
                                <td>₹<?php echo number_format((float)$f['paid_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($bal, 2); ?></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $f['status']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No fine records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
