<?php
include 'inc/config.php';

header('Content-Type: application/json');

if (!isset($_GET['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = intval($_GET['category_id']);

// Check if the category exists
$cat_check = $conn->prepare("SELECT id FROM expenditure_categories WHERE id = ?");
$cat_check->bind_param("i", $category_id);
$cat_check->execute();
$cat_result = $cat_check->get_result();

if ($cat_result->num_rows === 0) {
    // Category doesn't exist
    echo json_encode([]);
    $cat_check->close();
    $conn->close();
    exit;
}
$cat_check->close();

// Category exists, get subcategories
$stmt = $conn->prepare("SELECT id, subcategory_name FROM expenditure_subcategories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = [
        'id' => $row['id'],
        'subcategory_name' => $row['subcategory_name']
    ];
}

echo json_encode($subcategories);

$stmt->close();
$conn->close();
?> 