<?php
include 'inc/auth.php'; // Include the authentication file
include 'inc/config.php'; // Include the database connection file

header('Content-Type: application/json');

if (!isset($_GET['category_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = intval($_GET['category_id']);

// Fetch subcategories for the given category
$stmt = $conn->prepare("SELECT id, subcategory_name FROM income_subcategories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

echo json_encode($subcategories); 