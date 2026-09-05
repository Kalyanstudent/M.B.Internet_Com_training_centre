<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Global Navigation Header Component (Core PHP)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

$logged_user = current_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "M B Internet And Digital Studio | Mobile & Gift House"; ?></title>

  <!-- Google Fonts: Inter & Outfit -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3.3 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Custom Stylesheet -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Bootstrap 5 JS in Head for Instant Dropdown & Interactivity -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    /* Navbar Custom Polishing */
    .site-navbar {
      z-index: 1040;
      overflow-x: clip;
    }
    .nav-link {
      white-space: nowrap;
      padding: 0.45rem 0.60rem !important;
      font-size: 0.90rem;
      border-radius: 6px;
      transition: all 0.15s ease-in-out;
    }
    .nav-link:hover {
      background-color: rgba(37, 99, 235, 0.08);
      color: #1d4ed8 !important;
    }
    .user-profile-btn {
      white-space: nowrap;
      font-size: 0.86rem;
      max-width: 190px;
      overflow: hidden;
    }
    .dropdown-menu {
      z-index: 1060 !important;
    }
  </style>
</head>
<body class="bg-body-tertiary">

  <!-- Top Announcement Bar -->
  <div class="topbar-banner py-1 px-3 d-flex justify-content-between align-items-center text-white small flex-wrap gap-2" style="background: linear-gradient(90deg, #1e40af, #2563eb);">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <span><i class="bi bi-geo-alt-fill text-warning me-1"></i> Dabadari, Paschim Medinipur, WB - 721136</span>
      <span><i class="bi bi-clock-fill text-warning me-1"></i> Open: 09:00 - 21:00 (Mon-Sun)</span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <a href="mailto:mbidsmail@gmail.com" class="text-white text-decoration-none">
        <i class="bi bi-envelope-fill me-1"></i> mbidsmail@gmail.com
      </a>
      <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none">
        <i class="bi bi-facebook me-1"></i> <span class="d-none d-sm-inline">Facebook</span>
      </a>
      <a href="tel:+919775890661" class="text-white text-decoration-none fw-semibold">
        <i class="bi bi-telephone-fill me-1 text-warning"></i> +91 97758 90661
      </a>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <nav class="navbar navbar-expand-xl sticky-top border-bottom py-2 shadow-sm bg-white site-navbar">
    <div class="container-fluid px-2 px-lg-4">
      <a class="navbar-brand d-flex align-items-center gap-2 me-2 me-lg-3 flex-shrink-0" href="index.php">
        <div class="brand-logo-badge d-flex align-items-center justify-content-center text-white fw-bold rounded-3 shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, #1e40af, #2563eb); font-size: 1.05rem; flex-shrink: 0;">
          MB
        </div>
        <div>
          <span class="fw-bold fs-6 d-block text-dark lh-1 text-nowrap">M B Internet And Digital Studio</span>
          <span class="text-muted d-none d-md-block" style="font-size: 0.66rem; letter-spacing: 0.2px;">Mobile & Gift House • M.B Taxation</span>
        </div>
      </a>

      <button class="navbar-toggler border-0 shadow-none ms-auto me-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMain">
        <!-- Clean Compact Navigation Links -->
        <ul class="navbar-nav mx-auto mb-2 mb-xl-0 fw-semibold align-items-center">
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'courses.php' || $current_page == 'course-details.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="courses.php">Courses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tax-services.php' || $current_page == 'tax-request.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="tax-services.php">Taxation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'digital-services.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="digital-services.php">Digital Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'mobile-gift-house.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="mobile-gift-house.php">Mobile & Gifts</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'important-links.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="important-links.php">Official Portals</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="about.php">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active text-primary fw-bold bg-primary-subtle' : 'text-dark'; ?>" href="contact.php">Contact</a>
          </li>
        </ul>

        <!-- Right Side: User Profile Dropdown & Authentication Controls -->
        <div class="d-flex align-items-center gap-2 mt-2 mt-xl-0 ms-xl-2 flex-shrink-0">

          <?php if (is_logged_in()): ?>
            <?php if (is_admin()): ?>
              <!-- Admin Console Dropdown with Truncated Name -->
              <div class="dropdown flex-shrink-0">
                <button class="btn btn-danger btn-sm fw-bold dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm px-2 px-sm-3 py-2 rounded-3 user-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="adminDropdownBtn">
                  <i class="bi bi-shield-lock-fill flex-shrink-0"></i>
                  <span class="d-inline-block text-truncate text-start" style="max-width: 110px; vertical-align: middle;">
                    <?php echo htmlspecialchars($logged_user['name'] ?? 'Admin'); ?>
                  </span>
                  <i class="bi bi-chevron-down ms-1 flex-shrink-0" style="font-size: 0.72rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 small py-2 mt-2" aria-labelledby="adminDropdownBtn" style="min-width: 250px;">
                  <li class="px-3 py-2 border-bottom bg-light">
                    <span class="d-block fw-bold text-dark text-truncate"><?php echo htmlspecialchars($logged_user['name'] ?? 'Mukesh Bhattacharya'); ?></span>
                    <span class="badge bg-danger mt-1" style="font-size: 0.68rem;">Administrator</span>
                  </li>
                  <li><a class="dropdown-item py-2 fw-semibold" href="admin/index.php"><i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard Overview</a></li>
                  <li><a class="dropdown-item py-2" href="admin/courses.php"><i class="bi bi-mortarboard text-success me-2"></i> Manage Courses</a></li>
                  <li><a class="dropdown-item py-2" href="admin/tax-services.php"><i class="bi bi-receipt-cutoff text-purple me-2"></i> Tax & Legal Services</a></li>
                  <li><a class="dropdown-item py-2" href="admin/tax-requests.php"><i class="bi bi-file-earmark-text text-warning me-2"></i> Tax Applications</a></li>
                  <li><a class="dropdown-item py-2" href="admin/digital-services.php"><i class="bi bi-printer text-info me-2"></i> Digital Services</a></li>
                  <li><a class="dropdown-item py-2" href="admin/products.php"><i class="bi bi-phone text-warning me-2"></i> Mobile & Gifts</a></li>
                  <li><a class="dropdown-item py-2" href="admin/links.php"><i class="bi bi-link-45deg text-info me-2"></i> Official Portals</a></li>
                  <li><a class="dropdown-item py-2" href="admin/orders.php"><i class="bi bi-cart-check text-success me-2"></i> Admissions & Orders</a></li>
                  <li><a class="dropdown-item py-2" href="admin/users.php"><i class="bi bi-people text-secondary me-2"></i> User Directory</a></li>
                  <li><a class="dropdown-item py-2" href="admin/settings.php"><i class="bi bi-gear text-dark me-2"></i> Business Settings</a></li>
                  <li><hr class="dropdown-divider my-1"></li>
                  <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout Admin</a></li>
                </ul>
              </div>
            <?php else: ?>
              <!-- User Dropdown with Truncated Name (Dashboard + Logout inside) -->
              <div class="dropdown flex-shrink-0">
                <button class="btn btn-primary btn-sm fw-bold dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm px-2 px-sm-3 py-2 rounded-3 user-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdownBtn">
                  <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" style="width: 22px; height: 22px; font-size: 0.72rem;">
                    <?php echo strtoupper(substr($logged_user['name'] ?? 'U', 0, 1)); ?>
                  </div>
                  <span class="d-inline-block text-truncate text-start" style="max-width: 110px; vertical-align: middle;">
                    <?php echo htmlspecialchars($logged_user['name'] ?? 'User'); ?>
                  </span>
                  <i class="bi bi-chevron-down ms-1 flex-shrink-0" style="font-size: 0.72rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 small py-2 mt-2" aria-labelledby="userDropdownBtn" style="min-width: 260px;">
                  <li class="px-3 py-2 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fw-bold text-dark text-truncate"><?php echo htmlspecialchars($logged_user['name']); ?></span>
                      <span class="badge bg-success" style="font-size: 0.65rem;">User Account</span>
                    </div>
                    <span class="text-muted d-block text-truncate" style="font-size: 0.75rem;"><?php echo htmlspecialchars($logged_user['email']); ?></span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">ID: <?php echo htmlspecialchars($logged_user['user_code'] ?? 'USR-101'); ?></span>
                  </li>
                  <li><a class="dropdown-item py-2 fw-semibold text-primary" href="dashboard.php"><i class="bi bi-grid-fill me-2"></i> My Dashboard</a></li>
                  <li><a class="dropdown-item py-2" href="dashboard.php#tab-courses"><i class="bi bi-mortarboard text-success me-2"></i> Enrolled Courses</a></li>
                  <li><a class="dropdown-item py-2" href="dashboard.php#tab-requests"><i class="bi bi-file-earmark-text text-info me-2"></i> Tax & ITR Requests</a></li>
                  <li><a class="dropdown-item py-2" href="dashboard.php#tab-orders"><i class="bi bi-receipt text-warning me-2"></i> Orders & Receipts</a></li>
                  <li><a class="dropdown-item py-2" href="dashboard.php#tab-profile"><i class="bi bi-person-gear text-secondary me-2"></i> Profile & Password</a></li>
                  <li><hr class="dropdown-divider my-1"></li>
                  <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <!-- Guest Login Buttons -->
            <a href="login.php" class="btn btn-outline-primary btn-sm fw-semibold rounded-3 px-3 py-2">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
            <a href="register.php" class="btn btn-primary btn-sm fw-semibold shadow-sm rounded-3 px-3 py-2">
              <i class="bi bi-person-plus-fill me-1"></i> Register
            </a>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </nav>
