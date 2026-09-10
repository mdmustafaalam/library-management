<?php
// =====================================================
// includes/header.php
// Contains the <head>, Bootstrap CSS, custom CSS, top navbar.
// Included by every protected page AFTER auth.php.
// =====================================================

// All protected pages are exactly ONE folder deep, so links use ../ to go to root.
// The current folder name helps highlight the active nav item.
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
$userType = $_SESSION['user_type'] ?? 'admin';
$displayName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Admin';
$initials = strtoupper(substr($displayName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
