<?php
include 'inc/auth.php';
include 'inc/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['category_id']) || !isset($data['subcategory_name'])) {
    echo json_encode(['success' => false, 'message' => 'Category ID and subcategory name are required']);
    exit;
}

$category_id = intval($data['category_id']);
$subcategory_name = trim($data['subcategory_name']);

// Verify category exists
$cat_check = $conn->prepare("SELECT id FROM income_categories WHERE id = ?");
$cat_check->bind_param("i", $category_id);
$cat_check->execute();
$result = $cat_check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

// Insert new subcategory
$stmt = $conn->prepare("INSERT INTO income_subcategories (category_id, subcategory_name) VALUES (?, ?)");
$stmt->bind_param("is", $category_id, $subcategory_name);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'id' => $stmt->insert_id,
        'message' => 'Subcategory added successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding subcategory: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close(); 