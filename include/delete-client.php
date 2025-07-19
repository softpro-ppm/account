<?php
include '../inc/auth.php'; // Include the database connection file
// Database connection
include '../inc/config.php'; // Include the database connection file

// Check if the client ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the input to prevent SQL injection

    // Delete the client record from the database
    $sql = "DELETE FROM clients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to the client page with a success message
        header("Location: ../client.php?message=Client deleted successfully");
        exit();
    } else {
        // Redirect back to the client page with an error message
        header("Location: ../client.php?error=Failed to delete client");
        exit();
    }
} else {
    // Redirect back to the client page if no ID is provided
    header("Location: ../client.php?error=No client ID provided");
    exit();
}

$conn->close();
?>