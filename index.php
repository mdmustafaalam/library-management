<?php
// =====================================================
// index.php
// Landing page - redirects to login or dashboard.
// =====================================================

session_start();

if (isset($_SESSION['admin_id']) || (isset($_SESSION['user_type']) && isset($_SESSION['user_id']))) {
    if (($_SESSION['user_type'] ?? '') === 'member') {
        header("Location: member/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
} else {
    header("Location: login.php");
}
exit;
