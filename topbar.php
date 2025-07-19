<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_titles = [
    'dashboard' => 'Dashboard',
    'income' => 'Income Records',
    'expenditure' => 'Expenditure Records',
    'report' => 'Reports',
    'client' => 'Client List',
    'users' => 'User Management',
    'add-income' => 'Add Income',
    'edit-income' => 'Edit Income',
    'add-expenditure' => 'Add Expenditure',
    'edit-expenditure' => 'Edit Expenditure',
    'add-client' => 'Add Client',
    'edit-client' => 'Edit Client',
    'add-user' => 'Add User',
    'edit-user' => 'Edit User',
    'loan' => 'Loan'
];

$page_title = $page_titles[$current_page] ?? 'Dashboard';
?>

<div class="top-navbar">
    <div class="d-flex justify-content-between align-items-center w-100">
        <!-- Left side: Toggle & Title -->
        <div class="d-flex align-items-center">
            <button class="mobile-toggle btn btn-link d-lg-none me-3">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h4 class="mb-0 page-title"><?php echo $page_title; ?></h4>
        </div>

        <!-- Middle elements: Actions & Financial Year (if applicable) -->
        <div class="d-flex align-items-center gap-3">
             <!-- Financial Year Dropdown (only for Dashboard) -->
             <?php if ($current_page === 'dashboard' && isset($selectedFinancialYear) && isset($financialYears)): ?>
             <div class="financial-year-selector">
                 <div class="dropdown">
                     <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="financialYearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                         <?php echo $selectedFinancialYear; ?>
                     </button>
                     <ul class="dropdown-menu" aria-labelledby="financialYearDropdown">
                         <?php foreach ($financialYears as $year): ?>
                         <li>
                             <a class="dropdown-item financial-year-option" href="#" data-year="<?php echo $year; ?>">
                                 <?php echo $year; ?>
                             </a>
                         </li>
                         <?php endforeach; ?>
                     </ul>
                 </div>
             </div>
             <?php endif; ?>
             
             <!-- Quick Actions -->
             <?php if (in_array($current_page, ['dashboard', 'income', 'expenditure'])): ?>
             <div class="quick-actions d-none d-md-flex">
                 <a href="add-income.php" class="btn btn-sm btn-success me-2">
                     <i class="bi bi-plus-circle"></i>
                     <span class="d-none d-lg-inline">Add Income</span>
                 </a>
                 <a href="add-expenditure.php" class="btn btn-sm btn-danger">
                     <i class="bi bi-dash-circle"></i>
                     <span class="d-none d-lg-inline">Add Expenditure</span>
                 </a>
             </div>
             <?php endif; ?>
        </div>

        <!-- Right side: User Menu -->
        <div class="user-menu dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <div class="user-avatar me-2">
                    <i class="bi bi-person-circle fs-5"></i>
                </div>
                <div class="user-info d-none d-md-block">
                    <div class="fw-bold"><?php echo $_SESSION['username'] ?? 'Admin'; ?></div>
                    <div class="small text-muted"><?php echo $_SESSION['role'] ?? 'Administrator'; ?></div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Add event listener to the financial year options
  document.querySelectorAll('.financial-year-option').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault(); // Prevent default anchor behavior
      const selectedYear = this.getAttribute('data-year');
      
      // Construct the new URL
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set('financial_year', selectedYear);
      
      // Reload the page with the new financial year parameter
      window.location.href = currentUrl.toString();
    });
  });
});
</script> 