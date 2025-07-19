<?php
include 'inc/config.php';

header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['category_name']) || empty($data['category_name'])) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

$category_name = trim($data['category_name']);

// Check if category already exists
$check_stmt = $conn->prepare("SELECT id FROM expenditure_categories WHERE category_name = ?");
$check_stmt->bind_param("s", $category_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Category already exists']);
    $check_stmt->close(); // Close the check statement
    $conn->close();       // Close the connection
    exit;
}
$check_stmt->close(); // Close the check statement here too

// Insert new category
$stmt = $conn->prepare("INSERT INTO expenditure_categories (category_name) VALUES (?)");
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'id' => $conn->insert_id,
        'message' => 'Category added successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding category: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close(); 