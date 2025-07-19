<?php
include 'inc/auth.php'; // Include the database connection file
include 'inc/config.php'; // Include the database connection file

// Fetch clients from the database
$sql = "SELECT id, name, email, phone, address, created_at FROM clients ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clients</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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

    .table-responsive {
      border-radius: 0.5rem;
      overflow-x: auto;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      background-color: white;
      padding: 1.5rem;
      margin-top: 1rem;
      scrollbar-width: thin;
      scrollbar-color: #dee2e6 #f8f9fa;
    }

    .table-responsive::-webkit-scrollbar {
      height: 8px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
      background-color: #dee2e6;
      border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-track {
      background-color: #f8f9fa;
    }

    .table {
      white-space: nowrap;
      margin: 0;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 0.875rem;
    }

    .table th, .table td {
      text-align: center;
      vertical-align: middle;
      padding: 0.75rem;
      border-bottom: 1px solid #dee2e6;
    }

    .table th {
      background-color: #f8f9fa;
      text-transform: uppercase;
      font-weight: bold;
      color: #495057;
      font-size: 0.8rem;
    }

    .table tbody tr:hover {
      background-color: #f9f9f9;
      transition: background-color 0.3s ease;
    }

    .table td .btn {
      padding: 0.3rem 0.6rem;
      font-size: 0.75rem;
      border-radius: 0.3rem;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }

    .table td .btn-primary {
      background-color: #0d6efd;
      border: none;
      color: white;
      transition: background-color 0.3s ease;
    }

    .table td .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .table td .btn-danger {
      background-color: #dc3545;
      border: none;
      color: white;
      transition: background-color 0.3s ease;
    }

    .table td .btn-danger:hover {
      background-color: #bb2d3b;
    }

    .dataTables_wrapper {
      overflow-x: auto;
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
        <h5>Clients</h5>
        <a href="add-client.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Client</a>
      </div>

      <?php
      if (isset($_GET['message']))  { 
          echo "<div class='alert alert-success alert-dismissible fade show'>" . htmlspecialchars($_GET['message']) . 
              "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
      }
      if (isset($_GET['error'])) {
          echo "<div class='alert alert-danger alert-dismissible fade show'>" . htmlspecialchars($_GET['error']) . 
              "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
      }
      ?>

      <div class="table-responsive">
        <table id="clientTable" class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>SL No.</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Address</th>
              <th>Registered On</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php $sl_no = 1; ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $sl_no++; ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['phone']); ?></td>
                  <td><?php echo htmlspecialchars($row['address']); ?></td>
                  <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                  <td>
                    <a href="edit-client.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</a>
                    <a href="include/delete-client.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this client?');"><i class="bi bi-trash"></i> Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center">No clients found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Custom DataTable init script -->
<script>
  // Prevent DataTables warning messages from showing in the console
  $.fn.dataTable.ext.errMode = 'none';
  
  $(document).ready(function() {
    try {
      // Destroy the table if it's already initialized
      if ($.fn.DataTable.isDataTable('#clientTable')) {
        $('#clientTable').DataTable().destroy();
      }
      
      // Initialize the table
      $('#clientTable').DataTable({
        responsive: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columnDefs: [
          { targets: [0, 1, 2, 3, 4, 5, 6], className: 'text-center' }
        ],
        language: {
          emptyTable: "No clients found",
          zeroRecords: "No matching records found"
        },
        destroy: true // Allow the table to be reinitialized
      });
    } catch (error) {
      console.log("DataTable initialization error:", error);
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
      $('.alert').alert('close');
    }, 5000);
  });
</script>
<!-- Responsive script MUST be last -->
<script src="assets/js/responsive.js"></script>

</body>
</html>

<?php
$conn->close();
?>