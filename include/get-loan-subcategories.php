<?php
include '../inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $category_name = trim($_POST['category']);
    
    // Get category ID first
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
    
    // Get subcategories for the selected category
    $sql = "SELECT id, name FROM loan_subcategories WHERE category_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '<option value="">Select Subcategory</option>';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subcategory = htmlspecialchars($row['name']);
            $html .= "<option value=\"{$subcategory}\">{$subcategory}</option>";
        }
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'html' => '<option value="">No subcategories found</option>'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

$conn->close();
?> 