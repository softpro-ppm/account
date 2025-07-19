<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'inc/config.php';

// Check database connection
echo "<h2>Database Connection Test</h2>";
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
} else {
    echo "Database connection successful<br>";
}

// Check income_categories table
echo "<h2>Income Categories</h2>";
$categories_query = "SELECT id, category_name FROM income_categories";
$categories_result = $conn->query($categories_query);

if (!$categories_result) {
    echo "Error: " . $conn->error;
} else {
    if ($categories_result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Category Name</th></tr>";
        while ($row = $categories_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No categories found.";
    }
}

// Check income_subcategories table
echo "<h2>Income Subcategories</h2>";
$subcategories_query = "SELECT s.id, s.category_id, s.subcategory_name, c.category_name 
                        FROM income_subcategories s
                        JOIN income_categories c ON s.category_id = c.id";
$subcategories_result = $conn->query($subcategories_query);

if (!$subcategories_result) {
    echo "Error: " . $conn->error;
} else {
    if ($subcategories_result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Category ID</th><th>Category Name</th><th>Subcategory Name</th></tr>";
        while ($row = $subcategories_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['category_id'] . "</td>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "<td>" . $row['subcategory_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No subcategories found.";
    }
}

// Test AJAX endpoint
echo "<h2>AJAX Endpoint Test</h2>";
echo "<script src='https://code.jquery.com/jquery-3.7.0.js'></script>";
echo "<script>
    function testSubcategoryFetch(categoryId) {
        $.ajax({
            url: 'include/category-operations.php',
            method: 'POST',
            data: {
                action: 'get_subcategories',
                category_id: categoryId
            },
            success: function(response) {
                $('#ajaxResult').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                $('#ajaxResult').html('Error: ' + error);
            }
        });
    }
</script>";

echo "<select id='testCategory' onchange='testSubcategoryFetch(this.value)'>";
echo "<option value=''>Select a category</option>";

// Reset pointer to beginning of result set
$categories_result->data_seek(0);
while ($row = $categories_result->fetch_assoc()) {
    echo "<option value='" . $row['id'] . "'>" . $row['category_name'] . "</option>";
}
echo "</select>";
echo "<div id='ajaxResult'></div>";

$conn->close();
?> 