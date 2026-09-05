<?php
/**
 * MB Internet And Digital Studio | About Us & Mukesh Da (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "About M B Internet And Digital Studio | M.B Taxation";

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-warning text-dark fw-bold mb-2"><i class="bi bi-patch-check-fill"></i> Incorporated on 23-09-2013</span>
        <h1 class="display-6 fw-bold mb-2">About M B Internet And Digital Studio</h1>
        <p class="lead opacity-90 mb-0" style="font-size: 1.05rem;">
          A 14+ Years Trusted Institution in Dabadari, Paschim Medinipur offering Computer Training, M.B Taxation & Legal Services, Digital Citizen Services, Consumer Electronics & Personalized Gifts.
        </p>
      </div>
      <div class="col-lg-4 text-center">
        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
          <div class="display-6 fw-bold mb-0">14+ Years</div>
          <span class="small opacity-75">Serving Dabadari & Paschim Medinipur</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Story & Profile Section -->
<section class="py-5" style="background-color: var(--card-bg, #ffffff);">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 text-center">
        <div class="p-4 rounded-4 shadow-sm border bg-subtle">
          <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold display-4 mb-3" style="width: 110px; height: 110px;">
            MB
          </div>
          <h4 class="fw-bold text-dark mb-1">Mukesh Bhattacharya</h4>
          <span class="text-primary fw-semibold small d-block mb-3">Proprietor, Tax Practitioner & Senior Instructor</span>
          <p class="small text-muted mb-4">
            Committed educator and digital services consultant dedicated to youth computer literacy, tax compliance under <strong>M.B Taxation</strong>, and citizen technology support.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-3">
              <i class="bi bi-facebook me-1"></i> Facebook Gallery
            </a>
            <a href="https://wa.me/919775890661" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm px-3">
              <i class="bi bi-whatsapp me-1"></i> WhatsApp
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <h3 class="fw-bold text-dark mb-3">Shop & Firm Overview</h3>
        <p class="text-muted mb-3">
          <strong>M B Internet And Digital Studio, Mobile & Gift House</strong> (with Tax & Legal services operating under <strong>M.B Taxation</strong>) is a 14+ years established Proprietorship firm incorporated on <strong>23-09-2013</strong>, having its registered office located at <strong>Dabadari, Paschim Medinipur, West Bengal - 721136</strong>.
        </p>
        <p class="text-muted mb-4">
          The major activity of our establishment is Services, Sub-classified into repair of computers and personal and household goods, and is primarily engaged in the sale of Consumer Electronics, Mobiles, Computer Accessories & Study Materials, alongside Computer Education & Training, Digital Online Services, and Income Tax/GST filing.
        </p>

        <div class="row g-3">
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-subtle border">
              <h6 class="fw-bold text-dark mb-1"><i class="bi bi-laptop text-success me-1"></i> Computer Training Centre</h6>
              <span class="small text-muted">TallyPrime, Advanced Excel, Web & Office skills.</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-subtle border">
              <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-purple me-1"></i> M.B Taxation & Legal</h6>
              <span class="small text-muted">Accurate, safe, and confidential ITR, GST, & PAN.</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-subtle border">
              <h6 class="fw-bold text-dark mb-1"><i class="bi bi-printer text-primary me-1"></i> Digital Services Hub</h6>
              <span class="small text-muted">Online exam forms, color printing, PVC cards.</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-subtle border">
              <h6 class="fw-bold text-dark mb-1"><i class="bi bi-phone text-warning me-1"></i> Mobile & Gift House</h6>
              <span class="small text-muted">Consumer electronics, cables, mugs, & study material.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Official Business Profile Card -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
      <div class="card-header bg-transparent border-bottom p-4">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building-check text-primary me-2"></i> Official Enterprise Details</h5>
      </div>
      <div class="card-body p-4">
        <div class="row g-4">
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Firm Name:</span>
            <strong class="text-dark">M B Internet And Digital Studio</strong>
            <span class="text-muted small d-block mt-1">Mobile & Gift House • M.B Taxation</span>
          </div>
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Incorporation Date:</span>
            <strong class="text-dark">23rd September 2013 (14+ Years)</strong>
            <span class="badge bg-success mt-1">Active Proprietorship</span>
          </div>
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Registered Office:</span>
            <strong class="text-dark">Dabadari, Paschim Medinipur, WB - 721136</strong>
            <span class="text-muted small d-block">Geo Plus Code: CJ9J+M6X, Dabadari</span>
          </div>
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Official Email:</span>
            <strong class="text-primary">mbidsmail@gmail.com</strong>
          </div>
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Official Mobile & WhatsApp:</span>
            <strong class="text-dark"><a href="tel:+919775890661" class="text-decoration-none text-dark">+91 97758 90661</a></strong>
          </div>
          <div class="col-md-4">
            <span class="text-muted small d-block mb-1">Opening Hours:</span>
            <strong class="text-dark">09:00 - 21:00 (Monday to Sunday)</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
