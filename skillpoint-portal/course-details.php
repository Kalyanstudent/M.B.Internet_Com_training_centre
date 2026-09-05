<?php
/**
 * MB Internet And Digital Studio | Course Details & Syllabus (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
} elseif (!empty($slug)) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
} else {
    header("Location: courses.php");
    exit;
}

$course = $stmt->fetch();
if (!$course) {
    header("Location: courses.php");
    exit;
}

$page_title = $course['title'];

// JSON ফিল্ডগুলো ডিকোড করা
$learnings = !empty($course['learnings']) ? json_decode($course['learnings'], true) : [];
$syllabus = !empty($course['syllabus']) ? json_decode($course['syllabus'], true) : [];
$benefits = !empty($course['benefits']) ? json_decode($course['benefits'], true) : [];
$batches = !empty($course['batches']) ? json_decode($course['batches'], true) : [];

include __DIR__ . '/includes/header.php';
?>

<!-- Course Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 60%, #10b981 100%);">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-white opacity-75 text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="courses.php" class="text-white opacity-75 text-decoration-none">Courses</a></li>
        <li class="breadcrumb-item active text-white fw-semibold" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
      </ol>
    </nav>

    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="d-flex flex-wrap gap-2 mb-2">
          <span class="badge bg-white text-primary fw-bold"><?php echo htmlspecialchars($course['category']); ?></span>
          <?php if (!empty($course['badge'])): ?>
            <span class="badge bg-warning text-dark fw-bold"><?php echo htmlspecialchars($course['badge']); ?></span>
          <?php endif; ?>
        </div>
        <h1 class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
        <p class="lead opacity-90 mb-4" style="font-size: 1.05rem;">
          <?php echo htmlspecialchars($course['overview'] ?? $course['short_desc']); ?>
        </p>
        <div class="d-flex flex-wrap gap-3 small">
          <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1 rounded-pill">
            <i class="bi bi-clock-history text-warning"></i> Duration: <?php echo htmlspecialchars($course['duration']); ?> (<?php echo htmlspecialchars($course['hours'] ?? '45 Hours'); ?>)
          </span>
          <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1 rounded-pill">
            <i class="bi bi-bar-chart-fill text-warning"></i> Level: <?php echo htmlspecialchars($course['level'] ?? 'Beginner to Pro'); ?>
          </span>
          <span class="d-flex align-items-center gap-1 bg-white bg-opacity-10 px-3 py-1 rounded-pill">
            <i class="bi bi-person-check-fill text-warning"></i> Eligibility: <?php echo htmlspecialchars($course['eligibility'] ?? 'Open to All'); ?>
          </span>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
          <div class="card-body p-4 text-center">
            <span class="text-muted small d-block mb-1">Total Course Fee</span>
            <div class="d-flex justify-content-center align-items-baseline gap-2 mb-3">
              <span class="display-6 fw-bold text-primary">₹<?php echo number_format($course['price']); ?></span>
              <?php if (!empty($course['original_price'])): ?>
                <span class="text-muted text-decoration-line-through fs-5">₹<?php echo number_format($course['original_price']); ?></span>
              <?php endif; ?>
            </div>
            <?php if (is_admin()): ?>
              <a href="admin/courses.php" class="btn btn-warning btn-lg w-100 fw-bold shadow mb-2">
                <i class="bi bi-gear-fill me-1"></i> Manage Course (Admin)
              </a>
            <?php else: ?>
              <a href="checkout.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary btn-lg w-100 fw-bold shadow mb-2">
                <i class="bi bi-mortarboard-fill me-1"></i> Enroll & Start Learning
              </a>
            <?php endif; ?>
            <span class="small text-muted d-block"><i class="bi bi-shield-check text-success me-1"></i> ISO Certificate Included</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Curriculum & Details Body -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <div class="row g-4">
      <!-- Left Column: Syllabus & Learnings -->
      <div class="col-lg-8">
        <!-- What You Will Learn -->
        <?php if (!empty($learnings)): ?>
          <div class="p-4 bg-white rounded-4 border shadow-sm mb-4" style="background-color: var(--card-bg, #ffffff);">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-check2-circle text-success me-2"></i> What You Will Master</h4>
            <div class="row g-2">
              <?php foreach ($learnings as $item): ?>
                <div class="col-md-6 d-flex align-items-start gap-2 small text-muted">
                  <i class="bi bi-check-circle-fill text-success mt-1"></i>
                  <span><?php echo htmlspecialchars($item); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Detailed Syllabus Accordion -->
        <div class="p-4 bg-white rounded-4 border shadow-sm mb-4" style="background-color: var(--card-bg, #ffffff);">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i> Course Syllabus</h4>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Print Syllabus
            </button>
          </div>

          <?php if (!empty($syllabus)): ?>
            <div class="accordion" id="syllabusAccordion">
              <?php foreach ($syllabus as $idx => $mod): ?>
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                  <h2 class="accordion-header" id="heading<?php echo $idx; ?>">
                    <button class="accordion-button <?php echo ($idx > 0) ? 'collapsed' : ''; ?> fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $idx; ?>">
                      <span class="badge bg-primary me-2"><?php echo htmlspecialchars($mod['module'] ?? ('Module ' . ($idx + 1))); ?></span>
                      <?php echo htmlspecialchars($mod['title'] ?? ''); ?>
                    </button>
                  </h2>
                  <div id="collapse<?php echo $idx; ?>" class="accordion-collapse collapse <?php echo ($idx === 0) ? 'show' : ''; ?>" data-bs-parent="#syllabusAccordion">
                    <div class="accordion-body bg-subtle p-3">
                      <ul class="mb-0 small text-muted">
                        <?php if (!empty($mod['topics']) && is_array($mod['topics'])): ?>
                          <?php foreach ($mod['topics'] as $topic): ?>
                            <li class="mb-1"><?php echo htmlspecialchars($topic); ?></li>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </ul>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted small mb-0">Practical hands-on syllabus designed for industry employment.</p>
          <?php endif; ?>
        </div>

        <!-- Course Benefits -->
        <?php if (!empty($benefits)): ?>
          <div class="p-4 bg-white rounded-4 border shadow-sm" style="background-color: var(--card-bg, #ffffff);">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i> Student Benefits</h4>
            <div class="row g-3">
              <?php foreach ($benefits as $b): ?>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-subtle border d-flex align-items-center gap-2 small">
                    <i class="bi bi-award-fill text-primary fs-5"></i>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($b); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Batch Timings & Instructor -->
      <div class="col-lg-4">
        <!-- Available Batches -->
        <div class="p-4 bg-white rounded-4 border shadow-sm mb-4" style="background-color: var(--card-bg, #ffffff);">
          <h5 class="fw-bold text-dark mb-3"><i class="bi bi-calendar3 text-primary me-2"></i> Batch Schedules</h5>
          <?php if (!empty($batches)): ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($batches as $b): ?>
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-subtle border">
                  <div>
                    <strong class="d-block text-dark small"><?php echo htmlspecialchars($b['type'] ?? 'Regular Batch'); ?></strong>
                    <span class="small text-muted"><?php echo htmlspecialchars($b['time'] ?? 'Flexible Timings'); ?></span>
                  </div>
                  <span class="badge bg-success-subtle text-success border small"><?php echo htmlspecialchars($b['seats'] ?? 'Available'); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="small text-muted">Morning, Afternoon, and Evening batches available Monday to Saturday.</p>
          <?php endif; ?>
        </div>

        <!-- Instructor Info -->
        <div class="p-4 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3 mb-2" style="width: 60px; height: 60px;">
            MB
          </div>
          <h6 class="fw-bold text-dark mb-1">Mukesh Bhattacharya</h6>
          <span class="small text-muted d-block mb-3">Founder & Senior Technical Instructor</span>
          <p class="small text-muted mb-3">10+ years experience in computer applications, accounting systems, and taxation compliance.</p>
          <a href="contact.php" class="btn btn-outline-primary btn-sm w-100">
            <i class="bi bi-chat-dots-fill me-1"></i> Inquire About Batch
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
