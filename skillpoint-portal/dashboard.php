<?php
/**
 * MB Internet And Digital Studio | Student Dashboard (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_user();

$logged_user = current_user();
$page_title = "User Dashboard | MB Studio";

$success_msg = "";
$error_msg = "";

// প্রোফাইল আপডেট প্রসেসিং (Core PHP POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $mobile = Validator::normalizeMobile($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $new_password = (string)($_POST['new_password'] ?? '');

        if (empty($name) || empty($mobile)) {
            $error_msg = "Name and mobile number are required.";
        } elseif (mb_strlen($name, 'UTF-8') < 3 || mb_strlen($name, 'UTF-8') > 80) {
            $error_msg = "Name must be between 3 and 80 characters long.";
        } elseif (!Validator::isValidIndianMobile($mobile)) {
            $error_msg = "Please enter a valid 10-digit Indian mobile number.";
        } elseif (!empty($new_password) && (strlen($new_password) < 6 || strlen($new_password) > 128)) {
            $error_msg = "New password must be at least 6 characters long.";
        } else {
            if (!empty($new_password)) {
                $pass_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET name = :name, mobile = :mobile, address = :addr, password_hash = :phash WHERE id = :id");
                $stmt->execute([':name' => $name, ':mobile' => $mobile, ':addr' => $address, ':phash' => $pass_hash, ':id' => $logged_user['id']]);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = :name, mobile = :mobile, address = :addr WHERE id = :id");
                $stmt->execute([':name' => $name, ':mobile' => $mobile, ':addr' => $address, ':id' => $logged_user['id']]);
            }
            security_log('USER_PROFILE_UPDATED', ['name' => $name, 'mobile' => $mobile], $logged_user['id']);
            $success_msg = "Your profile has been updated successfully!";
            $logged_user['name'] = $name;
            $logged_user['mobile'] = $mobile;
            $logged_user['address'] = $address;
            $_SESSION['user_name'] = $name;
        }
    }
}

// ১. ইউজারের এনরোল্ড কোর্সসমূহ
$courses_stmt = $conn->prepare("
    SELECT e.enrollment_number, e.status as enrollment_status, e.enrolled_at, e.id as enrollment_id,
           c.id as course_id, c.title, c.duration, c.hours, c.category, c.slug, c.icon
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = :uid
    ORDER BY e.id DESC
");
$courses_stmt->execute([':uid' => $logged_user['id']]);
$enrolled_courses = $courses_stmt->fetchAll();

// ২. ইউজারের অর্ডার হিস্ট্রি
$orders_stmt = $conn->prepare("
    SELECT o.*, c.title as course_title
    FROM orders o
    JOIN courses c ON o.course_id = c.id
    WHERE o.user_id = :uid
    ORDER BY o.id DESC
");
$orders_stmt->execute([':uid' => $logged_user['id']]);
$orders = $orders_stmt->fetchAll();

// ৩. ইউজারের ট্যাক্স ও সার্ভিস রিকোয়েস্টসমূহ
$requests_stmt = $conn->prepare("
    SELECT * FROM service_requests
    WHERE user_id = :uid
    ORDER BY id DESC
");
$requests_stmt->execute([':uid' => $logged_user['id']]);
$tax_requests = $requests_stmt->fetchAll();

// ৪. ইউজারের আপলোড করা ডকুমেন্টসমূহ
$docs_stmt = $conn->prepare("
    SELECT * FROM documents
    WHERE user_id = :uid
    ORDER BY id DESC
");
$docs_stmt->execute([':uid' => $logged_user['id']]);
$documents = $docs_stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-4 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <span class="badge bg-white text-primary fw-bold mb-1">User Account Dashboard</span>
        <h1 class="fw-bold fs-3 mb-0">Welcome, <?php echo htmlspecialchars($logged_user['name']); ?>!</h1>
        <span class="small opacity-75"><?php echo htmlspecialchars($logged_user['email']); ?> • Mobile: <?php echo htmlspecialchars($logged_user['mobile']); ?></span>
      </div>
      <a href="courses.php" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm align-self-start align-self-md-center">
        <i class="bi bi-mortarboard me-1"></i> Browse More Courses
      </a>
    </div>
  </div>
</section>

<!-- Dashboard Body -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">

    <?php if (!empty($success_msg)): ?>
      <div class="alert alert-success rounded-4 mb-4 shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div class="alert alert-danger rounded-4 mb-4 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="p-3 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <span class="text-muted small d-block mb-1">Enrolled Courses</span>
          <h3 class="fw-bold text-primary mb-0"><?php echo count($enrolled_courses); ?></h3>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <span class="text-muted small d-block mb-1">Tax Requests</span>
          <h3 class="fw-bold text-success mb-0"><?php echo count($tax_requests); ?></h3>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <span class="text-muted small d-block mb-1">Total Orders</span>
          <h3 class="fw-bold text-info mb-0"><?php echo count($orders); ?></h3>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <span class="text-muted small d-block mb-1">Documents Vault</span>
          <h3 class="fw-bold text-warning mb-0"><?php echo count($documents); ?></h3>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
      <div class="card-header bg-transparent border-bottom p-3">
        <ul class="nav nav-pills gap-2" id="dashTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active fw-semibold" id="courses-tab" data-bs-toggle="tab" data-bs-target="#tab-courses" type="button">
              <i class="bi bi-mortarboard-fill me-1"></i> My Courses (<?php echo count($enrolled_courses); ?>)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-semibold" id="requests-tab" data-bs-toggle="tab" data-bs-target="#tab-requests" type="button">
              <i class="bi bi-file-earmark-text-fill me-1"></i> Tax Requests (<?php echo count($tax_requests); ?>)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-semibold" id="orders-tab" data-bs-toggle="tab" data-bs-target="#tab-orders" type="button">
              <i class="bi bi-receipt me-1"></i> Orders & Invoices (<?php echo count($orders); ?>)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-semibold" id="profile-tab" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button">
              <i class="bi bi-person-gear me-1"></i> Edit Profile
            </button>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">
        <div class="tab-content" id="dashTabsContent">
          
          <!-- Tab 1: My Courses -->
          <div class="tab-pane fade show active" id="tab-courses">
            <?php if (!empty($enrolled_courses)): ?>
              <div class="row g-4">
                <?php foreach ($enrolled_courses as $ec): ?>
                  <div class="col-md-6">
                    <div class="p-3 bg-subtle rounded-4 border h-100 d-flex flex-column">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary-subtle text-primary border small"><?php echo htmlspecialchars($ec['enrollment_number']); ?></span>
                        <span class="badge bg-success-subtle text-success border small"><?php echo htmlspecialchars($ec['enrollment_status']); ?></span>
                      </div>
                      <h5 class="fw-bold fs-6 text-dark mb-1"><?php echo htmlspecialchars($ec['title']); ?></h5>
                      <div class="small text-muted mb-3"><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($ec['duration']); ?> (<?php echo htmlspecialchars($ec['hours'] ?? '45 Hours'); ?>)</div>
                      <div class="d-flex gap-2 mt-auto pt-2 border-top">
                        <a href="course-details.php?id=<?php echo $ec['course_id']; ?>" class="btn btn-sm btn-outline-primary flex-fill">
                          <i class="bi bi-journal-text me-1"></i> View Syllabus
                        </a>
                        <a href="contact.php" class="btn btn-sm btn-primary flex-fill">
                          <i class="bi bi-calendar-check me-1"></i> Batch Timings
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-5 text-muted">
                <i class="bi bi-mortarboard display-3 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No Enrolled Courses Yet</h5>
                <p class="small text-muted mb-3">Browse our computer courses catalog to learn Tally Prime, Excel, Word, or Web Development.</p>
                <a href="courses.php" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Browse Courses</a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Tab 2: Tax Requests -->
          <div class="tab-pane fade" id="tab-requests">
            <?php if (!empty($tax_requests)): ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle small">
                  <thead class="table-light">
                    <tr>
                      <th>Request No</th>
                      <th>Service Type</th>
                      <th>Period</th>
                      <th>Status</th>
                      <th>Submitted Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tax_requests as $tr): ?>
                      <tr>
                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($tr['request_number']); ?></td>
                        <td><?php echo htmlspecialchars($tr['service_type']); ?></td>
                        <td>FY <?php echo htmlspecialchars($tr['financial_year']); ?> / AY <?php echo htmlspecialchars($tr['assessment_year']); ?></td>
                        <td>
                          <?php if ($tr['status'] === 'Completed'): ?>
                            <span class="badge bg-success-subtle text-success border">Completed</span>
                          <?php elseif ($tr['status'] === 'Processing'): ?>
                            <span class="badge bg-info-subtle text-info border">Processing</span>
                          <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border">Pending</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-muted"><?php echo date('d M Y', strtotime($tr['created_at'])); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-text display-3 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No Tax Service Requests</h5>
                <p class="small text-muted mb-3">Submit your ITR or GST filing application online with document upload.</p>
                <a href="tax-request.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> New Tax Request</a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Tab 3: Orders & Invoices -->
          <div class="tab-pane fade" id="tab-orders">
            <?php if (!empty($orders)): ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle small">
                  <thead class="table-light">
                    <tr>
                      <th>Order No</th>
                      <th>Course</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $o): ?>
                      <tr>
                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($o['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($o['course_title']); ?></td>
                        <td class="fw-bold text-primary">₹<?php echo number_format($o['final_amount']); ?></td>
                        <td><span class="badge bg-success-subtle text-success border"><?php echo htmlspecialchars($o['payment_status']); ?></span></td>
                        <td class="text-muted"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                        <td>
                          <a href="payment-success.php?order_number=<?php echo htmlspecialchars($o['order_number']); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-receipt"></i> Receipt
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt display-3 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No Orders Found</h5>
                <p class="small text-muted">You have not purchased any courses yet.</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Tab 4: Edit Profile -->
          <div class="tab-pane fade" id="tab-profile">
            <form method="POST" action="dashboard.php" style="max-width: 600px;">
              <?php echo csrf_field(); ?>
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Full Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($logged_user['name']); ?>" required maxlength="80">
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Mobile Number *</label>
                  <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($logged_user['mobile']); ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Email Address</label>
                  <input type="email" class="form-control" value="<?php echo htmlspecialchars($logged_user['email']); ?>" disabled>
                  <div class="form-text small">Email cannot be changed.</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($logged_user['address'] ?? ''); ?>">
              </div>

              <div class="mb-4">
                <label class="form-label small fw-bold text-dark">New Password (leave empty to keep current)</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password if changing">
              </div>

              <input type="hidden" name="update_profile" value="1">

              <button type="submit" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-save me-1"></i> Save Changes
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
