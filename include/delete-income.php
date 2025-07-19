<?php
include '../inc/auth.php'; // Include the database connection file
// Database connection
include '../inc/config.php'; // Include the database connection file

// Check if the income ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the input to prevent SQL injection

    // Delete the income record from the database
    $sql = "DELETE FROM income WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to the income page with a success message
        header("Location: ../income.php?message=Income record deleted successfully");
        exit();
    } else {
        // Redirect back to the income page with an error message
        header("Location: ../income.php?error=Failed to delete income record");
        exit();
    }
} else {
    // Redirect back to the income page if no ID is provided
    header("Location: ../income.php?error=No income ID provided");
    exit();
}

$conn->close();
?>