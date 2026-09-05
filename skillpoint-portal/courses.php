<?php
/**
 * MB Internet And Digital Studio | Courses Catalog with Search & Pagination (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = "Computer Courses & Training";

// ফিল্টার, সার্চ ও পেজিনেশন ভ্যারিয়েবল
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'popular';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

// বেস কুয়েরি
$where_clauses = ["status = 'active'"];
$params = [];

if ($category !== 'all' && !empty($category)) {
    $where_clauses[] = "category = :category";
    $params[':category'] = $category;
}

if (!empty($search)) {
    $where_clauses[] = "(title LIKE :search OR short_desc LIKE :search OR category LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_sql = implode(" AND ", $where_clauses);

// মোট রেকর্ড কাউন্ট (Total Count)
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

// সর্টিং নির্ধারণ
$order_sql = "rating DESC, id ASC";
if ($sort === 'price-low') {
    $order_sql = "price ASC";
} elseif ($sort === 'price-high') {
    $order_sql = "price DESC";
} elseif ($sort === 'newest') {
    $order_sql = "id DESC";
}

// কোর্স ডাটা ফেচ
$sql = "SELECT * FROM courses WHERE $where_sql ORDER BY $order_sql LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();

// ক্যাটেগরি ড্রপডাউন লিস্ট
$categories_stmt = $conn->query("SELECT DISTINCT category FROM courses WHERE status = 'active' ORDER BY category ASC");
$all_categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
  <div class="container py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h1 class="fw-bold fs-3 mb-1"><i class="bi bi-mortarboard-fill me-2"></i> Computer Education & Training</h1>
        <p class="mb-0 opacity-90 small">Practical lab training, government-recognized curriculum, ISO certified certificate, and live job support.</p>
      </div>
      <span class="badge bg-white text-success fs-6 fw-bold px-3 py-2 shadow-sm rounded-pill align-self-start align-self-md-center">
        <?php echo $total_rows; ?> Courses Available
      </span>
    </div>
  </div>
</section>

<!-- Filter & Search Section -->
<section class="py-4 border-bottom" style="background: var(--card-bg, #ffffff);">
  <div class="container">
    <form method="GET" action="courses.php" class="row g-3 align-items-center">
      <!-- Search Input -->
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-subtle border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0" placeholder="Search Tally, Excel, Web Dev, Python..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>

      <!-- Category Filter -->
      <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
          <option value="all">All Categories</option>
          <?php foreach ($all_categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Sort Order -->
      <div class="col-md-3 d-flex gap-2">
        <select name="sort" class="form-select" onchange="this.form.submit()">
          <option value="popular" <?php echo ($sort === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
          <option value="price-low" <?php echo ($sort === 'price-low') ? 'selected' : ''; ?>>Price: Low to High</option>
          <option value="price-high" <?php echo ($sort === 'price-high') ? 'selected' : ''; ?>>Price: High to Low</option>
          <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest Added</option>
        </select>
        <button type="submit" class="btn btn-primary px-3 fw-semibold">Search</button>
        <?php if (!empty($search) || $category !== 'all' || $sort !== 'popular'): ?>
          <a href="courses.php" class="btn btn-outline-secondary px-2" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<!-- Courses Grid -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    
    <?php if (!empty($courses)): ?>
      <div class="row g-4 mb-5">
        <?php foreach ($courses as $c): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column transition-hover" style="background: var(--card-bg, #ffffff);">
              
              <!-- Card Header / Badge -->
              <div class="p-4 border-bottom bg-subtle position-relative">
                <?php if (!empty($c['badge'])): ?>
                  <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-2 py-1 shadow-sm font-monospace" style="font-size: 0.72rem;">
                    <?php echo htmlspecialchars($c['badge']); ?>
                  </span>
                <?php endif; ?>
                <span class="badge bg-primary-subtle text-primary mb-2 border border-primary-subtle">
                  <?php echo htmlspecialchars($c['category']); ?>
                </span>
                <h5 class="fw-bold text-dark mb-1 lh-sm" style="min-height: 48px;">
                  <?php echo htmlspecialchars($c['title']); ?>
                </h5>
                <span class="text-muted small font-monospace">Course Code: <?php echo htmlspecialchars($c['course_code']); ?></span>
              </div>

              <!-- Card Body -->
              <div class="card-body p-4 d-flex flex-column">
                <p class="small text-muted mb-4 flex-grow-1" style="min-height: 48px;">
                  <?php echo htmlspecialchars($c['short_desc']); ?>
                </p>

                <!-- Course Meta Info -->
                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-subtle mb-3 small">
                  <div><i class="bi bi-clock-history text-primary me-1"></i> <?php echo htmlspecialchars($c['duration']); ?></div>
                  <div><i class="bi bi-mortarboard text-success me-1"></i> <?php echo htmlspecialchars($c['level'] ?? 'Beginner to Adv'); ?></div>
                </div>

                <!-- Price & Action -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                  <div>
                    <span class="text-muted small d-block" style="font-size: 0.75rem;">Complete Fee</span>
                    <span class="fw-bold fs-5 text-primary">₹<?php echo number_format($c['price']); ?></span>
                    <?php if (!empty($c['original_price']) && $c['original_price'] > $c['price']): ?>
                      <span class="text-muted text-decoration-line-through small ms-1">₹<?php echo number_format($c['original_price']); ?></span>
                    <?php endif; ?>
                  </div>

                  <div class="d-flex gap-2">
                    <a href="course-details.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-primary btn-sm">
                      Details
                    </a>
                    <?php if (is_admin()): ?>
                      <a href="admin/courses.php" class="btn btn-warning btn-sm fw-bold">
                        <i class="bi bi-gear-fill me-1"></i> Manage
                      </a>
                    <?php else: ?>
                      <a href="checkout.php?course_id=<?php echo $c['id']; ?>" class="btn btn-primary btn-sm fw-bold">
                        Enroll <i class="bi bi-arrow-right"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination Controls -->
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Course catalog pages">
          <ul class="pagination justify-content-center">
            <!-- Prev Page -->
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
              <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $page - 1; ?>">
                <i class="bi bi-chevron-left"></i> Prev
              </a>
            </li>

            <!-- Page Number Links -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $i; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php endfor; ?>

            <!-- Next Page -->
            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
              <a class="page-link" href="?category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $page + 1; ?>">
                Next <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-search display-3 text-muted mb-3 d-block"></i>
        <h4 class="fw-bold text-dark">No Courses Found</h4>
        <p class="text-muted small mb-3">No computer courses matched your search term "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
        <a href="courses.php" class="btn btn-primary btn-sm">Clear Search & View All</a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
