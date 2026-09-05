<?php
/**
 * MB Internet And Digital Studio | Admin Console Header (Core PHP)
 */
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// শুধুমাত্র অ্যাডমিনের জন্য সুরক্ষিত
require_admin();

$logged_admin = current_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " | Admin Console" : "Admin Console | MB Studio"; ?></title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3.3 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Stylesheets -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">

  <!-- Mobile Sidebar Backdrop -->
  <div class="admin-sidebar-backdrop" id="adminBackdrop" onclick="toggleAdminSidebar()"></div>

  <div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="admin-sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">
        <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
          <div class="rounded-3 bg-danger text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
            MB
          </div>
          <div>
            <span class="fw-bold d-block fs-6 text-dark lh-sm">Admin Console</span>
            <span class="text-muted" style="font-size: 0.70rem;">Mukesh Bhattacharya</span>
          </div>
        </a>
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleAdminSidebar()"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="p-3" style="overflow-y: auto; flex: 1;">
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <a href="index.php" class="nav-link rounded-3 <?php echo ($current_page == 'index.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-speedometer2 me-2"></i> Dashboard Overview
            </a>
          </li>
          <li class="nav-item">
            <a href="courses.php" class="nav-link rounded-3 <?php echo ($current_page == 'courses.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-mortarboard me-2"></i> Computer Courses
            </a>
          </li>
          <li class="nav-item">
            <a href="tax-services.php" class="nav-link rounded-3 <?php echo ($current_page == 'tax-services.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-receipt-cutoff me-2"></i> M.B Taxation Services
            </a>
          </li>
          <li class="nav-item">
            <a href="orders.php" class="nav-link rounded-3 <?php echo ($current_page == 'orders.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-cart-check me-2"></i> Admissions & Orders
            </a>
          </li>
          <li class="nav-item">
            <a href="payments.php" class="nav-link rounded-3 <?php echo ($current_page == 'payments.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-credit-card me-2"></i> Payments Audit
            </a>
          </li>
          <li class="nav-item">
            <a href="users.php" class="nav-link rounded-3 <?php echo ($current_page == 'users.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-people me-2"></i> User Directory
            </a>
          </li>
          <li class="nav-item">
            <a href="tax-requests.php" class="nav-link rounded-3 <?php echo ($current_page == 'tax-requests.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-file-earmark-text me-2"></i> Tax & Service Requests
            </a>
          </li>
          <li class="nav-item">
            <a href="digital-services.php" class="nav-link rounded-3 <?php echo ($current_page == 'digital-services.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-printer me-2"></i> Digital Services Hub
            </a>
          </li>
          <li class="nav-item">
            <a href="products.php" class="nav-link rounded-3 <?php echo ($current_page == 'products.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-phone me-2"></i> Mobile & Gifts
            </a>
          </li>
          <li class="nav-item">
            <a href="links.php" class="nav-link rounded-3 <?php echo ($current_page == 'links.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-link-45deg me-2"></i> Official Portals
            </a>
          </li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link rounded-3 <?php echo ($current_page == 'settings.php') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="bi bi-gear me-2"></i> Business Settings
            </a>
          </li>
          <li class="nav-item mt-3 pt-3 border-top">
            <a href="../index.php" class="nav-link text-muted" target="_blank">
              <i class="bi bi-box-arrow-up-right me-2"></i> View Live Website
            </a>
          </li>
          <li class="nav-item mt-2">
            <a href="../logout.php" class="nav-link text-white bg-danger rounded-3 fw-bold text-center py-2 shadow-sm">
              <i class="bi bi-box-arrow-right me-1"></i> Logout Admin
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <!-- Main Content Container -->
    <div class="admin-main">
      <!-- Topbar -->
      <header class="admin-topbar d-flex align-items-center justify-content-between p-3 border-bottom" style="background: var(--card-bg, #ffffff);">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" onclick="toggleAdminSidebar()" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-5"></i>
          </button>
          <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5"><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Dashboard"; ?></h5>
        </div>

        <div class="d-flex align-items-center gap-3">
          <!-- Admin Avatar & Name -->
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 34px; height: 34px; font-size: 0.85rem;">
              MB
            </div>
            <div class="d-none d-sm-block text-end">
              <span class="fw-semibold small text-dark d-block lh-1"><?php echo htmlspecialchars($logged_admin['name']); ?></span>
              <span class="text-muted" style="font-size: 0.65rem;">Administrator</span>
            </div>
          </div>
          <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 d-none d-sm-inline-flex align-items-center gap-1" title="Sign Out">
            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
          </a>
        </div>
      </header>

      <!-- Page Content -->
      <div class="admin-content p-3 p-md-4">
