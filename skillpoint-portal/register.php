<?php
/**
 * MB Internet And Digital Studio | User Registration (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Validator.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Create User Account | M B Internet And Digital Studio";
$error_message = "";
$redirect_url = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';

// Process Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error_message = "Security check failed. Please refresh the form and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $mobile = Validator::normalizeMobile($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirm_password = (string)($_POST['confirm_password'] ?? '');

        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            $error_message = "Please fill in all required fields marked with *.";
        } elseif (mb_strlen($name, 'UTF-8') < 3 || mb_strlen($name, 'UTF-8') > 80) {
            $error_message = "Full name must be between 3 and 80 characters.";
        } elseif (!Validator::isValidEmail($email)) {
            $error_message = "Please enter a valid email address.";
        } elseif (!Validator::isValidIndianMobile($mobile)) {
            $error_message = "Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.";
        } elseif (strlen($password) < 6 || strlen($password) > 128) {
            $error_message = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match. Please re-type your password carefully.";
        } else {
            // Check for existing account
            $check = $conn->prepare("SELECT id FROM users WHERE email = :email OR mobile = :mobile LIMIT 1");
            $check->execute([':email' => $email, ':mobile' => $mobile]);
            if ($check->fetch()) {
                $error_message = "An account with this email or mobile number already exists.";
            } else {
                $user_code = "usr-" . rand(100, 999) . "-" . rand(1000, 9999);
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("
                    INSERT INTO users (user_code, name, email, mobile, password_hash, address, role, status, created_at)
                    VALUES (:ucode, :name, :email, :mobile, :phash, :addr, 'user', 'active', NOW())
                ");

                $stmt->execute([
                    ':ucode'  => $user_code,
                    ':name'   => $name,
                    ':email'  => $email,
                    ':mobile' => $mobile,
                    ':phash'  => $password_hash,
                    ':addr'   => $address
                ]);

                $new_user_id = (int)$conn->lastInsertId();

                // Prevent session fixation
                session_regenerate_id(true);

                // Auto login
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['user_code'] = $user_code;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = 'user';

                security_log('USER_REGISTERED', ['user_code' => $user_code], $new_user_id);

                if (!empty($redirect_url) && !str_contains($redirect_url, 'register.php') && !str_contains($redirect_url, 'login.php')) {
                    header("Location: " . $redirect_url);
                    exit;
                }

                header("Location: dashboard.php");
                exit;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Register Section with Background Image -->
<section class="py-5 d-flex align-items-center position-relative" style="min-height: 85vh; background: linear-gradient(135deg, rgba(15, 23, 42, 0.84) 0%, rgba(30, 58, 138, 0.88) 100%), url('assets/images/auth_bg.jpg') center/cover no-repeat fixed;">
  <div class="container py-3">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
        
        <!-- Glassmorphism Card -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);">
          <div class="card-body p-4 p-sm-5">
            
            <div class="text-center mb-4">
              <div class="brand-logo-badge d-inline-flex align-items-center justify-content-center text-white fw-bold rounded-4 mb-2 shadow" style="width: 56px; height: 56px; font-size: 1.45rem; background: linear-gradient(135deg, #10b981, #059669);">
                MB
              </div>
              <h4 class="fw-bold text-dark mb-1">Create User Account</h4>
              <p class="small text-muted mb-0">Enroll in computer courses and track your digital & tax service requests</p>
            </div>

            <?php if (!empty($error_message)): ?>
              <div class="alert alert-danger rounded-3 small py-2 px-3 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
              </div>
            <?php endif; ?>

            <form method="POST" action="register.php<?php echo !empty($redirect_url) ? '?redirect=' . urlencode($redirect_url) : ''; ?>">
              <?php echo csrf_field(); ?>

              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Full Name *</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                  <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required minlength="3" maxlength="80" autofocus value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Mobile Number *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-phone text-muted"></i></span>
                    <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile" pattern="[6-9][0-9]{9}" maxlength="10" required value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">
                  </div>
                  <div class="form-text small" style="font-size: 0.70rem;">10 digits starting with 6-9</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Email Address *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="rahul@gmail.com" required maxlength="100" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Address / Village / City</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-geo-alt text-muted"></i></span>
                  <input type="text" name="address" class="form-control" placeholder="Near Lokkhi Bajar Market, Dabadari" maxlength="255" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                </div>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Password *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 chars" minlength="6" maxlength="128" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-dark">Confirm Password *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-shield-check text-muted"></i></span>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-type password" minlength="6" maxlength="128" required>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-2 mb-3">
                <i class="bi bi-person-plus-fill me-1"></i> Register Account
              </button>

              <div class="text-center small text-muted">
                Already have an account? 
                <a href="login.php<?php echo !empty($redirect_url) ? '?redirect=' . urlencode($redirect_url) : ''; ?>" class="text-primary fw-bold text-decoration-none">Sign In</a>
              </div>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
