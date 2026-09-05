<?php
/**
 * MB Internet And Digital Studio | Payment & Admission Receipt (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_user();

$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

$stmt = $conn->prepare("
    SELECT o.*, c.title as course_title, c.duration, c.hours, c.category
    FROM orders o
    JOIN courses c ON o.course_id = c.id
    WHERE o.order_number = :onum
    LIMIT 1
");
$stmt->execute([':onum' => $order_number]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Admission Receipt - " . $order['order_number'];

include __DIR__ . '/includes/header.php';
?>

<!-- Receipt Section -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container" style="max-width: 750px;">
    
    <div class="card border-0 shadow rounded-4 overflow-hidden" id="printableReceipt" style="background: var(--card-bg, #ffffff);">
      
      <!-- Receipt Header -->
      <div class="p-4 bg-primary text-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;">
            MB
          </div>
          <div>
            <h5 class="fw-bold mb-0">MB Internet And Digital Studio</h5>
            <span class="small opacity-75">Official Admission & Course Fee Receipt</span>
          </div>
        </div>
        <span class="badge bg-success text-white px-3 py-2 fs-6">PAID & VERIFIED</span>
      </div>

      <!-- Receipt Body -->
      <div class="card-body p-4 p-md-5">
        
        <div class="row g-3 mb-4 pb-3 border-bottom small">
          <div class="col-6 col-sm-3">
            <span class="text-muted d-block">Order Number</span>
            <strong class="text-dark"><?php echo htmlspecialchars($order['order_number']); ?></strong>
          </div>
          <div class="col-6 col-sm-3">
            <span class="text-muted d-block">Enrollment ID</span>
            <strong class="text-success"><?php echo htmlspecialchars($order['enrollment_number']); ?></strong>
          </div>
          <div class="col-6 col-sm-3">
            <span class="text-muted d-block">Date & Time</span>
            <strong class="text-dark"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></strong>
          </div>
          <div class="col-6 col-sm-3">
            <span class="text-muted d-block">Payment Mode</span>
            <strong class="text-dark"><?php echo htmlspecialchars($order['payment_method']); ?></strong>
          </div>
        </div>

        <!-- Student Info -->
        <h6 class="fw-bold text-dark mb-2">Student Particulars</h6>
        <div class="p-3 rounded-3 bg-subtle border mb-4 small">
          <div class="row g-2">
            <div class="col-sm-6"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
            <div class="col-sm-6"><strong>Mobile:</strong> <?php echo htmlspecialchars($order['mobile']); ?></div>
            <div class="col-sm-6"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></div>
            <div class="col-sm-6"><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
          </div>
        </div>

        <!-- Course Info -->
        <h6 class="fw-bold text-dark mb-2">Course Enrolled</h6>
        <table class="table table-bordered mb-4 small">
          <thead class="table-light">
            <tr>
              <th>Description</th>
              <th>Duration</th>
              <th class="text-end">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <strong class="text-dark"><?php echo htmlspecialchars($order['course_title']); ?></strong>
                <span class="d-block text-muted"><?php echo htmlspecialchars($order['category']); ?></span>
              </td>
              <td><?php echo htmlspecialchars($order['duration']); ?></td>
              <td class="text-end fw-bold">₹<?php echo number_format($order['total_amount']); ?></td>
            </tr>
            <?php if ($order['discount_amount'] > 0): ?>
              <tr class="text-success">
                <td colspan="2">Coupon Discount (<?php echo htmlspecialchars($order['coupon_code']); ?>)</td>
                <td class="text-end">- ₹<?php echo number_format($order['discount_amount']); ?></td>
              </tr>
            <?php endif; ?>
            <tr class="table-light fw-bold fs-6">
              <td colspan="2" class="text-end">Total Amount Paid:</td>
              <td class="text-end text-primary">₹<?php echo number_format($order['final_amount']); ?></td>
            </tr>
          </tbody>
        </table>

        <!-- Actions -->
        <div class="d-flex gap-2 justify-content-center pt-3 border-top d-print-none">
          <button type="button" class="btn btn-outline-primary px-4" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print Receipt
          </button>
          <a href="dashboard.php" class="btn btn-primary fw-bold px-4">
            <i class="bi bi-person-circle me-1"></i> Go to My Dashboard
          </a>
        </div>

      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
