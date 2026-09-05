<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House Admin (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

require_admin();

$page_title = "Manage Mobile & Gift Catalog";
$success_msg = "";
$error_msg = "";

// ১. ডিলিট
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $del_id = (int)$_GET['id'];
        $conn->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $del_id]);
        security_log('ADMIN_PRODUCT_DELETED', ['product_id' => $del_id]);
        $success_msg = "Product removed from catalog!";
    }
}

// ২. স্ট্যাটাস টগল
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $t_id = (int)$_GET['id'];
        $conn->prepare("UPDATE products SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id")->execute([':id' => $t_id]);
        security_log('ADMIN_PRODUCT_TOGGLED', ['product_id' => $t_id]);
        $success_msg = "Product status updated!";
    }
}

// ৩. প্রোডাক্ট যোগ (with validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Mobile Accessories');
        $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
        $badge = trim($_POST['badge'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $code = "MB-" . rand(100, 999);

        if (empty($name)) {
            $error_msg = "Product name is required.";
        } elseif (mb_strlen($name, 'UTF-8') < 3 || mb_strlen($name, 'UTF-8') > 120) {
            $error_msg = "Product name must be between 3 and 120 characters long.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (code, name, category, price, badge, description, status, created_at) VALUES (:code, :name, :cat, :price, :badge, :desc, 'active', NOW())");
            $stmt->execute([':code' => $code, ':name' => $name, ':cat' => $category, ':price' => $price, ':badge' => $badge ?: null, ':desc' => $description]);
            $new_pid = (int)$conn->lastInsertId();
            security_log('ADMIN_PRODUCT_CREATED', ['product_id' => $new_pid]);
            $success_msg = "New product added to catalog!";
        }
    }
}

// সার্চ ও পেজিনেশন
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(name LIKE :q OR code LIKE :q OR description LIKE :q)";
    $params[':q'] = "%$search_q%";
}
if (!empty($filter_cat)) {
    $where_clauses[] = "category = :cat";
    $params[':cat'] = $filter_cat;
}

$where_sql = implode(" AND ", $where_clauses);

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM products WHERE $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
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

<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Product Add Form -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-plus-circle-fill text-warning me-2"></i> Add Mobile Accessory or Gift Product</h6>
  </div>
  <div class="card-body p-4">
    <form method="POST" action="products.php">
      <?php echo csrf_field(); ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Product Name *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Fast Charging Type-C Cable 65W" required maxlength="120">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Mobile Accessories">Mobile Accessories</option>
            <option value="Personalized Gifts">Personalized Gifts</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Price (₹) (Optional)</label>
          <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="399">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Badge / Label (Optional)</label>
          <input type="text" name="badge" class="form-control" placeholder="e.g. 6 Months Warranty / Trending" maxlength="50">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Short Description</label>
          <input type="text" name="description" class="form-control" placeholder="Features, specifications, materials..." maxlength="255">
        </div>
      </div>

      <input type="hidden" name="save_product" value="1">
      <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
        <i class="bi bi-save me-1"></i> Add to Catalog
      </button>
    </form>
  </div>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="products.php" class="row g-2 align-items-center">
      <div class="col-md-7">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search product name, code or description..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Mobile Accessories" <?php echo ($filter_cat === 'Mobile Accessories') ? 'selected' : ''; ?>>Mobile Accessories</option>
          <option value="Personalized Gifts" <?php echo ($filter_cat === 'Personalized Gifts') ? 'selected' : ''; ?>>Personalized Gifts</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
        <?php if (!empty($search_q) || !empty($filter_cat)): ?>
          <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Products Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-warning me-2"></i> Catalog Items (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Badge</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($p['name']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($p['code']); ?></span>
                </td>
                <td><span class="badge bg-subtle text-dark border"><?php echo htmlspecialchars($p['category']); ?></span></td>
                <td class="fw-semibold text-primary"><?php echo !empty($p['price']) ? '₹' . number_format($p['price']) : 'In-Store'; ?></td>
                <td><span class="badge bg-warning-subtle text-warning border"><?php echo htmlspecialchars($p['badge'] ?: 'Standard'); ?></span></td>
                <td>
                  <a href="products.php?action=toggle&id=<?php echo $p['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($p['status'] === 'active') ? 'btn-success' : 'btn-secondary'; ?>" style="font-size: 0.75rem;">
                    <?php echo ucfirst($p['status']); ?>
                  </a>
                </td>
                <td>
                  <a href="products.php?action=delete&id=<?php echo $p['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product from catalog?');" title="Delete">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Showing page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&page=<?php echo $page - 1; ?>">Prev</a>
          </li>
          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
              <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&page=<?php echo $page + 1; ?>">Next</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
