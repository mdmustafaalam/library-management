<?php
// =====================================================
// includes/auth.php
// Protects admin pages.
// - Starts a session
// - Checks if the admin is logged in ($_SESSION['admin_id'])
// - Redirects to login.php if not logged in
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . getBaseUrl() . "login.php");
    exit;
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
