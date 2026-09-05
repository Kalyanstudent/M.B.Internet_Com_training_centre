<?php
/**
 * MB Internet And Digital Studio | Course Admission & Checkout (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Validator.php';

// লগইন করা আবশ্যক
require_user();

$page_title = "Course Admission Checkout";
$logged_user = current_user();

// নির্বাচিত কোর্স আইডি
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = :id AND status = 'active' LIMIT 1");
$stmt->execute([':id' => $course_id]);
$course = $stmt->fetch();

if (!$course) {
    // যদি ডিফল্ট কোর্স না পাওয়া যায় প্রথম সক্রিয় কোর্স আনা
    $first_stmt = $conn->query("SELECT * FROM courses WHERE status = 'active' ORDER BY id ASC LIMIT 1");
    $course = $first_stmt->fetch();
}

$all_courses_stmt = $conn->query("SELECT id, title, price, duration FROM courses WHERE status = 'active' ORDER BY id ASC");
$all_courses = $all_courses_stmt->fetchAll();

$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : (isset($_GET['coupon']) ? trim($_GET['coupon']) : '');
$discount = 0;
$error_msg = "";
$success_order = null;

if (!empty($coupon_code)) {
    if ($coupon_code === 'SKILL10') {
        $discount = round($course['price'] * 0.10);
    } elseif ($coupon_code === 'WELCOME500' && $course['price'] > 1000) {
        $discount = 500;
    }
}

// অর্ডার সাবমিশন প্রসেসিং (Core PHP POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $cust_name = trim($_POST['customer_name'] ?? '');
        $mobile = Validator::normalizeMobile($_POST['mobile'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $address = trim($_POST['address'] ?? '');
        $selected_course_id = (int)($_POST['course_id'] ?? $course['id']);

        // রি-ফেচ কোর্স প্রাইস সরাসরি ডাটাবেজ থেকে
        $p_stmt = $conn->prepare("SELECT * FROM courses WHERE id = :id AND status = 'active' LIMIT 1");
        $p_stmt->execute([':id' => $selected_course_id]);
        $selected_course = $p_stmt->fetch();

        if (empty($cust_name) || empty($mobile)) {
            $error_msg = "Please enter your full name and mobile number.";
        } elseif (mb_strlen($cust_name, 'UTF-8') < 3) {
            $error_msg = "Name must be at least 3 characters long.";
        } elseif (!Validator::isValidIndianMobile($mobile)) {
            $error_msg = "Please enter a valid 10-digit Indian mobile number.";
        } elseif (!$selected_course) {
            $error_msg = "Selected course is invalid or inactive.";
        } else {
            $base_price = (float)$selected_course['price'];
            $final_price = max(0, $base_price - $discount);
            $order_number = "ORD-" . date('Y') . "-" . rand(10000, 99999);
            $enrollment_number = "ENR-" . date('Y') . "-" . rand(10000, 99999);

            try {
                $conn->beginTransaction();

                // ১. অর্ডার ইনসার্ট
                $order_stmt = $conn->prepare("
                    INSERT INTO orders 
                    (order_number, user_id, course_id, customer_name, mobile, email, address, total_amount, discount_amount, final_amount, coupon_code, payment_status, payment_method, enrollment_number, created_at)
                    VALUES 
                    (:onum, :uid, :cid, :cname, :mob, :email, :addr, :tamt, :damt, :famt, :coupon, 'Paid', 'Online / Test Gateway', :enum, NOW())
                ");

                $order_stmt->execute([
                    ':onum'   => $order_number,
                    ':uid'    => $logged_user['id'],
                    ':cid'    => $selected_course['id'],
                    ':cname'  => $cust_name,
                    ':mob'    => $mobile,
                    ':email'  => $email,
                    ':addr'   => $address,
                    ':tamt'   => $base_price,
                    ':damt'   => $discount,
                    ':famt'   => $final_price,
                    ':coupon' => $coupon_code ?: null,
                    ':enum'   => $enrollment_number
                ]);

                $order_id = (int)$conn->lastInsertId();

                // ২. পেমেন্ট রেকর্ড ইনসার্ট
                $payment_number = "PAY-" . date('Y') . "-" . rand(10000, 99999);
                $pay_stmt = $conn->prepare("
                    INSERT INTO payments 
                    (payment_number, order_id, user_id, amount, payment_method, gateway, gateway_payment_id, status, created_at)
                    VALUES 
                    (:pnum, :oid, :uid, :amt, 'Online', 'Test Gateway', :gid, 'Success', NOW())
                ");

                $pay_stmt->execute([
                    ':pnum' => $payment_number,
                    ':oid'  => $order_id,
                    ':uid'  => $logged_user['id'],
                    ':amt'  => $final_price,
                    ':gid'  => 'pay_sim_' . time()
                ]);

                // ৩. স্টুডেন্ট এনরোলমেন্ট ইনসার্ট
                $enr_stmt = $conn->prepare("
                    INSERT INTO enrollments 
                    (enrollment_number, user_id, course_id, order_id, status, created_at)
                    VALUES 
                    (:enum, :uid, :cid, :oid, 'active', NOW())
                ");

                $enr_stmt->execute([
                    ':enum' => $enrollment_number,
                    ':uid'  => $logged_user['id'],
                    ':cid'  => $selected_course['id'],
                    ':oid'  => $order_id
                ]);

                $conn->commit();

                security_log('ORDER_ENROLLMENT_COMPLETED', [
                    'order_number'      => $order_number,
                    'course_id'         => $selected_course['id'],
                    'final_amount'      => $final_price
                ], $logged_user['id']);

                // পেমেন্ট সাকসেস পেইজে রিডাইরেক্ট
                header("Location: checkout.php?success=1&order_number=" . urlencode($order_number) . "&course_title=" . urlencode($selected_course['title']) . "&amount=" . urlencode((string)$final_price));
                exit;

            } catch (Exception $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                error_log("Checkout Error: " . $e->getMessage());
                $error_msg = "Transaction could not be processed. Please try again.";
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-4 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #0d9488 100%);">
  <div class="container">
    <h1 class="fw-bold fs-3 mb-1"><i class="bi bi-cart-check-fill me-2"></i> Course Admission & Checkout</h1>
    <p class="mb-0 opacity-90 small">Complete your registration to secure your practical lab batch seat.</p>
  </div>
</section>

<!-- Checkout Wizard Body -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <!-- Success Order Receipt Modal Card -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 border-start border-4 border-success" style="background: var(--card-bg, #ffffff); max-width: 750px; margin: 0 auto;">
        <div class="card-body p-4 p-md-5 text-center">
          <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; font-size: 2.2rem;">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <h3 class="fw-bold text-dark mb-1">Admission & Enrollment Confirmed!</h3>
          <p class="text-muted small mb-4">Congratulations! Your admission seat has been confirmed in MB Computer Training Centre.</p>

          <div class="bg-subtle p-4 rounded-4 border text-start mb-4">
            <div class="row g-3">
              <div class="col-sm-6">
                <span class="text-muted small d-block">Order Reference Number:</span>
                <strong class="text-dark fs-6"><?php echo htmlspecialchars($_GET['order_number'] ?? ''); ?></strong>
              </div>
              <div class="col-sm-6">
                <span class="text-muted small d-block">Course Enrolled:</span>
                <strong class="text-primary fs-6"><?php echo htmlspecialchars($_GET['course_title'] ?? ''); ?></strong>
              </div>
              <div class="col-sm-6">
                <span class="text-muted small d-block">Amount Paid:</span>
                <strong class="text-success fs-5">₹<?php echo number_format((float)($_GET['amount'] ?? 0)); ?></strong>
              </div>
              <div class="col-sm-6">
                <span class="text-muted small d-block">Payment Status:</span>
                <span class="badge bg-success">Verified & Active</span>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-center gap-3">
            <a href="dashboard.php#tab-courses" class="btn btn-primary fw-bold px-4 shadow-sm">
              <i class="bi bi-mortarboard me-1"></i> Go to My Courses
            </a>
            <a href="index.php" class="btn btn-outline-secondary fw-semibold px-3">
              Back to Home
            </a>
          </div>
        </div>
      </div>
    <?php else: ?>

      <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger rounded-4 mb-4 shadow-sm">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Left Column: Student Details -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
            <div class="card-body p-4 p-md-5">
              <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                <i class="bi bi-person-badge text-primary me-2"></i> Student Admission Information
              </h5>

              <form method="POST" action="checkout.php?course_id=<?php echo $course['id']; ?>" id="checkoutForm">
                <?php echo csrf_field(); ?>
                
                <!-- Selected Course Dropdown -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark">Selected Course *</label>
                  <select name="course_id" class="form-select" onchange="window.location.href='checkout.php?course_id=' + this.value;">
                    <?php foreach ($all_courses as $ac): ?>
                      <option value="<?php echo $ac['id']; ?>" <?php echo ($ac['id'] == $course['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ac['title']); ?> — ₹<?php echo number_format($ac['price']); ?> (<?php echo htmlspecialchars($ac['duration']); ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Student Full Name *</label>
                    <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? $logged_user['name'] ?? ''); ?>" required maxlength="80">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Mobile Number *</label>
                    <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($_POST['mobile'] ?? $logged_user['mobile'] ?? ''); ?>" pattern="[6-9][0-9]{9}" maxlength="10" required>
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $logged_user['email'] ?? ''); ?>" required maxlength="100">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Residential Address</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($_POST['address'] ?? $logged_user['address'] ?? ''); ?>" maxlength="255">
                  </div>
                </div>

                <!-- Payment Method Info -->
                <div class="p-3 bg-subtle rounded-3 border mb-4">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <span class="fw-bold text-dark small">Safe & Instant Admission</span>
                  </div>
                  <span class="small text-muted d-block">Instant enrollment confirmation, digital admission receipt, and batch allocation.</span>
                </div>

                <input type="hidden" name="coupon_code" value="<?php echo htmlspecialchars($coupon_code); ?>">
                <input type="hidden" name="confirm_payment" value="1">

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
                  <i class="bi bi-lock-fill me-1"></i> Confirm Admission & Pay ₹<?php echo number_format(max(0, $course['price'] - $discount)); ?>
                </button>
              </form>

            </div>
          </div>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 90px; background: var(--card-bg, #ffffff);">
            <div class="card-header bg-transparent border-bottom p-4">
              <h5 class="fw-bold text-dark mb-0">Admission Summary</h5>
            </div>
            <div class="card-body p-4">
              
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                  <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                  <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                  <span class="small text-muted"><?php echo htmlspecialchars($course['category'] ?? 'Professional'); ?> • <?php echo htmlspecialchars($course['duration']); ?></span>
                </div>
              </div>

              <!-- Coupon Code Form -->
              <form method="POST" action="checkout.php?course_id=<?php echo $course['id']; ?>" class="mb-4">
                <?php echo csrf_field(); ?>
                <div class="input-group">
                  <input type="text" name="coupon_code" class="form-control form-control-sm" placeholder="Promo code (e.g. SKILL10)" value="<?php echo htmlspecialchars($coupon_code); ?>" maxlength="20">
                  <button type="submit" class="btn btn-dark btn-sm fw-semibold">Apply</button>
                </div>
                <div class="form-text small" style="font-size: 0.72rem;">Try <strong>SKILL10</strong> for 10% discount</div>
              </form>

              <!-- Pricing Line Items -->
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Course Tuition Fee:</span>
                <span class="fw-semibold text-dark">₹<?php echo number_format($course['price']); ?></span>
              </div>

              <?php if ($discount > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                  <span class="small">Coupon Discount (<?php echo htmlspecialchars($coupon_code); ?>):</span>
                  <span class="fw-bold">- ₹<?php echo number_format($discount); ?></span>
                </div>
              <?php endif; ?>

              <div class="d-flex justify-content-between mb-3 text-muted small">
                <span>Registration & Lab Fees:</span>
                <span class="text-success fw-bold">FREE</span>
              </div>

              <hr>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold text-dark">Total Net Payable:</span>
                <span class="fw-bold text-primary fs-4">₹<?php echo number_format(max(0, $course['price'] - $discount)); ?></span>
              </div>

            </div>
          </div>
        </div>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
