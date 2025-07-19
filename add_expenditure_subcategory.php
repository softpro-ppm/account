<?php
include 'inc/config.php';

header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['category_id']) || !isset($data['subcategory_name']) || empty($data['subcategory_name'])) {
    echo json_encode(['success' => false, 'message' => 'Category ID and subcategory name are required']);
    exit;
}

$category_id = intval($data['category_id']);
$subcategory_name = trim($data['subcategory_name']);

// Check if subcategory already exists for this category
$check_stmt = $conn->prepare("SELECT id FROM expenditure_subcategories WHERE category_id = ? AND subcategory_name = ?");
$check_stmt->bind_param("is", $category_id, $subcategory_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Subcategory already exists for this category']);
    $check_stmt->close(); // Close check statement
    $conn->close();       // Close connection
    exit;
}
$check_stmt->close(); // Close check statement here too

// Insert new subcategory
$stmt = $conn->prepare("INSERT INTO expenditure_subcategories (category_id, subcategory_name) VALUES (?, ?)");
$stmt->bind_param("is", $category_id, $subcategory_name);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'id' => $conn->insert_id,
        'message' => 'Subcategory added successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding subcategory: ' . $stmt->error
    ]);
}

$stmt->close(); // Close insert statement
$conn->close(); // Close connection
?> 