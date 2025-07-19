<?php
include 'inc/auth.php'; // Include the authentication file to check user login status
// Database connection
include 'inc/config.php';

// Check if user is manager and redirect if true
if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'manager') {
    // Redirect to income page with error message since managers can't access dashboard
    header("Location: income.php?error=You do not have permission to access the dashboard");
    exit();
}

// Generate financial years for dropdown
$currentYear = date('Y');
$currentCalendarMonth = date('n'); // Current calendar month number
$startYearForDropdown = ($currentCalendarMonth >= 4) ? $currentYear : $currentYear - 1; // Financial year starts in April
$financialYears = [];
for ($i = 0; $i < 5; $i++) {
    $endYearForDropdown = $startYearForDropdown + 1;
    $financialYears[] = "$startYearForDropdown-$endYearForDropdown";
    $startYearForDropdown--;
}

// Determine the default financial year for display if none selected
$defaultFinancialYear = ($currentCalendarMonth >= 4) ? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y');

// Get the selected financial year from the query parameter or use the default
$selectedFinancialYear = $_GET['financial_year'] ?? $defaultFinancialYear;
list($startYear, $endYear) = explode('-', $selectedFinancialYear); // These determine the data range

// Create the short display format (YYYY-YY)
$displayFinancialYearShort = $startYear . '-' . substr($endYear, -2);

// --- Fetch Data based on SELECTED Financial Year ($startYear, $endYear) ---

// Fetch total income for the SELECTED financial year
$totalIncomeQuery = "
  SELECT SUM(amount) AS total_income 
  FROM income 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = $totalIncomeResult->fetch_assoc()['total_income'] ?? 0;

// Fetch total expenditure for the SELECTED financial year
$totalExpenditureQuery = "
  SELECT SUM(amount) AS total_expenditure 
  FROM expenditures 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)";
$totalExpenditureResult = $conn->query($totalExpenditureQuery);
$totalExpenditure = $totalExpenditureResult->fetch_assoc()['total_expenditure'] ?? 0;

// Fetch pending income for the CURRENT calendar month WITHIN the SELECTED financial year
$currentMonth = date('n'); // Get current calendar month number
$currentMonthPendingIncomeQuery = "
  SELECT SUM(balance) AS pending_income 
  FROM income 
  WHERE MONTH(date) = $currentMonth AND 
  ( (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR (MONTH(date) < 4 AND YEAR(date) = $endYear) )";
$currentMonthPendingIncomeResult = $conn->query($currentMonthPendingIncomeQuery);
$currentMonthPendingIncome = $currentMonthPendingIncomeResult->fetch_assoc()['pending_income'] ?? 0;

// Fetch pending income for the SELECTED financial year (Used for the 'Year' card)
$currentYearPendingIncomeQuery = "
  SELECT SUM(balance) AS pending_income 
  FROM income 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)";
$currentYearPendingIncomeResult = $conn->query($currentYearPendingIncomeQuery);
$currentYearPendingIncome = $currentYearPendingIncomeResult->fetch_assoc()['pending_income'] ?? 0;

// Fetch pending expenditure for the SELECTED financial year (Used for the 'Year' card)
$currentYearPendingExpenditureQuery = "
  SELECT SUM(balance) AS pending_expenditure 
  FROM expenditures 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)";
$currentYearPendingExpenditureResult = $conn->query($currentYearPendingExpenditureQuery);
$currentYearPendingExpenditure = $currentYearPendingExpenditureResult->fetch_assoc()['pending_expenditure'] ?? 0;

// Fetch monthly income for the SELECTED financial year
$monthlyIncomeQuery = "
  SELECT 
    MONTH(date) AS month, 
    SUM(amount) AS total 
  FROM income 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)
  GROUP BY MONTH(date)";
$monthlyIncomeResult = $conn->query($monthlyIncomeQuery);
$monthlyIncomeData = [];
while ($row = $monthlyIncomeResult->fetch_assoc()) {
    $monthlyIncomeData[$row['month']] = $row['total'];
}

// Fetch monthly expenditure for the SELECTED financial year
$monthlyExpenditureQuery = "
  SELECT 
    MONTH(date) AS month, 
    SUM(amount) AS total 
  FROM expenditures 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)
  GROUP BY MONTH(date)";
$monthlyExpenditureResult = $conn->query($monthlyExpenditureQuery);
$monthlyExpenditureData = [];
while ($row = $monthlyExpenditureResult->fetch_assoc()) {
    $monthlyExpenditureData[$row['month']] = $row['total'];
}

// Fetch pending loans for the SELECTED financial year
$totalPendingLoansQuery = "
  SELECT SUM(balance) AS total_pending_loans 
  FROM loans 
  WHERE balance > 0";
$totalPendingLoansResult = $conn->query($totalPendingLoansQuery);
$totalPendingLoans = $totalPendingLoansResult->fetch_assoc()['total_pending_loans'] ?? 0;

// --- Setup for Charts using SELECTED Financial Year Data ---

// Generate labels for the financial year (April to March)
$financialYearLabels = [];
for ($i = 4; $i <= 12; $i++) {
    $financialYearLabels[] = date('F', mktime(0, 0, 0, $i, 1));
}
for ($i = 1; $i <= 3; $i++) {
    $financialYearLabels[] = date('F', mktime(0, 0, 0, $i, 1));
}

// Fetch income distribution for the SELECTED financial year
$incomeDistributionQuery = "
  SELECT category, SUM(amount) AS total 
  FROM income 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)
  GROUP BY category";
$incomeDistributionResult = $conn->query($incomeDistributionQuery);
$incomeDistributionData = [];
while ($row = $incomeDistributionResult->fetch_assoc()) {
    $incomeDistributionData[] = ['category' => $row['category'], 'total' => $row['total']];
}

// Fetch expenditure distribution for the SELECTED financial year
$expenditureDistributionQuery = "
  SELECT category, SUM(amount) AS total 
  FROM expenditures 
  WHERE 
    (MONTH(date) >= 4 AND YEAR(date) = $startYear) OR 
    (MONTH(date) < 4 AND YEAR(date) = $endYear)
  GROUP BY category";
$expenditureDistributionResult = $conn->query($expenditureDistributionQuery);
$expenditureDistributionData = [];
while ($row = $expenditureDistributionResult->fetch_assoc()) {
    $expenditureDistributionData[] = ['category' => $row['category'], 'total' => $row['total']];
}

// Calculate total income and expenditure for the distribution pie chart (using financial year data)
$totalIncomeForPie = array_sum(array_column($incomeDistributionData, 'total'));
$totalExpenditureForPie = array_sum(array_column($expenditureDistributionData, 'total'));

// Prepare data for the distribution pie chart
$distributionPieData = [
    'Income' => $totalIncomeForPie,
    'Expenditure' => $totalExpenditureForPie
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
    .dashboard-card {
      border-radius: 1rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .top-navbar {
      position: sticky;
      top: 0;
      z-index: 1030;
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    canvas {
      background-color: white;
      border-radius: 1rem;
      padding: 1rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content w-100">
      <!-- Top Navbar -->
      <?php include 'topbar.php'; // Handles financial year selection ?>

      <div class="p-4">
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <!-- Update title to show selected financial year in YYYY-YY format -->
        <h2>Dashboard (<?php echo $displayFinancialYearShort; ?>)</h2> 
        <!-- Remove static month/year subheading -->
        <!-- <h5 class="text-muted mb-4"><?php echo date('F Y'); ?></h5> -->

        <!-- Cards: Update titles and ensure values use selected financial year data -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                <!-- Title uses YYYY-YY format -->
                <h5 class="card-title">Income (<?php echo $displayFinancialYearShort; ?>)</h5>
                <!-- Value uses data from selected financial year -->
                <h3 class="text-success">₹<?php echo number_format($totalIncome, 0); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses current month name -->
                <h5 class="card-title">Income (<?php echo date('F'); ?>)</h5>
                 <!-- Value uses current month's income FROM the selected financial year's monthly data -->
                <h3 class="text-success">₹<?php echo number_format($monthlyIncomeData[date('n')] ?? 0, 0); ?></h3>
              </div>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses YYYY-YY format -->
                <h5 class="card-title">Pending Income (<?php echo $displayFinancialYearShort; ?>)</h5>
                 <!-- Value uses pending income from selected financial year -->
                <h3 class="text-success">₹<?php echo number_format($currentYearPendingIncome, 0); ?></h3>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses current month name -->
                <h5 class="card-title">Pending Income (<?php echo date('F'); ?>)</h5>
                 <!-- Value uses pending income calculated for current month WITHIN selected financial year -->
                <h3 class="text-success">₹<?php echo number_format($currentMonthPendingIncome, 0); ?></h3>
              </div>
            </div>
          </div>
          </div>

        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses YYYY-YY format -->
                <h5 class="card-title">Expenditure (<?php echo $displayFinancialYearShort; ?>)</h5>
                 <!-- Value uses data from selected financial year -->
                <h3 class="text-danger">₹<?php echo number_format($totalExpenditure, 0); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses current month name -->
                <h5 class="card-title">Expenditure (<?php echo date('F'); ?>)</h5>
                 <!-- Value uses current month's expenditure FROM the selected financial year's monthly data -->
                <h3 class="text-danger">₹<?php echo number_format($monthlyExpenditureData[date('n')] ?? 0, 0); ?></h3>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses YYYY-YY format -->
                <h5 class="card-title">Pending Expenses (<?php echo $displayFinancialYearShort; ?>)</h5>
                 <!-- Value uses pending expenditure from selected financial year -->
                <h3 class="text-danger">₹<?php echo number_format($currentYearPendingExpenditure, 0); ?></h3>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card dashboard-card p-3">
              <div class="card-body">
                 <!-- Title uses YYYY-YY format -->
                <h5 class="card-title">Pending Loans (<?php echo $displayFinancialYearShort; ?>)</h5>
                 <!-- Value uses pending loans from selected financial year -->
                <h3 class="text-danger">₹<?php echo number_format($totalPendingLoans, 0); ?></h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Graphs Section - Data reflects selected financial year -->
        <div class="row g-4">
          <div class="col-md-4">
            <!-- Add selected financial year to chart title -->
            <h5 class="mb-3">Monthly Income Trend (<?php echo $selectedFinancialYear; ?>)</h5> 
            <canvas id="incomeChart" height="300"></canvas>
          </div>

          <div class="col-md-4">
             <!-- Add selected financial year to chart title -->
            <h5 class="mb-3">Income vs Expenditure (<?php echo $selectedFinancialYear; ?>)</h5>
            <canvas id="combinedChart" height="300"></canvas>
          </div>
          
          <div class="col-md-4">
             <!-- Add selected financial year to chart title -->
            <h5 class="mb-3">Monthly Expenditure Trend (<?php echo $selectedFinancialYear; ?>)</h5>
            <canvas id="expenditureChart" height="300"></canvas>
          </div>
        </div>

        <div class="row g-4 mt-4">
          <div class="col-md-4">
             <!-- Add selected financial year to chart title -->
            <h5 class="mb-3">Income Distribution (<?php echo $selectedFinancialYear; ?>)</h5>
            <canvas id="incomePieChart" height="200" width="200"></canvas>
          </div>

          <div class="col-md-4">
             <!-- Add selected financial year to chart title -->
            <h5 class="mb-3 text-center">Income vs Expenditure (<?php echo $selectedFinancialYear; ?>)</h5>
            <canvas id="distributionPieChart" height="300"></canvas>
          </div>

          <div class="col-md-4">
             <!-- Add selected financial year to chart title -->
            <h5 class="mb-3">Expenditure Distribution (<?php echo $selectedFinancialYear; ?>)</h5>
            <canvas id="expenditurePieChart" height="200" width="200"></canvas>
          </div>
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
    // --- Chart setup using Financial Year Data (Apr-Mar) ---

    // Monthly Income Trend
    const incomeChart = new Chart(document.getElementById('incomeChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($financialYearLabels); ?>, // Financial year labels (Apr-Mar)
        datasets: [{
          label: 'Income',
          data: <?php
            $incomeDataForGraph = [];
            for ($i = 4; $i <= 12; $i++) { // Apr to Dec
                $incomeDataForGraph[] = $monthlyIncomeData[$i] ?? 0;
            }
            for ($i = 1; $i <= 3; $i++) { // Jan to Mar
                $incomeDataForGraph[] = $monthlyIncomeData[$i] ?? 0;
            }
            echo json_encode($incomeDataForGraph);
          ?>,
          borderColor: 'green',
          backgroundColor: 'rgba(0, 128, 0, 0.1)',
          tension: 0.3,
          fill: true
        }]
      },
       options: { // Options kept same as before
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Income (₹)'
            }
          }
        }
      }
    });

    // Monthly Expenditure Trend
    const expenditureChart = new Chart(document.getElementById('expenditureChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($financialYearLabels); ?>, // Financial year labels (Apr-Mar)
        datasets: [{
          label: 'Expenditure',
          data: <?php
            $expenditureDataForGraph = [];
            for ($i = 4; $i <= 12; $i++) { // Apr to Dec
                $expenditureDataForGraph[] = $monthlyExpenditureData[$i] ?? 0;
            }
            for ($i = 1; $i <= 3; $i++) { // Jan to Mar
                $expenditureDataForGraph[] = $monthlyExpenditureData[$i] ?? 0;
            }
            echo json_encode($expenditureDataForGraph);
          ?>,
          borderColor: 'red',
          backgroundColor: 'rgba(255, 0, 0, 0.1)',
          tension: 0.3,
          fill: true
        }]
      },
      options: { // Options kept same as before
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Expenditure (₹)'
            }
          }
        }
      }
    });

    // Income Pie Chart (Data based on selected financial year)
    const incomePieChart = new Chart(document.getElementById('incomePieChart'), {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_column($incomeDistributionData, 'category')); ?>, 
        datasets: [{
          data: <?php echo json_encode(array_column($incomeDistributionData, 'total')); ?>, 
          backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#9c27b0', '#e91e63'],
          borderColor: '#ffffff',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
       options: { // Options kept same as before
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: {
                size: 10
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function (tooltipItem) {
                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                const value = tooltipItem.raw;
                const percentage = ((value / total) * 100).toFixed(2);
                return `${tooltipItem.label}: ₹${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Expenditure Pie Chart (Data based on selected financial year)
    const expenditurePieChart = new Chart(document.getElementById('expenditurePieChart'), {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_column($expenditureDistributionData, 'category')); ?>, 
        datasets: [{
          data: <?php echo json_encode(array_column($expenditureDistributionData, 'total')); ?>, 
          backgroundColor: ['#e91e63', '#ff5722', '#9c27b0', '#03a9f4', '#8bc34a'],
          borderColor: '#ffffff',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
      options: { // Options kept same as before
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: {
                size: 10
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function (tooltipItem) {
                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                const value = tooltipItem.raw;
                const percentage = ((value / total) * 100).toFixed(2);
                return `${tooltipItem.label}: ₹${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Combined Income vs Expenditure Graph (Data based on selected financial year)
    const combinedChart = new Chart(document.getElementById('combinedChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($financialYearLabels); ?>, // Financial year labels (Apr-Mar)
        datasets: [
          {
            label: 'Income',
            data: <?php echo json_encode($incomeDataForGraph); ?>, // Uses financial year data
            borderColor: 'green',
            backgroundColor: 'rgba(0, 128, 0, 0.1)',
            tension: 0.3,
            fill: true
          },
          {
            label: 'Expenditure',
            data: <?php echo json_encode($expenditureDataForGraph); ?>, // Uses financial year data
            borderColor: 'red',
            backgroundColor: 'rgba(255, 0, 0, 0.1)',
            tension: 0.3,
            fill: true
          }
        ]
      },
      options: { // Options kept same as before
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Amount (₹)'
            }
          }
        }
      }
    });

    // Income vs Expenditure Distribution Pie Chart (Data based on selected financial year)
    const distributionPieChart = new Chart(document.getElementById('distributionPieChart'), {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_keys($distributionPieData)); ?>, // Labels: Income, Expenditure
        datasets: [{
          data: <?php echo json_encode(array_values($distributionPieData)); ?>, // Data: Total income and expenditure for selected year
          backgroundColor: ['#4caf50', '#f44336'],
          borderColor: '#ffffff',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
      options: { // Options kept same as before
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function (tooltipItem) {
                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                const value = tooltipItem.raw;
                const percentage = ((value / total) * 100).toFixed(2);
                return `${tooltipItem.label}: ₹${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

  </script>
</body>

</html>
