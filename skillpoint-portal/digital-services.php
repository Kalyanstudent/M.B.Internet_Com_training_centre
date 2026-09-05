<?php
/**
 * MB Internet And Digital Studio | Digital & Citizen Online Services (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Digital & Citizen Online Services";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

$where_clauses = ["status = 'active'"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(title LIKE :search OR description LIKE :search OR category LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_sql = implode(" AND ", $where_clauses);

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM digital_services WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM digital_services WHERE $where_sql ORDER BY id ASC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$services = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #0d9488 100%);">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h1 class="display-6 fw-bold mb-2"><i class="bi bi-printer-fill me-2"></i> Digital & Citizen Services Hub</h1>
        <p class="lead opacity-90 mb-3" style="font-size: 1.05rem;">
          One-stop studio for online competitive exam form filling, high-speed color laser printing, document scanning, digital passport size photography, utility bill payments, and smart plastic PVC cards.
        </p>
        <a href="contact.php" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow">
          <i class="bi bi-geo-alt-fill me-1"></i> Visit Our Studio
        </a>
      </div>
      <div class="col-lg-4 text-center">
        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
          <h4 class="fw-bold mb-1">High-Speed Laser</h4>
          <span class="small opacity-75 d-block mb-3">Color & B/W Printing up to 1000+ pages</span>
          <span class="badge bg-warning text-dark px-3 py-2 fs-6">Instant Passport Photos in 5 Mins</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Search Bar -->
<section class="py-3 border-bottom" style="background: var(--card-bg, #ffffff);">
  <div class="container">
    <form method="GET" action="digital-services.php" class="row g-2 align-items-center">
      <div class="col-md-10">
        <div class="input-group">
          <span class="input-group-text bg-subtle"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search digital services (e.g. Exam Form, PVC Card, Passport Photo, Color Print)..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary flex-fill">Search</button>
        <?php if (!empty($search)): ?>
          <a href="digital-services.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<!-- Services Grid -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <?php if (!empty($services)): ?>
      <div class="row g-4 mb-4">
        <?php foreach ($services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="badge bg-subtle text-dark border small"><?php echo htmlspecialchars($s['category'] ?? 'General Service'); ?></span>
                  <span class="badge bg-primary-subtle text-primary border small"><?php echo htmlspecialchars($s['price_text']); ?></span>
                </div>

                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary mb-3" style="width: 46px; height: 46px; font-size: 1.4rem;">
                  <i class="bi <?php echo htmlspecialchars($s['icon'] ?? 'bi-file-earmark-text'); ?>"></i>
                </div>

                <h5 class="fw-bold text-dark fs-6 mb-2"><?php echo htmlspecialchars($s['title']); ?></h5>
                <p class="small text-muted mb-4 flex-grow-1"><?php echo htmlspecialchars($s['description']); ?></p>

                <div class="d-flex gap-2 mt-auto pt-3 border-top">
                  <a href="https://wa.me/919775890661?text=I%20am%20interested%20in%20<?php echo urlencode($s['title']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm flex-fill">
                    <i class="bi bi-whatsapp me-1"></i> Inquire
                  </a>
                  <a href="contact.php" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-geo-alt me-1"></i> Studio Visit
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination Controls -->
      <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
          <span class="small text-muted">Showing <strong><?php echo min($total_rows, $offset + 1); ?> - <?php echo min($total_rows, $offset + count($services)); ?></strong> of <strong><?php echo $total_rows; ?></strong> services</span>
          <nav aria-label="Digital Services Pagination">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                  <i class="bi bi-chevron-left"></i> Prev
                </a>
              </li>
              <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
                  <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>">
                    <?php echo $p; ?>
                  </a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
                  Next <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-search display-3 text-muted mb-3 d-block"></i>
        <h5 class="fw-bold text-dark">No Digital Services Found</h5>
        <p class="small text-muted mb-3">No services matched your search term "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
        <a href="digital-services.php" class="btn btn-primary btn-sm">Clear Filter</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
