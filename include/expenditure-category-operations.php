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
        // Note: Add/Edit category/subcategory actions can be added here if needed later
        // For now, we only need get_subcategories for the edit form
        
        case 'get_expenditure_subcategories': // Use a distinct action name
            $category_id = intval($_POST['category_id']);
            
            // Log for debugging
            error_log("Fetching expenditure subcategories for category ID: " . $category_id);
            
            $stmt = $conn->prepare("SELECT id, subcategory_name FROM expenditure_subcategories WHERE category_id = ?");
            if (!$stmt) {
                 echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
                 exit;
            }
            $stmt->bind_param("i", $category_id);
            
            if (!$stmt->execute()) {
                 echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
                 exit;
            }
            
            $result = $stmt->get_result();
            $subcategories = [];
            while ($row = $result->fetch_assoc()) {
                $subcategories[] = $row;
            }
            $stmt->close();
            
            error_log("Found " . count($subcategories) . " expenditure subcategories");
            
            echo json_encode([
                'status' => 'success',
                'subcategories' => $subcategories
            ]);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid expenditure action: ' . $action
            ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method for expenditure categories'
    ]);
}

$conn->close();
?> 