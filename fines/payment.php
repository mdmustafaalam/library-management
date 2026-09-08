<?php
// =====================================================
// fines/payment.php
// Record a fine payment.
// URL: payment.php?id=5
// Rules:
//   - payment must be > 0
//   - payment cannot exceed the remaining balance
//   - status updates: Pending / Partial / Paid
//   - payment_date set when fully paid
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$fid = (int)$_GET['id'];

// --- Fetch the fine record ---
$stmt = mysqli_prepare($conn,
    "SELECT f.*, m.member_code, m.name AS member_name, b.title
     FROM fines f
     INNER JOIN members m ON f.member_id = m.id
     INNER JOIN transactions t ON f.transaction_id = t.id
     INNER JOIN books b ON t.book_id = b.id
     WHERE f.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $fid);
mysqli_stmt_execute($stmt);
$fine = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$fine) {
    $_SESSION['success'] = "Fine record not found.";
    header("Location: list.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = (float)($_POST['amount'] ?? 0);

    $amount    = (float)$fine['amount'];
    $paid      = (float)$fine['paid_amount'];
    $balance   = $amount - $paid;

    if ($payment <= 0) {
        $errors[] = 'Payment amount must be greater than 0.';
    }
    if ($payment > $balance) {
        $errors[] = 'Payment cannot exceed the outstanding balance (₹' . number_format($balance, 2) . ').';
    }

    if (empty($errors)) {
        $newPaid = $paid + $payment;

        if ($newPaid >= $amount) {
            $newStatus = 'Paid';
            $payDate   = date('Y-m-d H:i:s');
        } elseif ($newPaid > 0) {
            $newStatus = 'Partial';
            $payDate   = null;
        } else {
            $newStatus = 'Pending';
            $payDate   = null;
        }

        $stmt = mysqli_prepare($conn,
            "UPDATE fines SET paid_amount = ?, status = ?, payment_date = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "dssi", $newPaid, $newStatus, $payDate, $fid);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Payment recorded successfully.";
            header("Location: list.php");
            exit;
        } else {
            $errors[] = 'Unable to record payment. Please try again.';
        }
    }
}

$amount  = (float)$fine['amount'];
$paid    = (float)$fine['paid_amount'];
$balance = $amount - $paid;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Fine Payment</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to Fines</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Member:</strong>
                <code><?php echo htmlspecialchars($fine['member_code']); ?></code>
                <?php echo htmlspecialchars($fine['member_name']); ?>
            </div>
            <div class="col-md-3"><strong>Book:</strong> <?php echo htmlspecialchars($fine['title']); ?></div>
            <div class="col-md-2"><strong>Fine Amount:</strong> ₹<?php echo number_format($amount, 2); ?></div>
            <div class="col-md-2"><strong>Paid:</strong> ₹<?php echo number_format($paid, 2); ?></div>
            <div class="col-md-2"><strong>Remaining:</strong> ₹<?php echo number_format($balance, 2); ?></div>
        </div>

        <form method="POST" action="payment.php?id=<?php echo $fid; ?>" novalidate>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="amount" class="form-label">Payment Amount (₹)</label>
                    <input type="number" step="0.01" min="0.01" max="<?php echo $balance; ?>" class="form-control"
                           id="amount" name="amount" required>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cash-stack me-1"></i> Record Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
