<?php
/**
 * MB Internet And Digital Studio | Business Settings Admin (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

require_admin();

$page_title = "Business Profile & Settings";
$success_msg = "";
$error_msg = "";

// সেটিংস সেভ হ্যান্ডলিং (Core PHP POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $settings = [
            'business_name'       => trim($_POST['business_name'] ?? 'MB Internet And Digital Studio'),
            'sub_brand'           => trim($_POST['sub_brand'] ?? 'Mobile & Gift House'),
            'owner_name'          => trim($_POST['owner_name'] ?? 'Mukesh Bhattacharya'),
            'gstin'               => trim($_POST['gstin'] ?? ''),
            'phone'               => Validator::normalizeMobile($_POST['phone'] ?? ''),
            'email'               => strtolower(trim($_POST['email'] ?? '')),
            'facebook_url'        => trim($_POST['facebook_url'] ?? 'https://www.facebook.com/MBIDS/photos_by'),
            'address'             => trim($_POST['address'] ?? ''),
            'razorpay_key_id'     => trim($_POST['razorpay_key_id'] ?? ''),
            'razorpay_key_secret' => trim($_POST['razorpay_key_secret'] ?? '')
        ];

        $u_stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v");
        foreach ($settings as $key => $val) {
            $u_stmt->execute([':k' => $key, ':v' => $val]);
        }
        security_log('ADMIN_SETTINGS_UPDATED', ['keys_updated' => array_keys($settings)]);
        $success_msg = "Business settings and payment keys saved successfully!";
    }
}

// সেটিংস ফেচ করা
$s_stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
$saved_settings = $s_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/includes/header.php';
?>

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

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-gear-fill text-primary me-2"></i> Business Information & Gateway Settings</h6>
  </div>
  <div class="card-body p-4 p-md-5">
    <form method="POST" action="settings.php">
      <?php echo csrf_field(); ?>
      
      <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Business Profile</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Business Name</label>
          <input type="text" name="business_name" class="form-control" value="<?php echo htmlspecialchars($saved_settings['business_name'] ?? 'MB Internet And Digital Studio'); ?>" required maxlength="120">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Sub-Brand Tagline</label>
          <input type="text" name="sub_brand" class="form-control" value="<?php echo htmlspecialchars($saved_settings['sub_brand'] ?? 'Mobile & Gift House'); ?>" maxlength="120">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Proprietor / Owner Name</label>
          <input type="text" name="owner_name" class="form-control" value="<?php echo htmlspecialchars($saved_settings['owner_name'] ?? 'Mukesh Bhattacharya'); ?>" required maxlength="80">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">GSTIN Number (Optional)</label>
          <input type="text" name="gstin" class="form-control text-uppercase" placeholder="19AAAAA0000A1Z5" value="<?php echo htmlspecialchars($saved_settings['gstin'] ?? ''); ?>" maxlength="15">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Official Mobile / WhatsApp Number</label>
          <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($saved_settings['phone'] ?? '9775890661'); ?>" pattern="[6-9][0-9]{9}" maxlength="10" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Official Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($saved_settings['email'] ?? 'mbidsmail@gmail.com'); ?>" required maxlength="100">
        </div>
        <div class="col-12">
          <label class="form-label small fw-bold text-dark">Facebook Page URL</label>
          <input type="url" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($saved_settings['facebook_url'] ?? 'https://www.facebook.com/MBIDS/photos_by'); ?>" maxlength="255">
        </div>
        <div class="col-12">
          <label class="form-label small fw-bold text-dark">Physical Registered Address</label>
          <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($saved_settings['address'] ?? 'Dabadari, Paschim Medinipur, West Bengal - 721136, India (Plus Code: CJ9J+M6X)'); ?>" required maxlength="255">
        </div>
      </div>

      <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Razorpay Payment Gateway API</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Razorpay Key ID</label>
          <input type="text" name="razorpay_key_id" class="form-control font-monospace" placeholder="rzp_test_..." value="<?php echo htmlspecialchars($saved_settings['razorpay_key_id'] ?? ''); ?>" maxlength="64">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Razorpay Key Secret</label>
          <input type="password" name="razorpay_key_secret" class="form-control font-monospace" placeholder="••••••••••••••••" value="<?php echo htmlspecialchars($saved_settings['razorpay_key_secret'] ?? ''); ?>" maxlength="64">
        </div>
      </div>

      <input type="hidden" name="save_settings" value="1">
      <button type="submit" class="btn btn-primary fw-bold px-4">
        <i class="bi bi-save me-1"></i> Save All Settings
      </button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
