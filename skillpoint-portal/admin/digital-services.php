<?php
/**
 * MB Internet And Digital Studio | Digital Services Admin (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

require_admin();

$page_title = "Manage Digital & Citizen Services";
$success_msg = "";
$error_msg = "";

// ১. ডিলিট
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $del_id = (int)$_GET['id'];
        $conn->prepare("DELETE FROM digital_services WHERE id = :id")->execute([':id' => $del_id]);
        security_log('ADMIN_DIGITAL_SERVICE_DELETED', ['service_id' => $del_id]);
        $success_msg = "Digital service deleted successfully!";
    }
}

// ২. স্ট্যাটাস টগল
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $t_id = (int)$_GET['id'];
        $conn->prepare("UPDATE digital_services SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id")->execute([':id' => $t_id]);
        security_log('ADMIN_DIGITAL_SERVICE_TOGGLED', ['service_id' => $t_id]);
        $success_msg = "Status updated successfully!";
    }
}

// ৩. সার্ভিস যোগ বা এডিট (with validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $id = (int)($_POST['service_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Application Services');
        $price_text = trim($_POST['price_text'] ?? 'Nominal Fee');
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) {
            $error_msg = "Service title is required.";
        } elseif (mb_strlen($title, 'UTF-8') < 3 || mb_strlen($title, 'UTF-8') > 120) {
            $error_msg = "Service title must be between 3 and 120 characters long.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE digital_services SET title = :title, category = :category, price_text = :ptext, description = :desc WHERE id = :id");
                $stmt->execute([':title' => $title, ':category' => $category, ':ptext' => $price_text, ':desc' => $description, ':id' => $id]);
                security_log('ADMIN_DIGITAL_SERVICE_UPDATED', ['service_id' => $id]);
                $success_msg = "Service updated successfully!";
            } else {
                $stmt = $conn->prepare("INSERT INTO digital_services (title, category, price_text, description, status, created_at) VALUES (:title, :category, :ptext, :desc, 'active', NOW())");
                $stmt->execute([':title' => $title, ':category' => $category, ':ptext' => $price_text, ':desc' => $description]);
                $new_id = (int)$conn->lastInsertId();
                security_log('ADMIN_DIGITAL_SERVICE_CREATED', ['service_id' => $new_id]);
                $success_msg = "New digital service added successfully!";
            }
        }
    }
}

// ৪. এডিট ফেচ
$edit_service = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    $e_stmt = $conn->prepare("SELECT * FROM digital_services WHERE id = :id LIMIT 1");
    $e_stmt->execute([':id' => $e_id]);
    $edit_service = $e_stmt->fetch();
}

// সার্চ ও পেজিনেশন
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(title LIKE :q OR description LIKE :q OR category LIKE :q)";
    $params[':q'] = "%$search_q%";
}

$where_sql = implode(" AND ", $where_clauses);

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM digital_services WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM digital_services WHERE $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
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

<!-- Form Box -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0">
      <i class="bi <?php echo $edit_service ? 'bi-pencil-square text-warning' : 'bi-plus-circle-fill text-primary'; ?> me-2"></i>
      <?php echo $edit_service ? 'Edit Service' : 'Add Digital / Citizen Service'; ?>
    </h6>
    <?php if ($edit_service): ?>
      <a href="digital-services.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i> Cancel</a>
    <?php endif; ?>
  </div>
  <div class="card-body p-4">
    <form method="POST" action="digital-services.php">
      <?php echo csrf_field(); ?>
      <?php if ($edit_service): ?>
        <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Service Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Online Exam Form Fill-up" value="<?php echo htmlspecialchars($edit_service['title'] ?? ''); ?>" required maxlength="120">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Category</label>
          <select name="category" class="form-select">
            <option value="Application Services" <?php echo (($edit_service['category'] ?? '') === 'Application Services') ? 'selected' : ''; ?>>Application Services</option>
            <option value="Printing & Photo" <?php echo (($edit_service['category'] ?? '') === 'Printing & Photo') ? 'selected' : ''; ?>>Printing & Photo</option>
            <option value="Smart Cards" <?php echo (($edit_service['category'] ?? '') === 'Smart Cards') ? 'selected' : ''; ?>>Smart Cards</option>
            <option value="Utility Payments" <?php echo (($edit_service['category'] ?? '') === 'Utility Payments') ? 'selected' : ''; ?>>Utility Payments</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Pricing Text</label>
          <input type="text" name="price_text" class="form-control" placeholder="₹50 / form or ₹2 / page" value="<?php echo htmlspecialchars($edit_service['price_text'] ?? ''); ?>" maxlength="50">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-bold text-dark">Service Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Explain turnaround time and requirements..." maxlength="500"><?php echo htmlspecialchars($edit_service['description'] ?? ''); ?></textarea>
      </div>

      <input type="hidden" name="save_service" value="1">
      <button type="submit" class="btn btn-primary fw-bold px-4">
        <i class="bi bi-save me-1"></i> <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
      </button>
    </form>
  </div>
</div>

<!-- Table Directory -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul text-primary me-2"></i> All Digital Services (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Service Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($services)): ?>
            <?php foreach ($services as $s): ?>
              <tr>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($s['title']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 60)); ?>...</span>
                </td>
                <td><span class="badge bg-subtle text-dark border"><?php echo htmlspecialchars($s['category']); ?></span></td>
                <td class="fw-semibold text-primary"><?php echo htmlspecialchars($s['price_text']); ?></td>
                <td>
                  <a href="digital-services.php?action=toggle&id=<?php echo $s['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($s['status'] === 'active') ? 'btn-success' : 'btn-secondary'; ?>" style="font-size: 0.75rem;">
                    <?php echo ucfirst($s['status']); ?>
                  </a>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="digital-services.php?edit_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                    <a href="digital-services.php?action=delete&id=<?php echo $s['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this digital service?');" title="Delete"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">No services found.</td></tr>
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
