<?php
// =====================================================
// config/db.php
// Connects PHP to the MySQL database (library_management)
// using MySQLi.
// =====================================================

$host     = "localhost";
$username = "root";
$password = "";
$database = "library_management";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
