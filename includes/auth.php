<?php
// =====================================================
// includes/auth.php
// Protects pages - checks for admin OR member login.
// - Starts a session
// - Checks $_SESSION['admin_id'] (password login)
//   or $_SESSION['user_type'] + $_SESSION['user_id'] (OTP login)
// - Redirects to login.php if not logged in
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['admin_id']) || (isset($_SESSION['user_type']) && isset($_SESSION['user_id']));

if (!$isLoggedIn) {
    header("Location: " . getBaseUrl() . "login.php");
    exit;
}

// Determine role
$userType = $_SESSION['user_type'] ?? 'admin';
$isAdmin  = ($userType === 'admin');

// Helper: restrict page to admin only
function requireAdmin()
{
    global $isAdmin;
    if (!$isAdmin) {
        header("Location: " . getBaseUrl() . "member/dashboard.php");
        exit;
    }
}

// Helper to build the correct base URL for root pages
function getBaseUrl()
{
    // Determine how many folders deep the current page is
    $dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($dir === '/' || $dir === '\\') {
        return $dir;
    }
    // Count path segments to build "../" references
    $segments = explode('/', trim($dir, '/'));
    $prefix   = str_repeat('../', count($segments));
    return $prefix;
}
