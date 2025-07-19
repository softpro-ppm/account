<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'inc/auth.php'; // Include the authentication file to check user session
include 'inc/config.php'; // Include the database connection file

// Fetch all categories
$categories_query = "SELECT id, category_name FROM income_categories";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Convert date from dd-mm-yyyy to yyyy-mm-dd for database storage
    $date = DateTime::createFromFormat('d-m-Y', $_POST['date']);
    if (!$date) {
        die("Invalid date format. Please use DD-MM-YYYY.");
    }
    $date = $date->format('Y-m-d');

    $name = ucfirst(trim($_POST['name']));
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    
    // Get category and subcategory names from their IDs
    $category_id = intval($_POST['category']);
    $subcategory_id = intval($_POST['subcategory']);
    
    // Get category name
    $cat_stmt = $conn->prepare("SELECT category_name FROM income_categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $category_result = $cat_stmt->get_result();
    $category = $category_result->fetch_assoc()['category_name'];
    
    // Get subcategory name
    $subcat_stmt = $conn->prepare("SELECT subcategory_name FROM income_subcategories WHERE id = ?");
    $subcat_stmt->bind_param("i", $subcategory_id);
    $subcat_stmt->execute();
    $subcategory_result = $subcat_stmt->get_result();
    $subcategory = $subcategory_result->fetch_assoc()['subcategory_name'];
    
    $amount = floatval($_POST['total_amount']);
    $received = floatval($_POST['received_amount']);
    $balance = $amount - $received;

    // Debugging: Check all variables
    // Uncomment the following lines to debug
    // var_dump($date, $name, $phone, $description, $category, $subcategory, $amount, $received, $balance);
    // exit;

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO income (date, name, phone, description, category, subcategory, amount, received, balance) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssddd", $date, $name, $phone, $description, $category, $subcategory, $amount, $received, $balance);

    if ($stmt->execute()) {
        // Redirect back to the income page with a success message
        header("Location: income.php?message=Income entry added successfully");
        exit();
    } else {
        // Display error message
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Income</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
      display: flex;
    }
    .sidebar {
      width: 250px;
      min-height: 100vh;
      background-color: #343a40;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1000;
    }
    .sidebar .nav-link {
      color: #ffffff;
      padding: 0.5rem 1rem;
      margin: 0.2rem 0;
    }
    .sidebar .nav-link:hover {
      background-color: #495057;
    }
    .sidebar .nav-link.active {
      background-color: #0d6efd;
    }
    .sidebar .nav-link i {
      margin-right: 0.5rem;
    }
    .main-content {
      flex: 1;
      margin-left: 250px;
      padding: 2rem;
      width: calc(100% - 250px);
      min-height: 100vh;
    }
    .form-container {
      background-color: #ffffff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .input-group .btn {
      z-index: 0;
    }
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        position: relative;
        min-height: auto;
      }
      .main-content {
        margin-left: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="d-flex w-100">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content">
      <!-- Top Navbar -->
      <?php include 'topbar.php'; // Add topbar include ?>
      
      <div class="form-container">
      <h3 class="mb-4">Add New Income</h3>
        <?php echo $message; ?>

      <form action="" method="POST">
        <div class="row g-3">
          <div class="col-md-4">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
          </div>
          <div class="col-md-4">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="col-md-4">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" pattern="\d{10}" title="Phone number must be exactly 10 digits">
          </div>

          <div class="col-md-4">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" id="description" name="description" required>
          </div>
          <div class="col-md-4">
            <label for="category" class="form-label">Category</label>
            <div class="input-group">
              <select id="category" name="category" class="form-select" required>
                <option value="" selected disabled>Choose...</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
          <div class="col-md-4">
            <label for="subcategory" class="form-label">Sub-category</label>
            <div class="input-group">
              <select id="subcategory" name="subcategory" class="form-select" required>
                <option value="" selected disabled>Choose category first</option>
              </select>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>

          <div class="col-md-4">
            <label for="total_amount" class="form-label">Total Amount (₹)</label>
            <input type="number" class="form-control" id="total_amount" name="total_amount" required>
          </div>
          <div class="col-md-4">
            <label for="received_amount" class="form-label">Received Amount (₹)</label>
            <input type="number" class="form-control" id="received_amount" name="received_amount" required>
          </div>
          <div class="col-md-4">
            <label for="balance_amount" class="form-label">Balance Amount (₹)</label>
            <input type="number" class="form-control" id="balance_amount" name="balance_amount" readonly>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Submit</button>
          <a href="income.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
      </div>
    </div>
  </div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addCategoryForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="newCategoryName" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="newCategoryName" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Subcategory Modal -->
  <div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-labelledby="addSubcategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSubcategoryModalLabel">Add New Subcategory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addSubcategoryForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="subcategoryCategory" class="form-label">Category</label>
              <select id="subcategoryCategory" class="form-select" required>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="newSubcategoryName" class="form-label">Subcategory Name</label>
              <input type="text" class="form-control" id="newSubcategoryName" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Subcategory</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    $(document).ready(function() {
      // Initialize date picker with dd-mm-yyyy format
      flatpickr('#date', {
        dateFormat: "d-m-Y",
        allowInput: true,
        defaultDate: "today"
      });

      // Calculate balance amount
      $('#total_amount, #received_amount').on('input', function() {
        const total = parseFloat($('#total_amount').val()) || 0;
        const received = parseFloat($('#received_amount').val()) || 0;
        $('#balance_amount').val(total - received);
      });

      // Debug log for troubleshooting
      console.log('Category dropdown initialized');
      
      // Handle category change with improved error handling
      $('#category').on('change', function() {
        const categoryId = $(this).val();
        console.log('Category changed to:', categoryId);
        
        if (categoryId) {
          // Clear and disable subcategory dropdown while loading
          const $subcategory = $('#subcategory');
          $subcategory.empty().append('<option value="" selected disabled>Loading subcategories...</option>');
          $subcategory.prop('disabled', true);
          
          // Fetch subcategories for selected category
          $.ajax({
            url: 'include/category-operations.php',
            method: 'POST',
            dataType: 'json',
            data: {
              action: 'get_subcategories',
              category_id: categoryId
            },
            success: function(response) {
              console.log('Subcategory response:', response);
              
              // Re-enable the dropdown
              $subcategory.prop('disabled', false);
              
              if (response.status === 'success') {
                $subcategory.empty();
                
                if (response.subcategories.length === 0) {
                  $subcategory.append('<option value="" selected disabled>No subcategories available</option>');
                } else {
                  $subcategory.append('<option value="" selected disabled>Choose subcategory...</option>');
                  response.subcategories.forEach(function(subcategory) {
                    $subcategory.append(`<option value="${subcategory.id}">${subcategory.subcategory_name}</option>`);
                  });
                }
              } else {
                $subcategory.empty().append('<option value="" selected disabled>Error loading subcategories</option>');
                console.error('Error loading subcategories:', response.message);
              }
            },
            error: function(xhr, status, error) {
              $subcategory.prop('disabled', false);
              $subcategory.empty().append('<option value="" selected disabled>Error loading subcategories</option>');
              console.error('AJAX error:', status, error);
              console.log('Response text:', xhr.responseText);
            }
          });
        } else {
          $('#subcategory').empty().append('<option value="" selected disabled>Choose category first</option>');
        }
      });

      // Handle add category form submission
      $('#addCategoryForm').on('submit', function(e) {
      e.preventDefault();
        const categoryName = $('#newCategoryName').val();
      
        $.ajax({
          url: 'include/category-operations.php',
        method: 'POST',
          data: {
            action: 'add_category',
            category_name: categoryName
          },
          success: function(response) {
            if (response.status === 'success') {
              // Add new category to dropdowns
              const $category = $('#category');
              const $subcategoryCategory = $('#subcategoryCategory');
              const newOption = `<option value="${response.id}">${categoryName}</option>`;
              
              $category.append(newOption);
              $subcategoryCategory.append(newOption);
              
              // Select the new category
              $category.val(response.id).trigger('change');
          
          // Close modal and reset form
              $('#addCategoryModal').modal('hide');
              $('#newCategoryName').val('');
        } else {
              alert('Failed to add category. Please try again.');
            }
          }
        });
      });

      // Handle add subcategory form submission
      $('#addSubcategoryForm').on('submit', function(e) {
      e.preventDefault();
        const categoryId = $('#subcategoryCategory').val();
        const subcategoryName = $('#newSubcategoryName').val();
      
        $.ajax({
          url: 'include/category-operations.php',
        method: 'POST',
          data: {
            action: 'add_subcategory',
          category_id: categoryId,
          subcategory_name: subcategoryName
          },
          success: function(response) {
            if (response.status === 'success') {
          // If the parent category is currently selected, add the new subcategory to the dropdown
              if ($('#category').val() === categoryId) {
                $('#subcategory').append(`<option value="${response.id}">${subcategoryName}</option>`);
          }
          
          // Close modal and reset form
              $('#addSubcategoryModal').modal('hide');
              $('#newSubcategoryName').val('');
        } else {
              alert('Failed to add subcategory. Please try again.');
            }
          }
        });
      });

      // Update subcategory category when opening modal
      $('#addSubcategoryModal').on('show.bs.modal', function() {
        const selectedCategory = $('#category').val();
        if (selectedCategory) {
          $('#subcategoryCategory').val(selectedCategory);
        }
      });
    });
  </script>
</body>
</html>