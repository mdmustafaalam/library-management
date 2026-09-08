<?php
// =====================================================
// config/helpers.php
// Small shared helper functions used across the project.
// =====================================================

/**
 * Generate the next member code (e.g. MEM001, MEM002 ...).
 */
function generateMemberCode($conn)
{
    $result = mysqli_query($conn, "SELECT member_code FROM members ORDER BY id DESC LIMIT 1");
    $row    = mysqli_fetch_assoc($result);

    if (!$row) {
        return "MEM001";
    }

    $lastNumber = (int)substr($row['member_code'], 3);
    $nextNumber = $lastNumber + 1;
    return "MEM" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}

/**
 * Generate the next book code (e.g. BOOK001, BOOK002 ...).
 */
function generateBookCode($conn)
{
    $result = mysqli_query($conn, "SELECT book_code FROM books ORDER BY id DESC LIMIT 1");
    $row    = mysqli_fetch_assoc($result);

    if (!$row) {
        return "BOOK001";
    }

    $lastNumber = (int)substr($row['book_code'], 4);
    $nextNumber = $lastNumber + 1;
    return "BOOK" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}

/**
 * Show a Bootstrap alert message (success / danger).
 */
function flash($message, $type = 'success')
{
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
        . htmlspecialchars($message)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}
