<?php
include 'inc/auth.php'; // Include authentication check
include 'inc/config.php'; // Database connection

$message = '';
$reports = [];

// Default conditions for page load
$from_date = null;
$to_date = null;
$type = 'all';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? null;
    $to_date = $_POST['to_date'] ?? null;
    $type = $_POST['type'] ?? 'all';

    // Convert dates to SQL format (yyyy-mm-dd)
    if ($from_date) {
        $from_date = DateTime::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
    }
    if ($to_date) {
        $to_date = DateTime::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
    }
}

// Initialize the base query
if ($type === 'income') {
    $query = "SELECT 'Income' AS type, date, name, category, subcategory, amount, received AS paid_received, balance FROM income";
} elseif ($type === 'expenditure') {
    $query = "SELECT 'Expenditure' AS type, date, name, category, subcategory, amount, paid AS paid_received, balance FROM expenditures";
} else {
    // Wrap the combined query in parentheses and alias it as `combined`
    $query = "SELECT * FROM (
        SELECT 'Income' AS type, date, name, category, subcategory, amount, received AS paid_received, balance FROM income
        UNION ALL
        SELECT 'Expenditure' AS type, date, name, category, subcategory, amount, paid AS paid_received, balance FROM expenditures
    ) AS combined";
}

// Add conditions dynamically
$conditions = [];
if ($from_date) {
    $conditions[] = "date >= '$from_date'";
}
if ($to_date) {
    $conditions[] = "date <= '$to_date'";
}

// Append conditions to the query
if (!empty($conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $conditions);
    $query .= $where_clause;
}

// Add ordering
$query .= " ORDER BY date DESC";

// Execute the query
$result = $conn->query($query);
if ($result) {
    $reports = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $message = "<div class='alert alert-danger'>Error generating report: " . $conn->error . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

    .dataTables_wrapper {
      overflow-x: auto;
    }

    /* Form Styles */
    .form-control, .form-select {
      font-size: 0.875rem;
      padding: 0.5rem 0.75rem;
      border-radius: 0.375rem;
      border: 1px solid #dee2e6;
      background-color: #fff;
    }

    .form-control:focus, .form-select:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .btn-primary {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      border-radius: 0.375rem;
      background-color: #0d6efd;
      border: none;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    /* Amount Columns */
    .table td:nth-child(7),
    .table td:nth-child(8),
    .table td:nth-child(9) {
      text-align: right;
      font-family: monospace;
      font-size: 0.875rem;
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
        <h5 class="mb-4">Generate Report</h5>
        <?php if (!empty($message)): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <form class="row g-3 mb-4" method="POST">
          <div class="col-md-3">
            <label for="from_date" class="form-label">From Date</label>
            <input type="text" class="form-control date-picker" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="<?php echo isset($_POST['from_date']) ? htmlspecialchars($_POST['from_date']) : ''; ?>">
          </div>
          <div class="col-md-3">
            <label for="to_date" class="form-label">To Date</label>
            <input type="text" class="form-control date-picker" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="<?php echo isset($_POST['to_date']) ? htmlspecialchars($_POST['to_date']) : ''; ?>">
          </div>
          <div class="col-md-3">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-select">
              <option value="all" <?php echo ($type === 'all') ? 'selected' : ''; ?>>All</option>
              <option value="income" <?php echo ($type === 'income') ? 'selected' : ''; ?>>Income</option>
              <option value="expenditure" <?php echo ($type === 'expenditure') ? 'selected' : ''; ?>>Expenditure</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
          </div>
        </form>

        <div class="table-responsive">
          <table id="reportTable" class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>SL No.</th>
                <th>Date</th>
                <th>Type</th>
                <th>Name</th>
                <th>Category</th>
                <th>Sub-Category</th>
                <th>Amount</th>
                <th>Paid/Received</th>
                <th>Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($reports)): ?>
                <?php $sl_no = 1; ?>
                <?php foreach ($reports as $report): ?>
                  <tr class="<?php echo strtolower($report['type']) === 'income' ? 'table-success' : 'table-danger'; ?>">
                    <td><?php echo $sl_no++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($report['date'])); ?></td>
                    <td><?php echo $report['type']; ?></td>
                    <td><?php echo htmlspecialchars($report['name']); ?></td>
                    <td><?php echo htmlspecialchars($report['category']); ?></td>
                    <td><?php echo htmlspecialchars($report['subcategory']); ?></td>
                    <td>₹<?php echo number_format($report['amount'], 2); ?></td>
                    <td>₹<?php echo number_format($report['paid_received'], 2); ?></td>
                    <td>₹<?php echo number_format($report['balance'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center">No records found.</td>
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="assets/js/responsive.js"></script>
  <script>
    // Prevent DataTables warning messages from showing in the console
    $.fn.dataTable.ext.errMode = 'none';
    
    $(document).ready(function() {
      // Initialize date pickers
      flatpickr('.date-picker', {
        dateFormat: "d-m-Y",
        allowInput: true
      });
      
      try {
        // Destroy the table if it's already initialized
        if ($.fn.DataTable.isDataTable('#reportTable')) {
          $('#reportTable').DataTable().destroy();
        }
        
        // Initialize the table
        $('#reportTable').DataTable({
          responsive: true,
          lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
          columnDefs: [
            { targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], className: 'text-center' }
          ],
          language: {
            emptyTable: "No records found",
            zeroRecords: "No matching records found"
          },
          destroy: true, // Allow the table to be reinitialized
          order: [[1, 'desc']] // Sort by date column in descending order
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