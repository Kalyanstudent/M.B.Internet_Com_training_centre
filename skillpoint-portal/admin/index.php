<?php
/**
 * MB Internet And Digital Studio | Admin Dashboard (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$page_title = "Executive Dashboard";

// ১. রিয়েল ডাটাবেজ কেপিআই (KPIs)
$total_revenue = (float)$conn->query("SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
$total_orders = (int)$conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_courses = (int)$conn->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
$pending_requests = (int)$conn->query("SELECT COUNT(*) FROM service_requests WHERE status = 'Pending'")->fetchColumn();

// ২. সাম্প্রতিক কোর্স অর্ডারসমূহ
$recent_orders_stmt = $conn->query("
    SELECT o.*, c.title as course_title
    FROM orders o
    JOIN courses c ON o.course_id = c.id
    ORDER BY o.id DESC
    LIMIT 5
");
$recent_orders = $recent_orders_stmt->fetchAll();

// ৩. সাম্প্রতিক ট্যাক্স রিকোয়েস্টসমূহ
$recent_requests_stmt = $conn->query("
    SELECT * FROM service_requests
    ORDER BY id DESC
    LIMIT 5
");
$recent_requests = $recent_requests_stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Metrics Summary Cards -->
<div class="row g-3 g-xl-4 mb-4">
  <!-- Revenue -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="stat-label">Total Revenue</div>
          <div class="stat-number text-primary">₹<?php echo number_format($total_revenue); ?></div>
        </div>
        <div class="stat-icon-wrap" style="background: rgba(37, 99, 235, 0.12); color: #2563eb;">
          <i class="bi bi-currency-rupee"></i>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 small text-success fw-medium">
        <i class="bi bi-shield-check"></i> Verified Transactions
      </div>
    </div>
  </div>

  <!-- Orders -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="stat-label">Admissions & Orders</div>
          <div class="stat-number text-success"><?php echo $total_orders; ?></div>
        </div>
        <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
          <i class="bi bi-cart-check-fill"></i>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 small text-muted">
        <i class="bi bi-mortarboard"></i> Computer Training
      </div>
    </div>
  </div>

  <!-- Registered Users -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="stat-label">Registered Clients</div>
          <div class="stat-number text-info"><?php echo $total_users; ?></div>
        </div>
        <div class="stat-icon-wrap" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
          <i class="bi bi-people-fill"></i>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 small text-muted">
        <i class="bi bi-person-badge"></i> Active Directory
      </div>
    </div>
  </div>

  <!-- Pending Tax Requests -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="stat-label">Pending Tax Actions</div>
          <div class="stat-number text-warning"><?php echo $pending_requests; ?></div>
        </div>
        <div class="stat-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
          <i class="bi bi-hourglass-split"></i>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 small text-warning fw-semibold">
        <i class="bi bi-exclamation-circle"></i> Requires Processing
      </div>
    </div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <a href="courses.php" class="btn btn-outline-primary w-100 p-3 rounded-4 shadow-sm text-start text-decoration-none d-flex align-items-center gap-3 bg-white">
      <div class="rounded-3 bg-primary-subtle text-primary p-2 fs-5"><i class="bi bi-mortarboard-fill"></i></div>
      <div>
        <strong class="d-block text-dark small">Manage Courses</strong>
        <span class="text-muted" style="font-size: 0.72rem;"><?php echo $total_courses; ?> Active Courses</span>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="tax-services.php" class="btn btn-outline-purple w-100 p-3 rounded-4 shadow-sm text-start text-decoration-none d-flex align-items-center gap-3 bg-white" style="border-color: #e9d5ff;">
      <div class="rounded-3 p-2 fs-5" style="background: #f3e8ff; color: #7c3aed;"><i class="bi bi-receipt-cutoff"></i></div>
      <div>
        <strong class="d-block text-dark small">M.B Taxation</strong>
        <span class="text-muted" style="font-size: 0.72rem;">ITR & GST Catalog</span>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="tax-requests.php" class="btn btn-outline-warning w-100 p-3 rounded-4 shadow-sm text-start text-decoration-none d-flex align-items-center gap-3 bg-white">
      <div class="rounded-3 bg-warning-subtle text-warning p-2 fs-5"><i class="bi bi-file-earmark-text-fill"></i></div>
      <div>
        <strong class="d-block text-dark small">Tax Requests</strong>
        <span class="text-muted" style="font-size: 0.72rem;"><?php echo $pending_requests; ?> Pending Review</span>
      </div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="settings.php" class="btn btn-outline-secondary w-100 p-3 rounded-4 shadow-sm text-start text-decoration-none d-flex align-items-center gap-3 bg-white">
      <div class="rounded-3 bg-light text-dark p-2 fs-5"><i class="bi bi-gear-fill"></i></div>
      <div>
        <strong class="d-block text-dark small">Store Settings</strong>
        <span class="text-muted" style="font-size: 0.72rem;">Enterprise Info</span>
      </div>
    </a>
  </div>
</div>

<!-- Recent Records Tables -->
<div class="row g-4">
  <!-- Recent Course Orders -->
  <div class="col-lg-6">
    <div class="admin-card h-100 mb-0">
      <div class="admin-card-header bg-white">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-cart-check text-primary"></i> Recent Admissions & Orders
        </h6>
        <a href="orders.php" class="btn btn-outline-primary btn-sm rounded-3">View All</a>
      </div>
      <div class="p-0">
        <div class="table-responsive">
          <table class="admin-table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Order No</th>
                <th>Client / Student</th>
                <th>Course</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_orders)): ?>
                <?php foreach ($recent_orders as $ro): ?>
                  <tr>
                    <td class="fw-bold text-dark font-monospace"><?php echo htmlspecialchars($ro['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($ro['customer_name']); ?></td>
                    <td><span class="text-truncate d-inline-block" style="max-width: 140px;"><?php echo htmlspecialchars($ro['course_title']); ?></span></td>
                    <td class="fw-bold text-primary">₹<?php echo number_format($ro['final_amount']); ?></td>
                    <td><span class="badge bg-success-subtle text-success border px-2 py-1 rounded-pill"><?php echo htmlspecialchars($ro['payment_status']); ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No recent orders recorded.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Tax Requests -->
  <div class="col-lg-6">
    <div class="admin-card h-100 mb-0">
      <div class="admin-card-header bg-white">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-medical-fill text-warning"></i> Recent Tax & ITR Requests
        </h6>
        <a href="tax-requests.php" class="btn btn-outline-warning btn-sm rounded-3 text-dark">View All</a>
      </div>
      <div class="p-0">
        <div class="table-responsive">
          <table class="admin-table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Req No</th>
                <th>Applicant</th>
                <th>Service</th>
                <th>Period</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_requests)): ?>
                <?php foreach ($recent_requests as $rr): ?>
                  <tr>
                    <td class="fw-bold text-dark font-monospace"><?php echo htmlspecialchars($rr['request_number']); ?></td>
                    <td><?php echo htmlspecialchars($rr['customer_name']); ?></td>
                    <td><span class="text-truncate d-inline-block" style="max-width: 130px;"><?php echo htmlspecialchars($rr['service_type']); ?></span></td>
                    <td class="small text-muted">AY <?php echo htmlspecialchars($rr['assessment_year'] ?? '2026-27'); ?></td>
                    <td>
                      <?php if ($rr['status'] === 'Completed'): ?>
                        <span class="badge bg-success-subtle text-success border px-2 py-1 rounded-pill">Completed</span>
                      <?php elseif ($rr['status'] === 'Processing'): ?>
                        <span class="badge bg-info-subtle text-info border px-2 py-1 rounded-pill">Processing</span>
                      <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border px-2 py-1 rounded-pill">Pending</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No tax service requests found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
