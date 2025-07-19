<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Pages that managers cannot access
$restricted_pages = ['dashboard.php'];

// Check if user is manager and trying to access restricted pages
if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'manager' && in_array($current_page, $restricted_pages)) {
    // Redirect to income page without error message
    header("Location: income.php");
    exit();
}

// Pages restricted to admin only
$admin_only_pages = ['users.php', 'add-user.php', 'edit-user.php'];

// Check if non-admin user is trying to access admin-only pages
if (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'admin' && in_array($current_page, $admin_only_pages)) {
    // Redirect to dashboard without error message
    header("Location: dashboard.php");
    exit();
}
?>