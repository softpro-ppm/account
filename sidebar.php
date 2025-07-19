<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar d-flex flex-column p-3 text-white position-fixed" style="width: 250px;">
  <h4 class="text-white">Account Panel</h4>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <?php if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'manager'): ?>
    <li><a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <?php endif; ?>
    <li><a href="income.php" class="nav-link <?php echo $current_page === 'income.php' ? 'active' : ''; ?>"><i class="bi bi-currency-rupee"></i> Income</a></li>
    <li><a href="expenditure.php" class="nav-link <?php echo $current_page === 'expenditure.php' ? 'active' : ''; ?>"><i class="bi bi-wallet2"></i> Expenditure</a></li>
    <li><a href="loan.php" class="nav-link <?php echo $current_page === 'loan.php' ? 'active' : ''; ?>"><i class="bi bi-cash-coin"></i> Loan</a></li>
    <li><a href="report.php" class="nav-link <?php echo $current_page === 'report.php' ? 'active' : ''; ?>"><i class="bi bi-bar-chart"></i> Reports</a></li>
    <!-- <li><a href="client.php" class="nav-link <?php echo $current_page === 'client.php' ? 'active' : ''; ?>"><i class="bi bi-person-lines-fill"></i> Clients</a></li> -->
    <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
    <li><a href="users.php" class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Users</a></li>
    <?php endif; ?>
  </ul>
</nav> 