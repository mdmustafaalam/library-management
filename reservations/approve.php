<?php
// =====================================================
// reservations/approve.php
// Handle Approve / Reject / Complete actions on a reservation.
// URL: approve.php?id=5&action=approve
//
// Rules:
//   - Approve only if book.available_copies > 0
//   - Approving does NOT auto-decrease copies (a reservation
//     is not an actual issue until converted into a transaction)
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$rid    = (int)$_GET['id'];
$action = $_GET['action'] ?? '';

// --- Fetch the reservation with book info ---
$stmt = mysqli_prepare($conn,
    "SELECT r.*, b.available_copies
     FROM reservations r
     INNER JOIN books b ON r.book_id = b.id
     WHERE r.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $rid);
mysqli_stmt_execute($stmt);
$res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$res) {
    $_SESSION['success'] = "Reservation not found.";
    header("Location: list.php");
    exit;
}

switch ($action) {
    case 'approve':
        // Cannot approve a non-pending reservation
        if ($res['status'] !== 'Pending') {
            $_SESSION['success'] = "Only pending reservations can be approved.";
            header("Location: list.php");
            exit;
        }
        // Must have available copies
        if ((int)$res['available_copies'] <= 0) {
            $_SESSION['success'] = "Cannot approve: no copies available for this book.";
            header("Location: list.php");
            exit;
        }
        $stmt = mysqli_prepare($conn, "UPDATE reservations SET status = 'Approved' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $rid);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Reservation approved.";
        break;

    case 'reject':
        if ($res['status'] !== 'Pending') {
            $_SESSION['success'] = "Only pending reservations can be rejected.";
            header("Location: list.php");
            exit;
        }
        $stmt = mysqli_prepare($conn, "UPDATE reservations SET status = 'Rejected' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $rid);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Reservation rejected.";
        break;

    case 'complete':
        if ($res['status'] !== 'Approved') {
            $_SESSION['success'] = "Only approved reservations can be completed.";
            header("Location: list.php");
            exit;
        }
        $stmt = mysqli_prepare($conn, "UPDATE reservations SET status = 'Completed' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $rid);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Reservation marked as completed.";
        break;

    default:
        $_SESSION['success'] = "Invalid action.";
        break;
}

header("Location: list.php");
exit;
