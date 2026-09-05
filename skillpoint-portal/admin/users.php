<?php
/**
 * MB Internet And Digital Studio | Student Users Management (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$page_title = "Registered Student Users";
$success_msg = "";
$error_msg = "";

// স্ট্যাটাস টগল
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $u_id = (int)$_GET['id'];
        $stmt = $conn->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id AND role = 'user'");
        $stmt->execute([':id' => $u_id]);
        security_log('ADMIN_USER_STATUS_TOGGLED', ['target_user_id' => $u_id]);
        $success_msg = "Student status updated successfully!";
    }
}

// সার্চ, ফিল্টার ও পেজিনেশন
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["role = 'user'"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(name LIKE :q OR email LIKE :q OR mobile LIKE :q OR user_code LIKE :q OR address LIKE :q)";
    $params[':q'] = "%$search_q%";
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = :st";
    $params[':st'] = $filter_status;
}

$where_sql = implode(" AND ", $where_clauses);

// কাউন্ট
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// লিস্ট
$sql = "SELECT * FROM users WHERE $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

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

<!-- Search & Filter Header -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="users.php" class="row g-2 align-items-center">
      <div class="col-md-7">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by student name, user code, email, mobile, address..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="active" <?php echo ($filter_status === 'active') ? 'selected' : ''; ?>>Active Accounts</option>
          <option value="inactive" <?php echo ($filter_status === 'inactive') ? 'selected' : ''; ?>>Suspended / Inactive</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Search</button>
        <?php if (!empty($search_q) || !empty($filter_status)): ?>
          <a href="users.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Student Directory (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Student Name & Code</th>
            <th>Contact Details</th>
            <th>Address</th>
            <th>Status</th>
            <th>Joined On</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($u['name']); ?></strong>
                  <span class="badge bg-subtle text-primary border" style="font-size: 0.72rem;"><?php echo htmlspecialchars($u['user_code']); ?></span>
                </td>
                <td>
                  <div><i class="bi bi-envelope text-muted me-1"></i> <?php echo htmlspecialchars($u['email']); ?></div>
                  <div><i class="bi bi-telephone text-muted me-1"></i> <?php echo htmlspecialchars($u['mobile']); ?></div>
                </td>
                <td class="text-muted"><?php echo htmlspecialchars($u['address'] ?: 'Not Provided'); ?></td>
                <td>
                  <span class="badge <?php echo ($u['status'] === 'active') ? 'bg-success-subtle text-success border' : 'bg-secondary-subtle text-secondary border'; ?>">
                    <?php echo ucfirst($u['status']); ?>
                  </span>
                </td>
                <td class="text-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                  <a href="users.php?action=toggle&id=<?php echo $u['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($u['status'] === 'active') ? 'btn-outline-danger' : 'btn-outline-success'; ?>" style="font-size: 0.75rem;">
                    <?php echo ($u['status'] === 'active') ? 'Suspend' : 'Activate'; ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No student accounts found.</td></tr>
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
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page - 1; ?>">Prev</a>
          </li>
          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
              <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page + 1; ?>">Next</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
