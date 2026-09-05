<?php
/**
 * MB Internet And Digital Studio | Homepage (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Home | Computer Training, M.B Taxation, Digital Services & Gift House";

// ১. পপুলার কম্পিউটার কোর্স ফেচ করা
$courses_stmt = $conn->query("SELECT * FROM courses WHERE status = 'active' ORDER BY rating DESC LIMIT 4");
$featured_courses = $courses_stmt->fetchAll();

// ২. ডিজিটাল সার্ভিসেস ফেচ করা
$digital_stmt = $conn->query("SELECT * FROM digital_services WHERE status = 'active' ORDER BY id ASC LIMIT 6");
$digital_services = $digital_stmt->fetchAll();

// ৩. মোবাইল ও গিফট প্রোডাক্ট ফেচ করা
$products_stmt = $conn->query("SELECT * FROM products WHERE status = 'active' ORDER BY id ASC LIMIT 4");
$featured_products = $products_stmt->fetchAll();

// ৪. গুরুত্বপূর্ণ সরকারি পোর্টাল লিংক ফেচ করা
$links_stmt = $conn->query("SELECT * FROM important_links WHERE status = 'active' ORDER BY display_order ASC LIMIT 6");
$important_links = $links_stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #0d9488 100%);">
  <div class="container py-4">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 mb-3 small flex-wrap">
          <span class="badge bg-warning text-dark fw-bold">Est. 23-09-2013</span>
          <span>14+ Years of Trust • Proprietor: Mukesh Bhattacharya ("MB")</span>
        </div>
        <h1 class="display-5 fw-extrabold mb-3">
          M B Internet And Digital Studio
          <span class="d-block text-warning fs-3 mt-1 fw-bold">Mobile & Gift House • M.B Taxation</span>
        </h1>
        <p class="lead opacity-90 mb-4" style="font-size: 1.1rem; line-height: 1.6;">
          Your trusted institution for <strong>Career-Oriented Computer Education</strong> (TallyPrime + GST, Advanced Excel, Web Dev), <strong>Income Tax / ITR & GST Compliance (M.B Taxation)</strong>, Online Competitive Exam Form Fill-up, High-Speed Color Printing, Mobile & Consumer Electronics, Computer Accessories & Study Materials.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="courses.php" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow">
            <i class="bi bi-mortarboard-fill me-1"></i> Computer Courses
          </a>
          <a href="tax-services.php" class="btn btn-outline-light btn-lg fw-semibold px-4">
            <i class="bi bi-file-earmark-lock-fill me-1"></i> M.B Taxation (ITR/GST)
          </a>
          <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-lg fw-semibold text-primary px-3" title="Visit our Facebook Page">
            <i class="bi bi-facebook fs-5"></i>
          </a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="p-4 rounded-4 shadow-lg border border-white border-opacity-20" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);">
          <h5 class="fw-bold text-warning mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-stars"></i> Our Core Business Verticals
          </h5>
          <div class="d-flex flex-column gap-3 small">
            <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-laptop fs-4 text-success"></i>
              <div>
                <strong class="d-block text-white">1. Computer Training Centre</strong>
                <span class="text-white-50">Basic, Word, Excel, TallyPrime + GST, Web Development</span>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-shield-check fs-4 text-info"></i>
              <div>
                <strong class="d-block text-white">2. M.B Taxation & Legal Services</strong>
                <span class="text-white-50">ITR-1/2/4, GST Registration & Returns, PAN, TDS</span>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-printer fs-4 text-primary"></i>
              <div>
                <strong class="d-block text-white">3. Digital & Citizen Services Hub</strong>
                <span class="text-white-50">Online Exam Form Filling, Laser Printing, PVC Smart Cards</span>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white bg-opacity-10">
              <i class="bi bi-phone fs-4 text-warning"></i>
              <div>
                <strong class="d-block text-white">4. Mobile, Electronics & Gift House</strong>
                <span class="text-white-50">Mobile Repair, Chargers, Cables, Custom Photo Mugs & Study Kits</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Facebook Showcase Banner -->
<section class="py-3 border-bottom bg-white" style="background: var(--card-bg, #ffffff);">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
      <span class="small fw-semibold text-dark">Dabadari, Paschim Medinipur, West Bengal - 721136 • Open: 09:00 - 21:00 (Mon-Sun)</span>
    </div>
    <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm fw-bold">
      <i class="bi bi-facebook me-1"></i> Browse Facebook Photo Gallery
    </a>
  </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="badge bg-primary-subtle text-primary fw-bold mb-1">Career Training</span>
        <h2 class="fw-bold text-dark fs-3 mb-0">Popular Computer Courses</h2>
      </div>
      <a href="courses.php" class="btn btn-outline-primary btn-sm fw-bold">View All Courses <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
      <?php if (!empty($featured_courses)): ?>
        <?php foreach ($featured_courses as $c): ?>
          <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="badge bg-subtle text-dark border small"><?php echo htmlspecialchars($c['category']); ?></span>
                  <?php if (!empty($c['badge'])): ?>
                    <span class="badge bg-warning-subtle text-warning border small"><?php echo htmlspecialchars($c['badge']); ?></span>
                  <?php endif; ?>
                </div>

                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary mb-3" style="width: 48px; height: 48px; font-size: 1.5rem;">
                  <i class="bi <?php echo htmlspecialchars($c['icon'] ?? 'bi-laptop'); ?>"></i>
                </div>

                <h5 class="fw-bold text-dark fs-6 mb-2"><?php echo htmlspecialchars($c['title']); ?></h5>
                <p class="small text-muted mb-3 flex-grow-1"><?php echo htmlspecialchars($c['short_desc']); ?></p>

                <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                  <span><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($c['duration']); ?></span>
                  <span><i class="bi bi-star-fill text-warning me-1"></i> <?php echo htmlspecialchars($c['rating']); ?></span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                  <span class="fw-bold text-primary fs-5">₹<?php echo number_format($c['price']); ?></span>
                  <?php if (is_admin()): ?>
                    <a href="admin/courses.php" class="btn btn-warning btn-sm fw-bold">
                      <i class="bi bi-gear-fill me-1"></i> Manage (Admin)
                    </a>
                  <?php else: ?>
                    <a href="checkout.php?course_id=<?php echo $c['id']; ?>" class="btn btn-primary btn-sm fw-semibold">
                      Enroll Now <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- M.B Taxation Services Showcase -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-warning text-dark fw-bold mb-2">Professional Compliance</span>
        <h2 class="fw-bold fs-3 mb-2"><i class="bi bi-file-earmark-lock-fill me-2"></i> M.B Taxation & Legal Services</h2>
        <p class="opacity-90 mb-0">
          Fast and reliable filing for Salaried ITR-1, Business ITR-4, Monthly/Quarterly GST returns, Trade License, and PAN Services. 100% confidential and compliant.
        </p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="tax-services.php" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow">
          <i class="bi bi-send-check-fill me-1"></i> Explore Tax Services
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Digital Services Hub -->
<section class="py-5" style="background-color: var(--card-bg, #ffffff);">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="badge bg-info-subtle text-info fw-bold mb-1">Instant Services</span>
        <h2 class="fw-bold text-dark fs-3 mb-0">Digital & Citizen Services Hub</h2>
      </div>
      <a href="digital-services.php" class="btn btn-outline-primary btn-sm fw-bold">View All Services <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
      <?php if (!empty($digital_services)): ?>
        <?php foreach ($digital_services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="badge bg-subtle text-dark border small"><?php echo htmlspecialchars($s['category'] ?? 'Digital'); ?></span>
                  <span class="badge bg-primary-subtle text-primary border small"><?php echo htmlspecialchars($s['price_text']); ?></span>
                </div>
                <h5 class="fw-bold text-dark fs-6 mb-2"><?php echo htmlspecialchars($s['title']); ?></h5>
                <p class="small text-muted mb-3 flex-grow-1"><?php echo htmlspecialchars($s['description']); ?></p>
                <a href="https://wa.me/919775890661?text=I%20am%20interested%20in%20<?php echo urlencode($s['title']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm mt-auto">
                  <i class="bi bi-whatsapp me-1"></i> WhatsApp Inquiry
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Mobile & Gift House Catalog Preview -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="badge bg-warning-subtle text-warning fw-bold mb-1">Shop & Gifts</span>
        <h2 class="fw-bold text-dark fs-3 mb-0">Mobile, Electronics & Gift House</h2>
      </div>
      <a href="mobile-gift-house.php" class="btn btn-outline-secondary btn-sm fw-bold">Browse Catalog <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
      <?php if (!empty($featured_products)): ?>
        <?php foreach ($featured_products as $p): ?>
          <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <span class="badge bg-subtle text-dark border small align-self-start mb-2"><?php echo htmlspecialchars($p['category']); ?></span>
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning mb-3" style="width: 46px; height: 46px; font-size: 1.4rem;">
                  <i class="bi <?php echo htmlspecialchars($p['icon'] ?? 'bi-box-seam'); ?>"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($p['name']); ?></h6>
                <p class="small text-muted mb-3 flex-grow-1"><?php echo htmlspecialchars($p['description']); ?></p>
                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                  <span class="fw-bold text-primary"><?php echo !empty($p['price']) ? '₹' . number_format($p['price']) : 'In-Store'; ?></span>
                  <a href="https://wa.me/919775890661?text=Inquiry%20for%20<?php echo urlencode($p['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-whatsapp"></i> Inquire
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Official Portals Directory Preview -->
<section class="py-5" style="background-color: var(--card-bg, #ffffff);">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <span class="badge bg-info-subtle text-info fw-bold mb-1">Direct Access</span>
        <h2 class="fw-bold text-dark fs-3 mb-0">Important Government & Citizen Portals</h2>
      </div>
      <a href="important-links.php" class="btn btn-outline-primary btn-sm fw-bold">View All Portals <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-3">
      <?php if (!empty($important_links)): ?>
        <?php foreach ($important_links as $l): ?>
          <div class="col-md-6 col-lg-4">
            <div class="p-3 bg-subtle rounded-4 border d-flex align-items-center justify-content-between">
              <div>
                <strong class="d-block text-dark small"><?php echo htmlspecialchars($l['title']); ?></strong>
                <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($l['category']); ?></span>
              </div>
              <a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary py-1 px-2" style="font-size: 0.75rem;">
                Visit <i class="bi bi-box-arrow-up-right ms-1"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
