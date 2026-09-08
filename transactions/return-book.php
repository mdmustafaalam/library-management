<?php
// =====================================================
// transactions/return-book.php
// Return a book (by transaction id).
// URL: return-book.php?id=15
//
// On return:
//   1. Set return_date = today
//   2. Set status = 'Returned'
//   3. Increment books.available_copies (+1)
//   4. If return_date > due_date, calculate fine
//      (₹10 per overdue day) and create a fine record
// Uses a DATABASE TRANSACTION.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';
require_once '../config/fine-config.php';

// $finePerDay is defined in config/fine-config.php (₹10 per overdue day)

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: issued-books.php");
    exit;
}
$tid = (int)$_GET['id'];

// --- Retrieve the transaction ---
$stmt = mysqli_prepare($conn, "SELECT * FROM transactions WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $tid);
mysqli_stmt_execute($stmt);
$txn = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$txn) {
    $_SESSION['success'] = "Transaction not found.";
    header("Location: issued-books.php");
    exit;
}

// Prevent returning an already-returned book
if ($txn['status'] === 'Returned') {
    $_SESSION['success'] = "This book has already been returned.";
    header("Location: issued-books.php");
    exit;
}

$errors = [];

mysqli_begin_transaction($conn);

try {
    $returnDate = date('Y-m-d');

    // Step 1 & 2: Update transaction
    $stmt = mysqli_prepare($conn,
        "UPDATE transactions SET return_date = ?, status = 'Returned' WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $returnDate, $tid);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Unable to update transaction.'); }

    // Step 3: Increase available copies
    $stmt = mysqli_prepare($conn,
        "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $txn['book_id']);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Unable to update book copies.'); }

    // Step 4 & 5: Check overdue and create fine
    $dueDateStr = $txn['due_date'];
    $fineCreated = false;
    if ($returnDate > $dueDateStr) {
        $overdueDays = (int)((strtotime($returnDate) - strtotime($dueDateStr)) / 86400);
        $amount = $overdueDays * $finePerDay;

        // Avoid duplicate fine for the same transaction
        $chk = mysqli_query($conn, "SELECT id FROM fines WHERE transaction_id = $tid");
        if (mysqli_num_rows($chk) === 0) {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO fines (transaction_id, member_id, amount, paid_amount, status)
                 VALUES (?, ?, ?, 0, 'Pending')"
            );
            mysqli_stmt_bind_param($stmt, "iid", $tid, $txn['member_id'], $amount);
            if (!mysqli_stmt_execute($stmt)) { throw new Exception('Unable to create fine record.'); }
            $fineCreated = true;
        }
    }

    mysqli_commit($conn);

    if ($fineCreated) {
        $_SESSION['success'] = "Book returned. Overdue fine of ₹" . number_format($amount, 2) . " was generated.";
    } else {
        $_SESSION['success'] = "Book returned successfully.";
    }
    header("Location: issued-books.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['success'] = $e->getMessage();
    header("Location: issued-books.php");
    exit;
}
