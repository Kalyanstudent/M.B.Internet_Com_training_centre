<?php
/**
 * MB Internet And Digital Studio | Contact Us (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Validator.php';

$page_title = "Contact Us & Studio Location";
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error_msg = "Security check failed. Please refresh the page and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $mobile = Validator::normalizeMobile($_POST['mobile'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $subject = trim($_POST['subject'] ?? 'General Inquiry');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($mobile) || empty($message)) {
            $error_msg = "Please fill in your name, mobile number, and message.";
        } elseif (mb_strlen($name, 'UTF-8') < 3 || mb_strlen($name, 'UTF-8') > 80) {
            $error_msg = "Name must be between 3 and 80 characters long.";
        } elseif (!Validator::isValidIndianMobile($mobile)) {
            $error_msg = "Please enter a valid 10-digit Indian mobile number.";
        } elseif (!empty($email) && !Validator::isValidEmail($email)) {
            $error_msg = "Please enter a valid email address.";
        } else {
            security_log('CONTACT_FORM_SUBMISSION', ['name' => $name, 'subject' => $subject]);
            $success_msg = "Thank you, " . htmlspecialchars($name) . "! Your message has been received. Mukesh Da will get in touch with you shortly.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #0d9488 100%);">
  <div class="container">
    <h1 class="display-6 fw-bold mb-2"><i class="bi bi-geo-alt-fill me-2"></i> Contact Us & Studio Location</h1>
    <p class="lead opacity-90 mb-0" style="font-size: 1.05rem;">
      We are here to answer your queries about computer courses, tax filing, printing services, and gift orders.
    </p>
  </div>
</section>

<!-- Contact Form & Info Grid -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container">
    <div class="row g-5">
      <!-- Left Column: Studio Details -->
      <div class="col-lg-5">
        <div class="p-4 bg-white rounded-4 border shadow-sm mb-4" style="background-color: var(--card-bg, #ffffff);">
          <h4 class="fw-bold text-dark mb-4">Get In Touch</h4>
          
          <div class="d-flex gap-3 mb-4">
            <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-1">Registered Office</h6>
              <p class="small text-muted mb-0">M B Internet And Digital Studio, Dabadari, Paschim Medinipur, West Bengal - 721136, India<br><small class="text-primary">Geo Plus Code: CJ9J+M6X, Dabadari</small></p>
            </div>
          </div>

          <div class="d-flex gap-3 mb-4">
            <div class="rounded-circle bg-success-subtle text-success p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-1">Official Mobile & WhatsApp</h6>
              <p class="small text-muted mb-0"><a href="tel:+919775890661" class="text-decoration-none text-dark fw-bold">+91 97758 90661</a></p>
            </div>
          </div>

          <div class="d-flex gap-3 mb-4">
            <div class="rounded-circle bg-info-subtle text-info p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-1">Official Email Address</h6>
              <p class="small text-muted mb-0"><a href="mailto:mbidsmail@gmail.com" class="text-decoration-none text-dark">mbidsmail@gmail.com</a></p>
            </div>
          </div>

          <div class="d-flex gap-3">
            <div class="rounded-circle bg-warning-subtle text-warning p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
              <i class="bi bi-clock-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-1">Working Hours</h6>
              <p class="small text-muted mb-0">Monday - Sunday: 09:00 - 21:00 (Open All 7 Days)</p>
            </div>
          </div>
        </div>

        <div class="p-4 bg-white rounded-4 border shadow-sm text-center" style="background-color: var(--card-bg, #ffffff);">
          <h6 class="fw-bold text-dark mb-2">Connect Directly with Mukesh Da</h6>
          <div class="d-flex justify-content-center gap-2">
            <a href="https://wa.me/919775890661" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm px-3">
              <i class="bi bi-whatsapp me-1"></i> WhatsApp
            </a>
            <a href="https://www.facebook.com/MBIDS/photos_by" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm px-3">
              <i class="bi bi-facebook me-1"></i> Facebook
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: Inquiry Form -->
      <div class="col-lg-7">
        <div class="p-4 p-md-5 bg-white rounded-4 border shadow-sm" style="background-color: var(--card-bg, #ffffff);">
          <h4 class="fw-bold text-dark mb-3">Send an Online Inquiry</h4>
          <p class="small text-muted mb-4">Fill out the form below and we will get back to you within 2 hours.</p>

          <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success rounded-3 mb-4">
              <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="contact.php">
            <?php echo csrf_field(); ?>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Your Full Name *</label>
                <input type="text" name="name" class="form-control" placeholder="Rahul Sharma" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required minlength="3" maxlength="80">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Mobile Number *</label>
                <input type="tel" name="mobile" class="form-control" placeholder="9876543210" pattern="[6-9][0-9]{9}" maxlength="10" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">Topic / Subject</label>
                <select name="subject" class="form-select">
                  <option value="Course Admission">Computer Course Admission</option>
                  <option value="Tax / ITR Filing">Income Tax / GST Filing</option>
                  <option value="Digital Printing">Digital Form / Printing Services</option>
                  <option value="Mobile / Gift Inquiry">Mobile Accessories / Gift Item</option>
                  <option value="Other">Other Inquiry</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Your Message *</label>
              <textarea name="message" class="form-control" rows="4" placeholder="Tell us how we can assist you..." required maxlength="1000"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
              <i class="bi bi-send-fill me-1"></i> Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
