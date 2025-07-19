<?php
include '../inc/auth.php'; // Include the database connection file
// Database connection
include '../inc/config.php'; // Include the database connection file

// Check if the expenditure ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the input to prevent SQL injection

    // Delete the expenditure record from the database
    $sql = "DELETE FROM expenditures WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to the expenditure page with a success message
        header("Location: ../expenditure.php?message=Expenditure record deleted successfully");
        exit();
    } else {
        // Redirect back to the expenditure page with an error message
        header("Location: ../expenditure.php?error=Failed to delete expenditure record");
        exit();
    }
} else {
    // Redirect back to the expenditure page if no ID is provided
    header("Location: ../expenditure.php?error=No expenditure ID provided");
    exit();
}

$conn->close();
?>