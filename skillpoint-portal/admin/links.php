<?php
/**
 * MB Internet And Digital Studio | Important Links Admin (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

require_admin();

$page_title = "Manage Official Government Links";
$success_msg = "";
$error_msg = "";

// ১. ডিলিট
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $del_id = (int)$_GET['id'];
        $conn->prepare("DELETE FROM important_links WHERE id = :id")->execute([':id' => $del_id]);
        security_log('ADMIN_LINK_DELETED', ['link_id' => $del_id]);
        $success_msg = "Link deleted successfully!";
    }
}

// ২. স্ট্যাটাস টগল
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $t_id = (int)$_GET['id'];
        $conn->prepare("UPDATE important_links SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id")->execute([':id' => $t_id]);
        security_log('ADMIN_LINK_TOGGLED', ['link_id' => $t_id]);
        $success_msg = "Status updated!";
    }
}

// ৩. লিংক যোগ (with validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_link'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Taxation');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $order = (int)($_POST['display_order'] ?? 1);

        if (empty($title) || empty($url)) {
            $error_msg = "Title and URL are required.";
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error_msg = "Please enter a valid URL (starting with http:// or https://).";
        } elseif (mb_strlen($title, 'UTF-8') < 3 || mb_strlen($title, 'UTF-8') > 120) {
            $error_msg = "Title must be between 3 and 120 characters.";
        } else {
            $stmt = $conn->prepare("INSERT INTO important_links (title, category, url, description, display_order, status, created_at) VALUES (:title, :cat, :url, :desc, :dorder, 'active', NOW())");
            $stmt->execute([':title' => $title, ':cat' => $category, ':url' => $url, ':desc' => $description, ':dorder' => $order]);
            $new_lid = (int)$conn->lastInsertId();
            security_log('ADMIN_LINK_CREATED', ['link_id' => $new_lid]);
            $success_msg = "New official link added!";
        }
    }
}

// সার্চ ও পেজিনেশন
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(title LIKE :q OR url LIKE :q OR category LIKE :q OR description LIKE :q)";
    $params[':q'] = "%$search_q%";
}

$where_sql = implode(" AND ", $where_clauses);

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

<!-- Link Add Form -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-plus-circle-fill text-info me-2"></i> Add Important Official Portal Link</h6>
  </div>
  <div class="card-body p-4">
    <form method="POST" action="links.php">
      <?php echo csrf_field(); ?>

      <div class="row g-3 mb-3">
        <div class="col-md-5">
          <label class="form-label small fw-bold text-dark">Portal Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Income Tax e-Filing 2.0" required maxlength="120">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Category *</label>
          <select name="category" class="form-select">
            <option value="Taxation">Taxation & ITR</option>
            <option value="Identity & Citizen">Identity & Aadhaar</option>
            <option value="Education & Employment">Education & Employment</option>
            <option value="Business & MSME">Business & MSME</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-bold text-dark">Official URL *</label>
          <input type="url" name="url" class="form-control" placeholder="https://eportal.incometax.gov.in" required maxlength="255">
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-9">
          <label class="form-label small fw-bold text-dark">Brief Description</label>
          <input type="text" name="description" class="form-control" placeholder="Direct access for AIS, 26AS, and return filing..." maxlength="255">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Display Order</label>
          <input type="number" name="display_order" class="form-control" value="1" min="1">
        </div>
      </div>

      <input type="hidden" name="save_link" value="1">
      <button type="submit" class="btn btn-info text-white fw-bold px-4">
        <i class="bi bi-save me-1"></i> Save Official Link
      </button>
    </form>
  </div>
</div>

<!-- Links Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-link-45deg text-info me-2"></i> Active Portal Links (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Order</th>
            <th>Portal Title</th>
            <th>Category</th>
            <th>URL</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($links)): ?>
            <?php foreach ($links as $l): ?>
              <tr>
                <td><span class="badge bg-light text-dark border"><?php echo $l['display_order']; ?></span></td>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($l['title']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($l['description']); ?></span>
                </td>
                <td><span class="badge bg-subtle text-dark border"><?php echo htmlspecialchars($l['category']); ?></span></td>
                <td><a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 220px;"><?php echo htmlspecialchars($l['url']); ?></a></td>
                <td>
                  <a href="links.php?action=toggle&id=<?php echo $l['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($l['status'] === 'active') ? 'btn-success' : 'btn-secondary'; ?>" style="font-size: 0.75rem;">
                    <?php echo ucfirst($l['status']); ?>
                  </a>
                </td>
                <td>
                  <a href="links.php?action=delete&id=<?php echo $l['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this official link?');" title="Delete">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No portal links found.</td></tr>
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
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&page=<?php echo $page - 1; ?>">Prev</a>
          </li>
          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
              <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&page=<?php echo $page + 1; ?>">Next</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
