<?php
// =====================================================
// index.php
// Landing page - redirects to login or dashboard.
// =====================================================

session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
} else {
    header("Location: login.php");
}
exit;
