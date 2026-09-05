<?php
/**
 * MB Internet And Digital Studio | Common Footer (Core PHP)
 */
?>
  <!-- Footer -->
  <footer class="pt-5 pb-3 border-top mt-5 bg-white" style="background-color: var(--card-bg, #ffffff);">
    <div class="container">
      <div class="row g-4 mb-4">
        <!-- Brand Info -->
        <div class="col-lg-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="brand-logo-badge d-flex align-items-center justify-content-center text-white fw-bold rounded-3" style="width: 38px; height: 38px; background: linear-gradient(135deg, #1e40af, #2563eb);">
              MB
            </div>
            <div>
              <span class="fw-bold fs-6 d-block text-dark">M B Internet And Digital Studio</span>
              <span class="text-muted small" style="font-size: 0.72rem;">Mobile & Gift House • M.B Taxation</span>
            </div>
          </div>
          <p class="text-muted small mb-2">
            A 14+ years trusted Proprietorship establishment (Inc. 23-09-2013) delivering professional Computer Education & Training, Income Tax & GST Consultancy (M.B Taxation), Digital Online Services, Mobile & Consumer Electronics, Computer Accessories & Study Materials.
          </p>
          <div class="small text-muted mb-3">
            <i class="bi bi-clock-fill text-warning me-1"></i> <strong>Hours:</strong> 09:00 - 21:00 (Open 7 Days a Week)
          </div>
          <div class="d-flex gap-2">
            <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Facebook">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="https://wa.me/919775890661" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
            <a href="mailto:mbidsmail@gmail.com" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Email">
              <i class="bi bi-envelope-fill"></i>
            </a>
          </div>
        </div>

        <!-- Core Verticals -->
        <div class="col-6 col-lg-2">
          <h6 class="fw-bold text-dark mb-3">Core Verticals</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2 text-muted">
            <li><a href="courses.php" class="text-decoration-none text-muted hover-primary">Computer Training</a></li>
            <li><a href="tax-services.php" class="text-decoration-none text-muted hover-primary">M.B Taxation (ITR/GST)</a></li>
            <li><a href="digital-services.php" class="text-decoration-none text-muted hover-primary">Digital Services Hub</a></li>
            <li><a href="mobile-gift-house.php" class="text-decoration-none text-muted hover-primary">Mobile & Gift House</a></li>
            <li><a href="important-links.php" class="text-decoration-none text-muted hover-primary">Official Portals</a></li>
          </ul>
        </div>

        <!-- Quick Navigation -->
        <div class="col-6 col-lg-2">
          <h6 class="fw-bold text-dark mb-3">Quick Navigation</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2 text-muted">
            <li><a href="about.php" class="text-decoration-none text-muted hover-primary">About Mukesh Da</a></li>
            <li><a href="contact.php" class="text-decoration-none text-muted hover-primary">Studio Location</a></li>
            <?php if (is_logged_in()): ?>
              <li><a href="dashboard.php" class="text-decoration-none text-muted hover-primary">Student Dashboard</a></li>
              <li><a href="logout.php" class="text-decoration-none text-danger fw-semibold">Logout</a></li>
            <?php else: ?>
              <li><a href="login.php" class="text-decoration-none text-muted hover-primary">Sign In</a></li>
              <li><a href="register.php" class="text-decoration-none text-muted hover-primary">New Admission</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="col-lg-4">
          <h6 class="fw-bold text-dark mb-3">Registered Office Details</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2 text-muted">
            <li class="d-flex gap-2">
              <i class="bi bi-person-badge-fill text-primary"></i>
              <span><strong>Proprietor:</strong> Mukesh Bhattacharya</span>
            </li>
            <li class="d-flex gap-2">
              <i class="bi bi-geo-alt-fill text-primary"></i>
              <span>Dabadari, Paschim Medinipur, West Bengal - 721136, India<br><small class="text-muted">(Plus Code: CJ9J+M6X, Dabadari)</small></span>
            </li>
            <li class="d-flex gap-2">
              <i class="bi bi-telephone-fill text-primary"></i>
              <span><a href="tel:+919775890661" class="text-decoration-none text-dark fw-bold">+91 97758 90661</a></span>
            </li>
            <li class="d-flex gap-2">
              <i class="bi bi-envelope-fill text-primary"></i>
              <span>mbidsmail@gmail.com</span>
            </li>
          </ul>
        </div>
      </div>

      <hr class="opacity-10 my-3">

      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted">
        <div>&copy; 2013 - <?php echo date('Y'); ?> <strong>M B Internet And Digital Studio</strong>. All rights reserved.</div>
        <div class="mt-2 mt-sm-0">
          <span class="badge bg-primary-subtle text-primary border me-1">Govt Verified Est. 2013</span>
          <span class="badge bg-success-subtle text-success border">14+ Years of Trust</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- WhatsApp Floating Action Button -->
  <a href="https://wa.me/919775890661?text=Hello%20Mukesh%20Da,%20I%20want%20to%20know%20more%20about%20your%20services" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center" style="bottom: 24px; right: 24px; width: 52px; height: 52px; z-index: 1040; font-size: 1.5rem;" title="Chat with Mukesh Da on WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
</body>
</html>
