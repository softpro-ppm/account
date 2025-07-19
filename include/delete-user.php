<?php
include '../inc/auth.php'; // Include the database connection file
// Database connection
include '../inc/config.php'; // Include the database connection file

// Check if the user ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the input to prevent SQL injection

    // Delete the user from the database
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to the users page with a success message
        header("Location: ../users.php?message=User deleted successfully");
        exit();
    } else {
        // Redirect back to the users page with an error message
        header("Location: ../users.php?error=Failed to delete user");
        exit();
    }
} else {
    // Redirect back to the users page if no ID is provided
    header("Location: ../users.php?error=No user ID provided");
    exit();
}

$conn->close();
?>