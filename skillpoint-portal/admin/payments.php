<?php
/**
 * MB Internet And Digital Studio | Payments Audit Log (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$page_title = "Payments Audit Log";

$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(p.payment_number LIKE :q OR o.order_number LIKE :q OR u.name LIKE :q OR u.email LIKE :q OR p.gateway_payment_id LIKE :q OR p.gateway LIKE :q)";
    $params[':q'] = "%$search_q%";
}

$where_sql = implode(" AND ", $where_clauses);

// কাউন্ট
$count_stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE $where_sql
");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// লিস্ট
$sql = "
    SELECT p.*, o.order_number, u.name as user_name, u.email as user_email
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE $where_sql
    ORDER BY p.id DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Search Bar -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="payments.php" class="row g-2 align-items-center">
      <div class="col-md-10">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by payment ID, order number, student name, gateway ID..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Search</button>
        <?php if (!empty($search_q)): ?>
          <a href="payments.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-credit-card text-primary me-2"></i> Payment Transactions (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Payment ID</th>
            <th>Order No</th>
            <th>Student</th>
            <th>Gateway Ref</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Transaction Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['payment_number']); ?></td>
                <td><?php echo htmlspecialchars($p['order_number'] ?? '#' . $p['order_id']); ?></td>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($p['user_name'] ?? 'Student'); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($p['user_email'] ?? ''); ?></span>
                </td>
                <td>
                  <span class="d-block text-dark"><?php echo htmlspecialchars($p['gateway'] ?? 'Online'); ?></span>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($p['gateway_payment_id'] ?? 'N/A'); ?></span>
                </td>
                <td class="fw-bold text-success">₹<?php echo number_format($p['amount']); ?></td>
                <td><span class="badge bg-success-subtle text-success border"><?php echo htmlspecialchars($p['status']); ?></span></td>
                <td class="text-muted"><?php echo date('d M Y, h:i A', strtotime($p['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No payment logs found matching criteria.</td></tr>
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
