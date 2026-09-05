<?php
/**
 * MB Internet And Digital Studio | User & Admin Login (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Validator.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/index.php");
        exit;
    } else {
        header("Location: dashboard.php");
        exit;
    }
}

$page_title = "Sign In | M B Internet And Digital Studio";
$error_message = "";
$redirect_url = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';
$prefill_role = isset($_GET['role']) ? trim($_GET['role']) : 'user';

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!verify_csrf()) {
        $error_message = "Security validation failed. Please refresh the page and try again.";
    } else {
        $identifier = strtolower(trim($_POST['identifier'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            $error_message = "Please enter your registered email/mobile number and password.";
        } else {
            $login_result = Auth::login($identifier, $password);

            if ($login_result['success']) {
                $user = $login_result['user'];
                
                // Safe redirect
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                    exit;
                } else {
                    if (!empty($redirect_url) && !str_contains($redirect_url, 'login.php') && !str_contains($redirect_url, 'logout.php')) {
                        header("Location: " . $redirect_url);
                        exit;
                    }
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $error_message = $login_result['message'] ?? "Invalid email/mobile number or password. Please try again.";
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Modern Responsive Auth Section with Background Image -->
<section class="py-5 d-flex align-items-center position-relative" style="min-height: 85vh; background: linear-gradient(135deg, rgba(15, 23, 42, 0.84) 0%, rgba(30, 58, 138, 0.88) 100%), url('assets/images/auth_bg.jpg') center/cover no-repeat fixed;">
  <div class="container py-3">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
        
        <!-- Glassmorphism Card -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);">
          <div class="card-body p-4 p-sm-5">
            
            <div class="text-center mb-4">
              <div class="brand-logo-badge d-inline-flex align-items-center justify-content-center text-white fw-bold rounded-4 mb-2 shadow" style="width: 56px; height: 56px; font-size: 1.45rem; background: linear-gradient(135deg, #1e40af, #2563eb);">
                MB
              </div>
              <h4 class="fw-bold text-dark mb-1">Account Sign In</h4>
              <p class="small text-muted mb-0">Sign in to access your User Dashboard or Admin Console</p>
            </div>

            <!-- Quick One-Click Demo Credential Buttons -->
            <div class="p-3 bg-light rounded-4 border mb-4 shadow-sm">
              <span class="small fw-bold text-dark d-block mb-2 text-center text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Quick Login Credentials:</span>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm flex-fill fw-semibold rounded-3 py-2" onclick="fillCredentials('user@mbinternet.com', 'student123')">
                  <i class="bi bi-person-fill me-1"></i> User Demo
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm flex-fill fw-semibold rounded-3 py-2" onclick="fillCredentials('admin@mbinternet.com', 'admin123')">
                  <i class="bi bi-shield-lock-fill me-1"></i> Admin Login
                </button>
              </div>
            </div>

            <?php if (isset($_GET['logged_out'])): ?>
              <div class="alert alert-success rounded-3 small py-2 px-3 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>You have been logged out successfully.</span>
              </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
              <div class="alert alert-danger rounded-3 small py-2 px-3 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
              </div>
            <?php endif; ?>

            <form method="POST" action="login.php<?php echo !empty($redirect_url) ? '?redirect=' . urlencode($redirect_url) : ''; ?>">
              <?php echo csrf_field(); ?>

              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Email Address or Mobile Number *</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                  <input type="text" name="identifier" id="loginIdentifier" class="form-control border-start-0 ps-0" placeholder="user@mbinternet.com or 9775890661" value="<?php echo ($prefill_role === 'admin') ? 'admin@mbinternet.com' : 'user@mbinternet.com'; ?>" required autofocus maxlength="100">
                </div>
              </div>

              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-bold text-dark mb-0">Password *</label>
                </div>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                  <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Enter password" value="<?php echo ($prefill_role === 'admin') ? 'admin123' : 'student123'; ?>" required maxlength="128">
                  <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="togglePasswordVisibility()">
                    <i class="bi bi-eye text-muted" id="passEyeIcon"></i>
                  </button>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Account
              </button>

              <div class="text-center small text-muted">
                Don't have an account yet? 
                <a href="register.php<?php echo !empty($redirect_url) ? '?redirect=' . urlencode($redirect_url) : ''; ?>" class="text-primary fw-bold text-decoration-none">Create New Account</a>
              </div>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  function fillCredentials(id, pass) {
    document.getElementById('loginIdentifier').value = id;
    document.getElementById('loginPassword').value = pass;
  }

  function togglePasswordVisibility() {
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('passEyeIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash text-muted';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye text-muted';
    }
  }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
