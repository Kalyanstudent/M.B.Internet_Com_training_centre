<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House Catalog (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Mobile & Gift House";

$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 8;

$where_clauses = ["status = 'active'"];
$params = [];

if ($category === 'mobile') {
    $where_clauses[] = "category = 'Mobile Accessories'";
} elseif ($category === 'gifts') {
    $where_clauses[] = "category = 'Personalized Gifts'";
}

if (!empty($search)) {
    $where_clauses[] = "(name LIKE :search OR description LIKE :search OR code LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_sql = implode(" AND ", $where_clauses);

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM products WHERE $where_sql ORDER BY id ASC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h1 class="display-6 fw-bold mb-2"><i class="bi bi-gift-fill me-2"></i> Mobile & Gift House</h1>
        <p class="lead opacity-90 mb-3" style="font-size: 1.05rem;">
          Genuine branded mobile chargers, durable braided cables, earphones, neckbands, along with custom printed photo mugs, magic cups, wooden photo frames, and birthday gift items.
        </p>
        <a href="https://wa.me/919775890661?text=I%20want%20to%20order%20a%20custom%20gift%20/%20mobile%20item" target="_blank" rel="noopener noreferrer" class="btn btn-dark btn-lg fw-bold px-4 shadow">
          <i class="bi bi-whatsapp me-1 text-success"></i> Custom Gift Inquiry on WhatsApp
        </a>
      </div>
      <div class="col-lg-4 text-center">
        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
          <h4 class="fw-bold mb-1">Custom Photo Gifts</h4>
          <span class="small opacity-75 d-block mb-3">Mugs, Wooden Frames & Keychains in 24 Hrs</span>
          <span class="badge bg-warning text-dark px-3 py-2 fs-6">Branded Mobile Accessories</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filter Tabs and Search -->
<section class="py-3 border-bottom" style="background: var(--card-bg, #ffffff);">
  <div class="container">
    <form method="GET" action="mobile-gift-house.php" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="d-flex flex-wrap gap-2">
          <a href="mobile-gift-house.php" class="btn btn-sm <?php echo ($category === 'all') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'; ?>">
            All Items
          </a>
          <a href="mobile-gift-house.php?category=mobile" class="btn btn-sm <?php echo ($category === 'mobile') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'; ?>">
            <i class="bi bi-phone me-1"></i> Mobile Accessories
          </a>
          <a href="mobile-gift-house.php?category=gifts" class="btn btn-sm <?php echo ($category === 'gifts') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'; ?>">
            <i class="bi bi-gift me-1"></i> Personalized Gifts
          </a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="input-group input-group-sm">
          <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
          <input type="text" name="search" class="form-control" placeholder="Search catalog items..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-primary">Search</button>
          <?php if (!empty($search)): ?>
            <a href="mobile-gift-house.php?category=<?php echo urlencode($category); ?>" class="btn btn-outline-secondary" title="Clear Filter"><i class="bi bi-x-lg"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- Product Catalog Grid -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <?php if (!empty($products)): ?>
      <div class="row g-4 mb-4">
        <?php foreach ($products as $p): ?>
          <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="badge bg-subtle text-dark border small"><?php echo htmlspecialchars($p['category']); ?></span>
                  <?php if (!empty($p['badge'])): ?>
                    <span class="badge bg-warning-subtle text-warning border small"><?php echo htmlspecialchars($p['badge']); ?></span>
                  <?php endif; ?>
                </div>

                <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning mb-3" style="width: 48px; height: 48px; font-size: 1.5rem;">
                  <i class="bi <?php echo htmlspecialchars($p['icon'] ?? 'bi-box-seam'); ?>"></i>
                </div>

                <h5 class="fw-bold text-dark fs-6 mb-1"><?php echo htmlspecialchars($p['name']); ?></h5>
                <span class="text-muted small mb-2 d-block" style="font-size: 0.72rem;">Code: <?php echo htmlspecialchars($p['code'] ?? 'MB-PROD'); ?></span>
                <p class="small text-muted mb-3 flex-grow-1"><?php echo htmlspecialchars($p['description']); ?></p>

                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                  <span class="fw-bold text-primary fs-6">
                    <?php echo !empty($p['price']) ? '₹' . number_format($p['price']) : 'In-Store Pricing'; ?>
                  </span>
                  <a href="https://wa.me/919775890661?text=Inquiry%20for%20<?php echo urlencode($p['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-whatsapp"></i> Inquire
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
          <span class="small text-muted">Showing <strong><?php echo min($total_rows, $offset + 1); ?> - <?php echo min($total_rows, $offset + count($products)); ?></strong> of <strong><?php echo $total_rows; ?></strong> items</span>
          <nav aria-label="Catalog Pagination">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                  <i class="bi bi-chevron-left"></i> Prev
                </a>
              </li>
              <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
                  <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>">
                    <?php echo $p; ?>
                  </a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
                  Next <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-box-seam display-3 text-muted mb-3 d-block"></i>
        <h5 class="fw-bold text-dark">No Items Found</h5>
        <p class="small text-muted mb-3">No products matched your selected category and search.</p>
        <a href="mobile-gift-house.php" class="btn btn-warning btn-sm text-dark fw-bold">View All Catalog</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
