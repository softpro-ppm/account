<?php
include '../inc/config.php';

header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_category':
            $category_name = trim($_POST['category_name']);
            $stmt = $conn->prepare("INSERT INTO income_categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $category_name);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'id' => $stmt->insert_id,
                    'message' => 'Category added successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to add category: ' . $conn->error
                ]);
            }
            break;
            
        case 'add_subcategory':
            $category_id = intval($_POST['category_id']);
            $subcategory_name = trim($_POST['subcategory_name']);
            
            $stmt = $conn->prepare("INSERT INTO income_subcategories (category_id, subcategory_name) VALUES (?, ?)");
            $stmt->bind_param("is", $category_id, $subcategory_name);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'id' => $stmt->insert_id,
                    'message' => 'Subcategory added successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to add subcategory: ' . $conn->error
                ]);
            }
            break;
            
        case 'get_subcategories':
            $category_id = intval($_POST['category_id']);
            
            // Log the category ID for debugging
            error_log("Fetching subcategories for category ID: " . $category_id);
            
            $stmt = $conn->prepare("SELECT id, subcategory_name FROM income_subcategories WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $subcategories = [];
            while ($row = $result->fetch_assoc()) {
                $subcategories[] = $row;
            }
            
            error_log("Found " . count($subcategories) . " subcategories");
            
            echo json_encode([
                'status' => 'success',
                'subcategories' => $subcategories
            ]);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action: ' . $action
            ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 