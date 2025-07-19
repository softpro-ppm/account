<?php
include '../inc/auth.php';
include '../inc/config.php';

// Check if user is manager and redirect if true
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'manager') {
    header("Location: ../dashboard.php?error=You do not have permission to access this page");
    exit();
}

// Check if loan ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../loan.php?error=Invalid loan ID");
    exit();
}

$loan_id = $_GET['id'];

// Check if the loan exists
$check_sql = "SELECT id FROM loans WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $loan_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    header("Location: ../loan.php?error=Loan not found");
    exit();
}
$check_stmt->close();

// Delete the loan
$delete_sql = "DELETE FROM loans WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $loan_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    header("Location: ../loan.php?message=Loan deleted successfully");
    exit();
} else {
    $delete_stmt->close();
    header("Location: ../loan.php?error=Error deleting loan: " . $conn->error);
    exit();
}

$conn->close();
?> 