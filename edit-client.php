<?php
include 'inc/auth.php'; // Include the authentication file to check user session
// Database connection
include 'inc/config.php'; // Include the database connection file

$message = '';

// Fetch client details for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    $sql = "SELECT * FROM clients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    if (!$client) {
        header("Location: client.php?error=Client not found");
        exit();
    }
} else {
    header("Location: client.php?error=No client ID provided");
    exit();
}

// Handle form submission for updating client
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ucfirst(trim($_POST['name']));
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Update client record in the database
    $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);

    if ($stmt->execute()) {
        header("Location: client.php?message=Client updated successfully");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Client</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      font-family: 'Roboto', sans-serif;
    }
    .sidebar {
      height: 100vh;
      background-color: #343a40;
      color: #ffffff;
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
    }
    .btn-primary {
      border-radius: 0.5rem;
    }
    .btn-secondary {
      border-radius: 0.5rem;
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

      <h3 class="mb-4">Edit Client</h3>
      <?php if (!empty($message)): ?>
        <?php echo $message; ?>
      <?php endif; ?>
      <form action="" method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($client['address']); ?></textarea>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
          <button type="submit" class="btn btn-primary w-100 me-2">Update</button>
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