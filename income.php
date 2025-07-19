<?php
include 'inc/auth.php'; // Include the authentication file
include 'inc/config.php'; // Include the database connection file

$sql = "SELECT 
        id,
        date,
        name,
        phone,
        description,
        category,
        subcategory,
        amount,
        received,
        balance,
        created_at,
        updated_at
        FROM income 
        ORDER BY date DESC, id DESC";

$result = $conn->query($sql);

if (!$result) {
    error_log("SQL Error in income.php: " . $conn->error);
    echo "<div class='alert alert-danger'>Error fetching income records: " . $conn->error . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Income Records </title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"/>
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
      transition: background-color 0.3s ease;
    }

    .table td .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .table td .btn-danger {
      background-color: #dc3545;
      border: none;
      transition: background-color 0.3s ease;
    }

    .table td .btn-danger:hover {
      background-color: #bb2d3b;
    }

    .badge {
      font-size: 0.75rem;
      padding: 0.3rem 0.5rem;
      border-radius: 0.3rem;
      display: inline-flex;
      align-items: center;
      gap: 0.2rem;
    }

    .badge.bg-success {
      background-color: #198754;
      color: #ffffff;
    }

    .badge.bg-danger {
      background-color: #dc3545;
      color: #ffffff;
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
          <h5>Income Records 1</h5>
          <a href="add-income.php" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Add New Income</a>
        </div>

        <?php
        if (isset($_GET['message'])) { 
            echo "<div class='alert alert-success alert-dismissible fade show'>" . htmlspecialchars($_GET['message']) . 
                "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        }
        if (isset($_GET['error'])) {
            echo "<div class='alert alert-danger alert-dismissible fade show'>" . htmlspecialchars($_GET['error']) . 
                "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        }
        ?>

        <div class="table-responsive">
          <table id="incomeTable" class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>SL No.</th>
                <th>Date</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Description</th>
                <th>Category</th>
                <th>Sub-category</th>
                <th>Total Amount</th>
                <th>Received</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($result && $result->num_rows > 0) {
                  $sl_no = 1;
                  while ($row = $result->fetch_assoc()) {
                      $formatted_date = date("d-m-Y", strtotime($row['date']));
                      $formatted_created_at = date("d-m-Y H:i:s", strtotime($row['created_at']));
                      $formatted_updated_at = date("d-m-Y H:i:s", strtotime($row['updated_at']));
                      $status = ($row['balance'] == 0) 
                          ? "<span class='badge bg-success'><i class='bi bi-check-circle'></i> Paid</span>" 
                          : "<span class='badge bg-danger'><i class='bi bi-x-circle'></i> Pending</span>";

                      echo "<tr>";
                      echo "<td></td>";
                      echo "<td>" . htmlspecialchars($formatted_date) . "</td>";
                      echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['subcategory']) . "</td>";
                      echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                      echo "<td>₹" . number_format($row['received'], 2) . "</td>";
                      echo "<td>₹" . number_format($row['balance'], 2) . "</td>";
                      echo "<td>" . $status . "</td>";
                      echo "<td class='action-column'>
                              <a href='edit-income.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'><i class='bi bi-pencil'></i></a>
                              <a href='include/delete-income.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this record?\")'><i class='bi bi-trash'></i></a>
                            </td>";
                      echo "</tr>";
                      $sl_no++;
                  }
              } else {
                  echo "<tr><td colspan='15' class='text-center'>No records found</td></tr>";
              }
              ?>
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
  <script src="assets/js/responsive.js"></script>
  <script>
    // Prevent DataTables warning messages from showing in the console
    $.fn.dataTable.ext.errMode = 'none';
    
    $(document).ready(function() {
      try {
        // Destroy the table if it's already initialized
        if ($.fn.DataTable.isDataTable('#incomeTable')) {
          $('#incomeTable').DataTable().destroy();
        }
        
        // Initialize the table
        var table = $('#incomeTable').DataTable({
          "processing": true,
          "order": [[1, 'desc']], // Order by date column (index 1) in descending order
          "columnDefs": [
            { 
              "targets": 0,
              "searchable": false,
              "orderable": false,
              "render": function (data, type, row, meta) {
                if (type === 'display') {
                  return meta.row + meta.settings._iDisplayStart + 1;
                }
                return data;
              }
            },
            { "orderable": false, "targets": 11 } // Action column
          ],
          "pageLength": 10,
          "dom": '<"top"lf>rt<"bottom"ip><"clear">',
          "language": {
            "lengthMenu": "_MENU_ records per page",
            "zeroRecords": "No matching records found",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No records available",
            "infoFiltered": "(filtered from _MAX_ total records)",
            "search": "Search:",
            "paginate": {
              "first": "First",
              "last": "Last",
              "next": "Next",
              "previous": "Previous"
            }
          }
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
</body>
</html>

<?php
$conn->close();
?>
