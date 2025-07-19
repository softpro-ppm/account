<?php
// Database connection
$host = "localhost";
$username = "u820431346_new_account";
$password = "otRkXMf]5;Ny";
$database = "u820431346_new_account";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>