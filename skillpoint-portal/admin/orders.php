<?php
/**
 * MB Internet And Digital Studio | Orders & Admissions Directory (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$page_title = "Orders & Admissions Directory";

$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(o.order_number LIKE :q OR o.customer_name LIKE :q OR o.email LIKE :q OR o.mobile LIKE :q OR o.enrollment_number LIKE :q OR c.title LIKE :q)";
    $params[':q'] = "%$search_q%";
}
if (!empty($filter_status)) {
    $where_clauses[] = "o.payment_status = :st";
    $params[':st'] = $filter_status;
}

$where_sql = implode(" AND ", $where_clauses);

// Count
$count_stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM orders o
    JOIN courses c ON o.course_id = c.id
    WHERE $where_sql
");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch Rows
$sql = "
    SELECT o.*, c.title as course_title, c.duration
    FROM orders o
    JOIN courses c ON o.course_id = c.id
    WHERE $where_sql
    ORDER BY o.id DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Search and Filter Header -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="orders.php" class="row g-2 align-items-center">
      <div class="col-md-7">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by Order ID, Student Name, Phone, Email..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Payment Statuses</option>
          <option value="Paid" <?php echo ($filter_status === 'Paid') ? 'selected' : ''; ?>>Paid</option>
          <option value="Pending" <?php echo ($filter_status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="Failed" <?php echo ($filter_status === 'Failed') ? 'selected' : ''; ?>>Failed</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Search</button>
        <?php if (!empty($search_q) || !empty($filter_status)): ?>
          <a href="orders.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cart-check text-primary me-2"></i> All Course Orders & Admissions (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Order No</th>
            <th>Enrollment ID</th>
            <th>Student Name</th>
            <th>Contact</th>
            <th>Course</th>
            <th>Amount</th>
            <th>Payment Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td class="fw-bold text-dark"><?php echo htmlspecialchars($o['order_number']); ?></td>
                <td><span class="badge bg-primary-subtle text-primary border"><?php echo htmlspecialchars($o['enrollment_number'] ?? 'Pending'); ?></span></td>
                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($o['customer_name']); ?></td>
                <td>
                  <span class="d-block text-dark"><?php echo htmlspecialchars($o['mobile']); ?></span>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($o['email']); ?></span>
                </td>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($o['course_title']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($o['duration']); ?></span>
                </td>
                <td class="fw-bold text-primary">₹<?php echo number_format($o['final_amount']); ?></td>
                <td><span class="badge bg-success-subtle text-success border"><?php echo htmlspecialchars($o['payment_status']); ?></span></td>
                <td class="text-muted"><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">No orders found matching criteria.</td></tr>
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
