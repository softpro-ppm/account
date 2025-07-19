<?php
include 'inc/auth.php'; // Include the authentication file to check user login status
// Database connection
include 'inc/config.php'; // Include the database connection file

$expenditure = null;
$categories = [];
$subcategories = [];
$expenditure_category_id = null;
$expenditure_subcategory_id = null;

// Fetch all expenditure categories
$categories_query = "SELECT id, category_name FROM expenditure_categories"; // Use expenditure table
$categories_result = $conn->query($categories_query);
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch expenditure details for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    
    // Fetch expenditure record and its related category/subcategory IDs
    $sql = "SELECT e.*, c.id as category_id, sc.id as subcategory_id 
            FROM expenditures e 
            LEFT JOIN expenditure_categories c ON e.category = c.category_name 
            LEFT JOIN expenditure_subcategories sc ON e.subcategory = sc.subcategory_name AND sc.category_id = c.id 
            WHERE e.id = ?"; // Use expenditure tables
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $expenditure = $result->fetch_assoc();

    if (!$expenditure) {
        header("Location: expenditure.php?error=Expenditure record not found");
        exit();
    }

    // Convert the date to dd-mm-yyyy format for display
    $expenditure['date'] = date('d-m-Y', strtotime($expenditure['date']));
    $expenditure_category_id = $expenditure['category_id'];
    $expenditure_subcategory_id = $expenditure['subcategory_id'];

    // Fetch subcategories for the current expenditure's category
    if ($expenditure_category_id) {
        $subcategories_query = "SELECT id, subcategory_name FROM expenditure_subcategories WHERE category_id = ?"; // Use expenditure table
        $subcat_stmt = $conn->prepare($subcategories_query);
        $subcat_stmt->bind_param("i", $expenditure_category_id);
        $subcat_stmt->execute();
        $subcategories_result = $subcat_stmt->get_result();
        if ($subcategories_result) {
            while ($row = $subcategories_result->fetch_assoc()) {
                $subcategories[] = $row;
            }
        }
        $subcat_stmt->close();
    }
    $stmt->close();
} else {
    header("Location: expenditure.php?error=No expenditure ID provided");
    exit();
}

// Handle form submission for updating expenditure
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert date from dd-mm-yyyy to yyyy-mm-dd for database storage
    $date = DateTime::createFromFormat('d-m-Y', $_POST['date'])->format('Y-m-d');
    $name = ucfirst(trim($_POST['name']));
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    
    // Get category and subcategory names from IDs
    $category_id = intval($_POST['category']);
    $subcategory_id = intval($_POST['subcategory']);
    
    // Get category name
    $cat_stmt = $conn->prepare("SELECT category_name FROM expenditure_categories WHERE id = ?"); // Use expenditure table
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $category_result = $cat_stmt->get_result();
    $category = $category_result->fetch_assoc()['category_name'];
    $cat_stmt->close();
    
    // Get subcategory name
    $subcat_stmt = $conn->prepare("SELECT subcategory_name FROM expenditure_subcategories WHERE id = ?"); // Use expenditure table
    $subcat_stmt->bind_param("i", $subcategory_id);
    $subcat_stmt->execute();
    $subcategory_result = $subcat_stmt->get_result();
    $subcategory = $subcategory_result->fetch_assoc()['subcategory_name'];
    $subcat_stmt->close();

    $amount = floatval($_POST['amount']);
    $paid = floatval($_POST['paid']);
    $balance = $amount - $paid;

    // Update expenditure record in the database
    $sql = "UPDATE expenditures SET date = ?, name = ?, phone = ?, description = ?, category = ?, subcategory = ?, amount = ?, paid = ?, balance = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssdddi", $date, $name, $phone, $description, $category, $subcategory, $amount, $paid, $balance, $id);

    if ($stmt->execute()) {
        header("Location: expenditure.php?message=Expenditure record updated successfully");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close(); // Close connection at the end of PHP block
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Expenditure</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #343a40;
    }
    .sidebar .nav-link {
      color: #ffffff;
    }
    .sidebar .nav-link.active {
      background-color: #495057;
    }
    .main-content {
      margin-left: 250px;
      padding: 2rem;
    }
    .form-container {
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content w-100">
    <!-- Top Navbar -->
    <?php include 'topbar.php'; // Add topbar include ?>

    <!-- <h3 class="mb-4">Edit Expenditure</h3> -->
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="" method="POST" class="form-container">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="date" class="form-label">Date</label>
          <input type="text" class="form-control date-picker" id="date" name="date" value="<?php echo htmlspecialchars($expenditure['date']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($expenditure['name']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="phone" class="form-label">Phone</label>
          <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($expenditure['phone']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="description" class="form-label">Description</label>
          <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($expenditure['description']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="category" class="form-label">Category</label>
          <select id="category" name="category" class="form-select" required>
             <option value="" disabled>Choose...</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($cat['id'] == $expenditure_category_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="subcategory" class="form-label">Sub-category</label>
          <select id="subcategory" name="subcategory" class="form-select" required>
            <option value="" disabled>Choose category first</option>
             <?php foreach ($subcategories as $subcat): ?>
              <option value="<?php echo htmlspecialchars($subcat['id']); ?>" <?php echo ($subcat['id'] == $expenditure_subcategory_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($subcat['subcategory_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="amount" class="form-label">Total Amount (₹)</label>
          <input type="number" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($expenditure['amount']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="paid" class="form-label">Paid Amount (₹)</label>
          <input type="number" class="form-control" id="paid" name="paid" value="<?php echo htmlspecialchars($expenditure['paid']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="balance" class="form-label">Balance Amount (₹)</label>
          <input type="number" class="form-control" id="balance" name="balance" value="<?php echo htmlspecialchars($expenditure['balance']); ?>" readonly>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary">Update Expenditure</button>
        <a href="expenditure.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/responsive.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  $(document).ready(function() {
    // Initialize Flatpickr for date picker
    flatpickr('.date-picker', {
      dateFormat: "d-m-Y"
    });

    // Update balance amount dynamically
    $('#amount, #paid').on('input', function() { // Use #paid for expenditure
        const total = parseFloat($('#amount').val()) || 0;
        const paid = parseFloat($('#paid').val()) || 0; // Use #paid
        $('#balance').val(total - paid); // Correct calculation
    });

    // Pre-populate balance on load
    $('#amount').trigger('input'); 

    // Handle category change for expenditure
    $('#category').on('change', function() {
        const categoryId = $(this).val();
        const $subcategory = $('#subcategory');
        
        if (categoryId) {
            $subcategory.empty().append('<option value="" selected disabled>Loading...</option>').prop('disabled', true);
            
            $.ajax({
                url: 'include/expenditure-category-operations.php', // Point to the new handler
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_expenditure_subcategories', // Use the new action name
                    category_id: categoryId
                },
                success: function(response) {
                    $subcategory.prop('disabled', false).empty();
                    if (response.status === 'success' && response.subcategories.length > 0) {
                        $subcategory.append('<option value="" selected disabled>Choose subcategory...</option>');
                        response.subcategories.forEach(function(subcat) {
                            $subcategory.append(`<option value="${subcat.id}">${subcat.subcategory_name}</option>`);
                        });
                        // Add logic here if you want to re-select the original subcategory after category change
                        // const originalSubcategoryId = '<?php echo $expenditure_subcategory_id; ?>'; 
                        // ... (similar logic as in edit-income)
                    } else {
                        $subcategory.append('<option value="" selected disabled>No subcategories available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    $subcategory.prop('disabled', false).empty().append('<option value="" selected disabled>Error loading</option>');
                    console.error('AJAX error:', status, error, xhr.responseText);
                }
            });
        } else {
            $subcategory.empty().append('<option value="" selected disabled>Choose category first</option>').prop('disabled', true);
        }
    });
  });
</script>
</body>
</html>