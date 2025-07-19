<?php
include 'inc/auth.php';
include 'inc/config.php';

// Check if user is manager and redirect if true
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'manager') {
    header("Location: dashboard.php?error=You do not have permission to access this page");
    exit();
}

// Check if loan ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: loan.php?error=Invalid loan ID");
    exit();
}

$loan_id = $_GET['id'];

// Fetch loan details
$sql = "SELECT * FROM loans WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: loan.php?error=Loan not found");
    exit();
}

$loan = $result->fetch_assoc();
$stmt->close();

// Fetch loan categories
$sql = "SELECT name FROM loan_categories ORDER BY name";
$result = $conn->query($sql);
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use the MySQL formatted date from the hidden input
    $date = $_POST['mysql_date'] ?? date('Y-m-d', strtotime($_POST['date']));
    $name = $_POST['name'];
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'];
    $paid = $_POST['paid'];
    $balance = $amount - $paid;

    // Update the loan
    $sql = "UPDATE loans SET date = ?, name = ?, phone = ?, description = ?, 
            amount = ?, paid = ?, balance = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdddi", $date, $name, $phone, $description, $amount, $paid, $balance, $loan_id);
    
    if ($stmt->execute()) {
        header("Location: loan.php?message=Loan updated successfully");
        exit();
    } else {
        $error = "Error updating loan: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Loan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        html, body {
            height: 100%;
            overflow: auto;
        }

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
            overflow-y: auto;
            height: 100vh;
        }

        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1rem 2rem;
        }

        .form-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
            margin-top: 1rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .input-group .btn-add-item {
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            color: #0d6efd;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 0 0.375rem 0.375rem 0;
            transition: all 0.2s;
        }

        .input-group .btn-add-item:hover {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .input-group .form-select {
            border-radius: 0.375rem 0 0 0.375rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #495057;
        }

        .btn-save {
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
        }

        .btn-save i {
            margin-right: 0.5rem;
        }

        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-radius: 0 0 0.5rem 0.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .alert {
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }

        /* Flatpickr custom styles */
        .flatpickr-input {
            background-color: #fff !important;
        }
        
        .flatpickr-calendar {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .input-group .flatpickr-input {
            border-start-start-radius: 0;
            border-end-start-radius: 0;
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
            <?php include 'topbar.php'; ?>

            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Edit Loan</h5>
                    <a href="loan.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Loans</a>
                </div>

                <div class="form-container">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="editLoanForm" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="text" class="form-control" id="date" name="date" 
                                           value="<?php echo date('d-m-Y', strtotime($loan['date'])); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($loan['name']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($loan['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="description" class="form-label">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                    <input type="text" class="form-control" id="description" name="description" 
                                           value="<?php echo htmlspecialchars($loan['description'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" 
                                           value="<?php echo $loan['amount']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="paid" class="form-label">Paid Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="paid" name="paid" step="0.01" 
                                           value="<?php echo $loan['paid']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="balance" class="form-label">Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="balance" name="balance" step="0.01" 
                                           value="<?php echo $loan['balance']; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-save">
                                <i class="bi bi-save"></i> Update Loan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newCategory" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="newCategory" placeholder="Enter category name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCategory">Save Category</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subcategory Modal -->
    <div class="modal fade" id="addSubcategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subcategoryCategory" class="form-label">Category</label>
                        <select class="form-select" id="subcategoryCategory" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newSubcategory" class="form-label">Subcategory Name</label>
                        <input type="text" class="form-control" id="newSubcategory" placeholder="Enter subcategory name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSubcategory">Save Subcategory</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="assets/js/responsive.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Flatpickr
            flatpickr("#date", {
                dateFormat: "d-m-Y",
                defaultDate: new Date("<?php echo $loan['date']; ?>"),
                allowInput: true
            });

            // Calculate balance automatically when amount or paid changes
            function calculateBalance() {
                var amount = parseFloat($('#amount').val()) || 0;
                var paid = parseFloat($('#paid').val()) || 0;
                var balance = amount - paid;
                $('#balance').val(balance.toFixed(2));
            }

            // Bind calculation to both amount and paid inputs
            $('#amount, #paid').on('input', calculateBalance);

            // Form validation
            var form = document.getElementById('editLoanForm');
            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            }

            // Initial calculation in case of pre-filled values
            calculateBalance();

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 