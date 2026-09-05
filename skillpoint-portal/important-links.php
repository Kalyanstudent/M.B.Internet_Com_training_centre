<?php
/**
 * MB Internet And Digital Studio | Important Official Portals Directory (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Important Government & Official Links";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

$where_clauses = ["status = 'active'"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(title LIKE :search OR description LIKE :search OR category LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category !== 'all' && !empty($category)) {
    $where_clauses[] = "category = :cat";
    $params[':cat'] = $category;
}

$where_sql = implode(" AND ", $where_clauses);

// মোট সংখ্যা ও পেজ হিসাব
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM important_links WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM important_links WHERE $where_sql ORDER BY display_order ASC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$links = $stmt->fetchAll();

// Categories
$cats_stmt = $conn->query("SELECT DISTINCT category FROM important_links WHERE status = 'active' ORDER BY category ASC");
$categories = $cats_stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);">
  <div class="container">
    <h1 class="display-6 fw-bold mb-2"><i class="bi bi-shield-check me-2"></i> Important Government & Official Portals</h1>
    <p class="lead opacity-90 mb-0" style="font-size: 1.05rem;">
      Verified direct access directory for Income Tax, GST, UIDAI Aadhaar, EPFO, Passport Seva, and DigiLocker citizen portals.
    </p>
  </div>
</section>

<!-- Filter and Search Bar -->
<section class="py-3 border-bottom" style="background: var(--card-bg, #ffffff);">
  <div class="container">
    <form method="GET" action="important-links.php" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-subtle"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search portals (e.g. Aadhaar, ITR, GST, Passport)..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>
      <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
          <option value="all">All Portal Categories</option>
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
          <a href="important-links.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<!-- Portals Directory Grid -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <?php if (!empty($links)): ?>
      <div class="row g-4 mb-4">
        <?php foreach ($links as $l): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="rounded-3 bg-info-subtle text-info p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi <?php echo htmlspecialchars($l['icon'] ?? 'bi-link-45deg'); ?>"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold text-dark fs-6 mb-0"><?php echo htmlspecialchars($l['title']); ?></h5>
                    <span class="badge bg-subtle text-muted border" style="font-size: 0.7rem;"><?php echo htmlspecialchars($l['category']); ?></span>
                  </div>
                </div>

                <p class="small text-muted mb-4 flex-grow-1"><?php echo htmlspecialchars($l['description']); ?></p>

                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                  <a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm fw-semibold">
                    <?php echo htmlspecialchars($l['button_text'] ?? 'Visit Portal'); ?> <i class="bi bi-box-arrow-up-right ms-1"></i>
                  </a>
                  <span class="small text-muted" style="font-size: 0.72rem;"><i class="bi bi-patch-check-fill text-success"></i> Official</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination Controls -->
      <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
          <span class="small text-muted">Showing <strong><?php echo min($total_rows, $offset + 1); ?> - <?php echo min($total_rows, $offset + count($links)); ?></strong> of <strong><?php echo $total_rows; ?></strong> portals</span>
          <nav aria-label="Portals Pagination">
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
        <h5 class="fw-bold text-dark">No Portals Found</h5>
        <p class="small text-muted mb-3">No official links matched your search term "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
        <a href="important-links.php" class="btn btn-primary btn-sm">Clear Filter</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
