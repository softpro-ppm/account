<?php
include 'inc/auth.php';
include 'inc/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['category_name'])) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

$category_name = trim($data['category_name']);

// Insert new category
$stmt = $conn->prepare("INSERT INTO income_categories (category_name) VALUES (?)");
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'id' => $stmt->insert_id,
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