<?php
include 'inc/auth.php'; // Include the authentication file to check user session
// Database connection
include 'inc/config.php'; // Include the database connection file

// Check if user is manager and redirect if true
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'manager') {
    // Redirect to dashboard with error message
    header("Location: dashboard.php?error=You do not have permission to access this page");
    exit();
}

// Fetch all categories
$categories_query = "SELECT id, category_name FROM expenditure_categories";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert date from dd-mm-yyyy to yyyy-mm-dd for database storage
    $date = DateTime::createFromFormat('d-m-Y', $_POST['date'])->format('Y-m-d');
    $name = ucfirst(trim($_POST['name']));
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    
    // Get category and subcategory names from their IDs
    $category_id = intval($_POST['category']);
    $subcategory_id = intval($_POST['subcategory']);
    
    // Get category name
    $cat_stmt = $conn->prepare("SELECT category_name FROM expenditure_categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $category_result = $cat_stmt->get_result();
    $category_name = $category_result->fetch_assoc()['category_name'];
    $cat_stmt->close();
    
    // Get subcategory name
    $subcat_stmt = $conn->prepare("SELECT subcategory_name FROM expenditure_subcategories WHERE id = ?");
    $subcat_stmt->bind_param("i", $subcategory_id);
    $subcat_stmt->execute();
    $subcategory_result = $subcat_stmt->get_result();
    $subcategory_name = $subcategory_result->fetch_assoc()['subcategory_name'];
    $subcat_stmt->close();
    
    $amount = floatval($_POST['total_amount']);
    $paid = floatval($_POST['paid_amount']);
    $balance = $amount - $paid;

    // Insert into database using both IDs and names
    $stmt = $conn->prepare("
        INSERT INTO expenditures (date, name, phone, description, category_id, subcategory_id, category, subcategory, amount, paid, balance) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssiissddd", $date, $name, $phone, $description, $category_id, $subcategory_id, $category_name, $subcategory_name, $amount, $paid, $balance);

    if ($stmt->execute()) {
        // Redirect back to the expenditure page with a success message
        header("Location: expenditure.php?message=Expenditure entry added successfully");
        exit();
    } else {
        // Redirect back to the expenditure page with an error message
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Expenditure</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
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
      background: white;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .form-container h3 {
      margin-bottom: 1.5rem;
      font-weight: bold;
    }
    .form-container .form-label {
      font-weight: 500;
    }
    .form-container .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }
    .form-container .btn-primary:hover {
      background-color: #0056b3;
      border-color: #004085;
    }
    .top-navbar {
      position: sticky;
      top: 0;
      z-index: 1030;
      background: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .top-navbar h4 {
      margin: 0;
      font-weight: bold;
    }
    .top-navbar .user-menu {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .top-navbar .user-menu i {
      font-size: 1.5rem;
      color: #6c757d;
    }
    .top-navbar .user-menu .dropdown-toggle {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      color: #343a40;
      font-weight: 500;
    }
    .top-navbar .user-menu .dropdown-toggle:hover {
      color: #007bff;
    }
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content w-100">
      <!-- Top Navbar -->
      <?php include 'topbar.php'; // Assuming topbar should also be included here for consistency ?>

      <div class="form-container w-100">
        <h3>Add Expenditure</h3>
        <?php if (!empty($message)): ?>
          <?php echo $message; ?>
        <?php endif; ?>
        <form action="" method="POST">
          <div class="row g-4">
            <div class="col-md-4">
              <label for="date" class="form-label">Date</label>
              <input type="text" class="form-control date-picker" id="date" name="date" placeholder="DD-MM-YYYY" required>
            </div>
            <div class="col-md-4">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
            </div>
            <div class="col-md-4">
              <label for="phone" class="form-label">Phone</label>
              <input type="text" class="form-control" id="phone" name="phone" pattern="\d{10}" title="Phone number must be exactly 10 digits" >
            </div>
            <div class="col-md-4">
              <label for="description" class="form-label">Description</label>
              <input type="text" class="form-control" id="description" name="description" placeholder="Enter description" required>
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
              <input type="number" class="form-control" id="total_amount" name="total_amount" placeholder="Enter total amount" required>
            </div>
            <div class="col-md-4">
              <label for="paid_amount" class="form-label">Paid Amount (₹)</label>
              <input type="number" class="form-control" id="paid_amount" name="paid_amount" placeholder="Enter paid amount" required>
            </div>
            <div class="col-md-4">
              <label for="balance_amount" class="form-label">Balance Amount (₹)</label>
              <input type="number" class="form-control" id="balance_amount" name="balance_amount" readonly>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="expenditure.php" class="btn btn-secondary">Cancel</a>
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="assets/js/responsive.js"></script>
  <script>
    // Initialize Flatpickr for date picker
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize date picker if Flatpickr is available
      if (typeof flatpickr === 'function') {
        flatpickr('.date-picker', {
          dateFormat: "d-m-Y",
          defaultDate: "today"
        });
      } else {
        console.warn("Flatpickr not loaded. Using fallback date initialization.");
        // Set today's date as default
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        document.getElementById('date').value = `${day}-${month}-${year}`;
      }
      
      // Set default values if needed
      if (!document.getElementById('date').value) {
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        document.getElementById('date').value = `${day}-${month}-${year}`;
      }
      
      // Check if category is already selected (for page refresh cases)
      const categorySelect = document.getElementById('category');
      if (categorySelect.value) {
        loadSubcategories(categorySelect.value);
      }
      
      // Initialize balance calculation if values are present
      if (document.getElementById('total_amount').value || document.getElementById('paid_amount').value) {
        updateBalance();
      }
    });

    // Update balance amount dynamically
    document.getElementById('paid_amount').addEventListener('input', updateBalance);
    document.getElementById('total_amount').addEventListener('input', updateBalance);

    function updateBalance() {
      const total = parseFloat(document.getElementById('total_amount').value) || 0;
      const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
      const balance = total - paid;
      document.getElementById('balance_amount').value = balance;
    }

    // Capitalize the first letter of each word in the name field
    document.getElementById('name').addEventListener('input', function () {
      const value = this.value;
      this.value = value
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    });

    // Validate phone number (10 digits only)
    document.getElementById('phone').addEventListener('input', function () {
      const phoneField = this;
      const phoneValue = phoneField.value;

      // Allow only digits and limit to 10 characters
      phoneField.value = phoneValue.replace(/\D/g, '').slice(0, 10);

      // Check if the phone number is exactly 10 digits
      if (phoneField.value.length === 10) {
        phoneField.setCustomValidity(''); // Valid input
      } else {
        phoneField.setCustomValidity('Phone number must be exactly 10 digits'); // Invalid input
      }
    });

    // Function to load subcategories
    function loadSubcategories(categoryId) {
      if (!categoryId) return;
      
      console.log('Loading subcategories for category ID:', categoryId);
      
      const subcategorySelect = document.getElementById('subcategory');
      
      // Clear current options
      subcategorySelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
      
      // Fetch subcategories for selected category
      fetch(`get_subcategories.php?category_id=${categoryId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
          }
          return response.json();
        })
        .then(data => {
          console.log('Subcategories data received:', data);
          subcategorySelect.innerHTML = '<option value="" selected disabled>Choose subcategory...</option>';
          
          if (!Array.isArray(data)) {
            console.error('Unexpected data format:', data);
            subcategorySelect.innerHTML = '<option value="" selected disabled>Error: Invalid data format</option>';
            return;
          }
          
          if (data.length === 0) {
            const option = document.createElement('option');
            option.value = "";
            option.textContent = "No subcategories found";
            option.disabled = true;
            subcategorySelect.appendChild(option);
          } else {
            data.forEach(subcategory => {
              try {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.subcategory_name;
                subcategorySelect.appendChild(option);
              } catch (err) {
                console.error('Error adding subcategory option:', err, subcategory);
              }
            });
          }
        })
        .catch(error => {
          console.error('Error fetching subcategories:', error);
          subcategorySelect.innerHTML = '<option value="" selected disabled>Error loading subcategories</option>';
        });
    }

    // Dynamic subcategory loading
    document.getElementById('category').addEventListener('change', function() {
      loadSubcategories(this.value);
    });

    // Add Category Form Handler
    document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const categoryName = document.getElementById('newCategoryName').value;
      
      if (!categoryName.trim()) {
        alert('Please enter a category name');
        return;
      }
      
      fetch('add_expenditure_category.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ category_name: categoryName })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Add new category to the dropdowns
          const categorySelect = document.getElementById('category');
          const subcategoryCategorySelect = document.getElementById('subcategoryCategory');
          const option = new Option(categoryName, data.id);
          categorySelect.add(option);
          subcategoryCategorySelect.add(option.cloneNode(true));
          
          // Close modal and reset form
          const modalElement = document.getElementById('addCategoryModal');
          const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
          modal.hide();
          document.getElementById('addCategoryForm').reset();
          
          // Select the newly added category
          categorySelect.value = data.id;
          // Load subcategories (which will be empty for a new category)
          loadSubcategories(data.id);
        } else {
          alert('Error adding category: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding category: ' + error.message);
      });
    });

    // Add Subcategory Form Handler
    document.getElementById('addSubcategoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const categoryId = document.getElementById('subcategoryCategory').value;
      const subcategoryName = document.getElementById('newSubcategoryName').value;
      
      if (!categoryId) {
        alert('Please select a category');
        return;
      }
      
      if (!subcategoryName.trim()) {
        alert('Please enter a subcategory name');
        return;
      }
      
      fetch('add_expenditure_subcategory.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          category_id: categoryId,
          subcategory_name: subcategoryName
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // If the parent category is currently selected, add the new subcategory to the dropdown
          const currentCategoryId = document.getElementById('category').value;
          if (currentCategoryId === categoryId) {
            const subcategorySelect = document.getElementById('subcategory');
            const option = new Option(subcategoryName, data.id);
            subcategorySelect.appendChild(option);
            // Select the newly added subcategory
            subcategorySelect.value = data.id;
          }
          
          // Close modal and reset form
          const modalElement = document.getElementById('addSubcategoryModal');
          const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
          modal.hide();
          document.getElementById('addSubcategoryForm').reset();
        } else {
          alert('Error adding subcategory: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding subcategory: ' + error.message);
      });
    });
  </script>
</body>
</html>
