<?php
/**
 * MB Internet And Digital Studio | M.B Taxation & Legal Services (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Tax & Legal Services | M.B Taxation";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

$where_clauses = ["status = 'active'"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(title LIKE :search OR short_desc LIKE :search OR category LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category !== 'all' && !empty($category)) {
    $where_clauses[] = "category = :cat";
    $params[':cat'] = $category;
}

$where_sql = implode(" AND ", $where_clauses);

// Count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM tax_services WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM tax_services WHERE $where_sql ORDER BY id ASC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$services = $stmt->fetchAll();

// Categories
$cats_stmt = $conn->query("SELECT DISTINCT category FROM tax_services WHERE status = 'active' ORDER BY category ASC");
$categories = $cats_stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-20 text-white small fw-bold mb-2">
          <i class="bi bi-patch-check-fill text-warning"></i> M.B Taxation • Professional Tax & Legal Division
        </div>
        <h1 class="display-6 fw-bold mb-2"><i class="bi bi-file-earmark-lock-fill me-2"></i> Income Tax & Legal Services</h1>
        <p class="lead opacity-90 mb-3" style="font-size: 1.05rem;">
          Accurate, legally compliant tax filing and corporate compliance by experienced tax practitioners. Salaried Form 16, Business ITR-4, GST monthly/quarterly returns, PAN services, and trade licenses.
        </p>
        <a href="tax-request.php" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow">
          <i class="bi bi-send-check-fill me-1"></i> Submit Tax Filing Request
        </a>
      </div>
      <div class="col-lg-4 text-center">
        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
          <h4 class="fw-bold mb-1">24-48 Hours</h4>
          <span class="small opacity-75 d-block mb-3">Fast Turnaround for ITR Acknowledgement</span>
          <span class="badge bg-success text-white px-3 py-2 fs-6">100% Encrypted Document Vault</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filter & Search Bar -->
<section class="py-3 border-bottom bg-white" style="background: var(--card-bg, #ffffff);">
  <div class="container">
    <form method="GET" action="tax-services.php" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search tax services (e.g. ITR Filing, GST, TDS, PAN Card)..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>
      <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
          <option value="all">All Tax Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary flex-fill">Search</button>
        <?php if (!empty($search) || $category !== 'all'): ?>
          <a href="tax-services.php" class="btn btn-outline-secondary" title="Clear Search"><i class="bi bi-x-lg"></i></a>
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
                  <span class="badge bg-subtle text-dark border small"><?php echo htmlspecialchars($s['category'] ?? 'Taxation'); ?></span>
                  <span class="badge bg-purple-subtle text-purple border small" style="color: #7c3aed; background: #f3e8ff;">M.B Taxation</span>
                </div>

                <div class="d-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 46px; height: 46px; font-size: 1.4rem; background: #f3e8ff; color: #7c3aed;">
                  <i class="bi <?php echo htmlspecialchars($s['icon'] ?? 'bi-file-earmark-text'); ?>"></i>
                </div>

                <h5 class="fw-bold text-dark fs-6 mb-2"><?php echo htmlspecialchars($s['title']); ?></h5>
                <p class="small text-muted mb-3 flex-grow-1"><?php echo htmlspecialchars($s['short_desc']); ?></p>

                <div class="d-flex justify-content-between align-items-center small text-muted mb-3 pb-2 border-bottom">
                  <span><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($s['turnaround'] ?? '24-48 Hours'); ?></span>
                  <span class="fw-bold text-primary fs-6"><?php echo !empty($s['price']) ? '₹' . number_format($s['price']) : 'Affordable Fee'; ?></span>
                </div>

                <?php if (is_admin()): ?>
                  <a href="admin/tax-services.php" class="btn btn-outline-warning btn-sm w-100 fw-semibold mt-auto">
                    <i class="bi bi-gear-fill me-1"></i> Manage Service (Admin)
                  </a>
                <?php else: ?>
                  <a href="tax-request.php?service=<?php echo urlencode($s['title']); ?>" class="btn btn-outline-primary btn-sm w-100 fw-semibold mt-auto">
                    <i class="bi bi-pencil-square me-1"></i> Apply / Submit Request
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination Controls -->
      <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
          <span class="small text-muted">Showing <strong><?php echo min($total_rows, $offset + 1); ?> - <?php echo min($total_rows, $offset + count($services)); ?></strong> of <strong><?php echo $total_rows; ?></strong> tax services</span>
          <nav aria-label="Tax Services Pagination">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>">
                  <i class="bi bi-chevron-left"></i> Prev
                </a>
              </li>
              <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
                  <a class="page-link" href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $p; ?>">
                    <?php echo $p; ?>
                  </a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>">
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
        <h5 class="fw-bold text-dark">No Tax Services Found</h5>
        <p class="small text-muted mb-3">No services matched your query "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
        <a href="tax-services.php" class="btn btn-primary btn-sm">Clear Filter</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
