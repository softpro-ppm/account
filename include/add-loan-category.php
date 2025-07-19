<?php
include '../inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category']);
    
    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name cannot be empty'
        ]);
        exit;
    }

    // Check if category already exists
    $check_sql = "SELECT id FROM loan_categories WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Category already exists'
        ]);
        exit;
    }
    
    // Insert new category
    $sql = "INSERT INTO loan_categories (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully',
            'id' => $new_id,
            'name' => $name
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding category: ' . $conn->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 