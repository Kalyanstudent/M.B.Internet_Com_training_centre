<?php
/**
 * MB Internet And Digital Studio | Tax Requests & Document Review (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$page_title = "Tax & Service Requests Management";
$success_msg = "";
$error_msg = "";

// স্ট্যাটাস ও নোট আপডেট হ্যান্ডলিং (Core PHP POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $req_id = (int)$_POST['request_id'];
        $status = trim($_POST['status'] ?? 'Pending');
        $notes = trim($_POST['internal_notes'] ?? '');

        $stmt = $conn->prepare("UPDATE service_requests SET status = :status, internal_notes = :notes WHERE id = :id");
        $stmt->execute([':status' => $status, ':notes' => $notes, ':id' => $req_id]);
        security_log('ADMIN_TAX_REQUEST_UPDATED', ['request_id' => $req_id, 'status' => $status]);
        $success_msg = "Request status & consultant notes updated successfully!";
    }
}

// ডিটেইলস দেখার জন্য সিলেক্টেড রিকোয়েস্ট ফেচ
$selected_request = null;
$attached_docs = [];
if (isset($_GET['view_id'])) {
    $v_id = (int)$_GET['view_id'];
    $r_stmt = $conn->prepare("SELECT * FROM service_requests WHERE id = :id LIMIT 1");
    $r_stmt->execute([':id' => $v_id]);
    $selected_request = $r_stmt->fetch();

    if ($selected_request) {
        $d_stmt = $conn->prepare("SELECT * FROM documents WHERE request_id = :rid");
        $d_stmt->execute([':rid' => $v_id]);
        $attached_docs = $d_stmt->fetchAll();
    }
}

// সার্চ, ফিল্টার ও পেজিনেশন
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(request_number LIKE :q OR customer_name LIKE :q OR email LIKE :q OR mobile LIKE :q OR pan_number LIKE :q OR service_type LIKE :q)";
    $params[':q'] = "%$search_q%";
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = :st";
    $params[':st'] = $filter_status;
}

$where_sql = implode(" AND ", $where_clauses);

// কাউন্ট
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM service_requests WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// লিস্ট
$sql = "SELECT * FROM service_requests WHERE $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$requests = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success rounded-4 mb-4 shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
  </div>
<?php endif; ?>

<!-- Review Selected Request Panel -->
<?php if ($selected_request): ?>
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold text-dark mb-0">
        <i class="bi bi-pencil-square text-primary me-2"></i> Review Request: <?php echo htmlspecialchars($selected_request['request_number']); ?>
      </h6>
      <a href="tax-requests.php" class="btn btn-sm btn-outline-secondary">Close Review</a>
    </div>
    <div class="card-body p-4">
      <div class="row g-4">
        <!-- Client Info -->
        <div class="col-md-6">
          <h6 class="fw-bold text-dark mb-2">Applicant Particulars</h6>
          <div class="p-3 rounded-3 bg-subtle border small mb-3">
            <div class="mb-1"><strong>Client Name:</strong> <?php echo htmlspecialchars($selected_request['customer_name']); ?></div>
            <div class="mb-1"><strong>Mobile:</strong> <?php echo htmlspecialchars($selected_request['mobile']); ?></div>
            <div class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($selected_request['email']); ?></div>
            <div class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($selected_request['address']); ?></div>
            <div class="mb-1"><strong>PAN Number:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($selected_request['pan_number'] ?: 'N/A'); ?></span></div>
            <div class="mb-0"><strong>Period:</strong> FY <?php echo htmlspecialchars($selected_request['financial_year']); ?> / AY <?php echo htmlspecialchars($selected_request['assessment_year']); ?></div>
          </div>

          <!-- Attached Documents List -->
          <h6 class="fw-bold text-dark mb-2">Attached Documents (<?php echo count($attached_docs); ?>)</h6>
          <?php if (!empty($attached_docs)): ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($attached_docs as $doc): ?>
                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-subtle border small">
                  <div class="d-flex align-items-center gap-2 text-truncate">
                    <i class="bi bi-file-earmark-lock-fill text-primary fs-5"></i>
                    <span class="fw-semibold text-dark text-truncate"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                    <span class="text-muted">(<?php echo htmlspecialchars($doc['file_size_formatted']); ?>)</span>
                  </div>
                  <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">Download</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="small text-muted mb-0">No documents uploaded with this request.</p>
          <?php endif; ?>
        </div>

        <!-- Status & Notes Update Form -->
        <div class="col-md-6">
          <h6 class="fw-bold text-dark mb-2">Update Application Status</h6>
          <form method="POST" action="tax-requests.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="request_id" value="<?php echo $selected_request['id']; ?>">

            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Current Status *</label>
              <select name="status" class="form-select">
                <option value="Pending" <?php echo ($selected_request['status'] === 'Pending') ? 'selected' : ''; ?>>Pending Review</option>
                <option value="Processing" <?php echo ($selected_request['status'] === 'Processing') ? 'selected' : ''; ?>>In Processing / Computation</option>
                <option value="Completed" <?php echo ($selected_request['status'] === 'Completed') ? 'selected' : ''; ?>>Completed / ITR-V Filed</option>
                <option value="Cancelled" <?php echo ($selected_request['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Internal Consultant Notes</label>
              <textarea name="internal_notes" class="form-control" rows="4" placeholder="Add tax computation remarks, acknowledgement numbers, or client follow-up details..."><?php echo htmlspecialchars($selected_request['internal_notes'] ?? ''); ?></textarea>
            </div>

            <input type="hidden" name="update_request" value="1">
            <button type="submit" class="btn btn-primary fw-bold px-4">
              <i class="bi bi-save me-1"></i> Save Status & Notes
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="tax-requests.php" class="row g-2 align-items-center">
      <div class="col-md-7">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by Request ID, Name, PAN, Phone, Service..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="Pending" <?php echo ($filter_status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="Processing" <?php echo ($filter_status === 'Processing') ? 'selected' : ''; ?>>Processing</option>
          <option value="Completed" <?php echo ($filter_status === 'Completed') ? 'selected' : ''; ?>>Completed</option>
          <option value="Cancelled" <?php echo ($filter_status === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Search</button>
        <?php if (!empty($search_q) || !empty($filter_status)): ?>
          <a href="tax-requests.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- All Tax Requests Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i> All Service Applications (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Request No</th>
            <th>Client Name</th>
            <th>Service Type</th>
            <th>PAN & Period</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td class="fw-bold text-dark"><?php echo htmlspecialchars($r['request_number']); ?></td>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($r['customer_name']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($r['mobile']); ?></span>
                </td>
                <td><?php echo htmlspecialchars($r['service_type']); ?></td>
                <td>
                  <span class="badge bg-subtle text-dark border"><?php echo htmlspecialchars($r['pan_number'] ?: 'No PAN'); ?></span>
                  <div class="text-muted" style="font-size: 0.72rem;">AY <?php echo htmlspecialchars($r['assessment_year']); ?></div>
                </td>
                <td>
                  <?php if ($r['status'] === 'Completed'): ?>
                    <span class="badge bg-success-subtle text-success border">Completed</span>
                  <?php elseif ($r['status'] === 'Processing'): ?>
                    <span class="badge bg-info-subtle text-info border">Processing</span>
                  <?php elseif ($r['status'] === 'Cancelled'): ?>
                    <span class="badge bg-danger-subtle text-danger border">Cancelled</span>
                  <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning border">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                <td>
                  <a href="tax-requests.php?view_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                    <i class="bi bi-pencil-square me-1"></i> Review
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No tax service requests found matching criteria.</td></tr>
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
