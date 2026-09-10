<?php
// =====================================================
// config/db.php
// Connects PHP to the MySQL database using MySQLi.
// =====================================================

require_once __DIR__ . '/env.php';

$conn = mysqli_connect(
    env('DB_HOST', 'localhost'),
    env('DB_USERNAME', 'root'),
    env('DB_PASSWORD', ''),
    env('DB_NAME', 'library_management')
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
