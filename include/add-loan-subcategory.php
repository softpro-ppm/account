<?php
include '../inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category']);
    $subcategory_name = trim($_POST['subcategory']);
    
    if (empty($category_name) || empty($subcategory_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Category and subcategory names cannot be empty'
        ]);
        exit;
    }

    // Get category ID
    $cat_sql = "SELECT id FROM loan_categories WHERE name = ?";
    $cat_stmt = $conn->prepare($cat_sql);
    $cat_stmt->bind_param("s", $category_name);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    
    if ($cat_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Category not found'
        ]);
        exit;
    }
    
    $category_id = $cat_result->fetch_assoc()['id'];

    // Check if subcategory already exists for this category
    $check_sql = "SELECT id FROM loan_subcategories WHERE category_id = ? AND name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $category_id, $subcategory_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Subcategory already exists for this category'
        ]);
        exit;
    }
    
    // Insert new subcategory
    $sql = "INSERT INTO loan_subcategories (category_id, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $category_id, $subcategory_name);
    
    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Subcategory added successfully',
            'id' => $new_id,
            'name' => $subcategory_name
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding subcategory: ' . $conn->error
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