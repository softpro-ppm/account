<?php

include 'inc/auth.php'; // Include the database connection file


// Database connection
include 'inc/config.php'; // Include the database connection file

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ucfirst(trim($_POST['name']));
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $address);

    if ($stmt->execute()) {
        // Redirect back to the client page with a success message
        header("Location: client.php?message=Client added successfully");
        exit();
    } else {
        // Show error message
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Client</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      font-family: 'Roboto', sans-serif;
    }

    /* Sidebar Styling */
    .sidebar {
      height: 100vh;
      background-color: #343a40;
      color: #ffffff;
      transition: width 0.3s;
    }
    .sidebar:hover {
      width: 270px;
    }
    .sidebar .nav-link {
      color: #ffffff;
      transition: color 0.3s, background-color 0.3s;
    }
    .sidebar .nav-link.active {
      background-color: #495057;
      font-weight: bold;
    }
    .sidebar .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    /* Main Content Styling */
    .main-content {
      margin-left: 250px;
      padding: 2rem;
    }
    .main-content h3 {
      font-weight: bold;
      color: #495057;
    }

    /* Form Styling */
    form {
      background-color: #ffffff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .form-label {
      font-weight: bold;
      color: #495057;
    }
    .form-control {
      border-radius: 0.5rem;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
      border-radius: 0.5rem;
    }
    .btn-secondary {
      border-radius: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        width: 100%;
        height: auto;
        z-index: 1030;
      }
      .main-content {
        margin-left: 0;
      }
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

      <h3 class="mb-4">Add New Client</h3>
      <?php if (!empty($message)): ?>
        <?php echo $message; ?>
      <?php endif; ?>
      <form action="" method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
          </div>
          <div class="col-md-6">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" placeholder="Enter address" rows="3"></textarea>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
          <button type="submit" class="btn btn-primary w-100 me-2">Submit</button>
          <a href="client.php" class="btn btn-secondary w-100 ms-2">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/responsive.js"></script>
</body>
</html>