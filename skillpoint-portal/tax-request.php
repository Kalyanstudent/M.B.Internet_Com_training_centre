<?php
/**
 * MB Internet And Digital Studio | Tax Service Request & Document Upload (Core PHP)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Validator.php';
require_once __DIR__ . '/includes/FileUploader.php';

// Authentication required
require_user();

$page_title = "Submit Tax Service Request";
$logged_user = current_user();

$service_param = isset($_GET['service']) ? trim($_GET['service']) : 'Income Tax (ITR) Filing';

$success_message = "";
$error_message = "";
$created_request = null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error_message = "Security validation failed. Please refresh the page and try again.";
    } else {
        $service_type = trim($_POST['service_type'] ?? '');
        $customer_name = trim($_POST['customer_name'] ?? '');
        $mobile = Validator::normalizeMobile($_POST['mobile'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $address = trim($_POST['address'] ?? '');
        $pan_number = strtoupper(trim($_POST['pan_number'] ?? ''));
        $financial_year = trim($_POST['financial_year'] ?? '2025-2026');
        $assessment_year = trim($_POST['assessment_year'] ?? '2026-2027');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($service_type) || empty($customer_name) || empty($mobile)) {
            $error_message = "Please fill in your name, mobile number, and select the tax service.";
        } elseif (!Validator::isValidIndianMobile($mobile)) {
            $error_message = "Please enter a valid 10-digit Indian mobile number.";
        } elseif (!empty($pan_number) && !Validator::isValidPAN($pan_number)) {
            $error_message = "Please enter a valid 10-character Indian PAN number (e.g. ABCDE1234F).";
        } elseif (!empty($email) && !Validator::isValidEmail($email)) {
            $error_message = "Please enter a valid email address.";
        } else {
            try {
                $conn->beginTransaction();

                $request_number = "REQ-" . date('Y') . "-" . rand(10000, 99999);

                $stmt = $conn->prepare("
                    INSERT INTO service_requests 
                    (request_number, user_id, customer_name, mobile, email, address, pan_number, financial_year, assessment_year, service_type, internal_notes, status, created_at)
                    VALUES 
                    (:req_num, :user_id, :c_name, :mob, :email, :addr, :pan, :fy, :ay, :stype, :notes, 'Pending', NOW())
                ");

                $stmt->execute([
                    ':req_num' => $request_number,
                    ':user_id' => $logged_user['id'],
                    ':c_name'  => $customer_name,
                    ':mob'     => $mobile,
                    ':email'   => $email,
                    ':addr'    => $address,
                    ':pan'     => $pan_number,
                    ':fy'      => $financial_year,
                    ':ay'      => $assessment_year,
                    ':stype'   => $service_type,
                    ':notes'   => $notes
                ]);

                $request_id = (int)$conn->lastInsertId();

                // Process attached document files
                if (!empty($_FILES['documents']['name'][0])) {
                    foreach ($_FILES['documents']['name'] as $key => $filename) {
                        if (empty($filename)) continue;

                        $fileSingle = [
                            'name'     => $_FILES['documents']['name'][$key],
                            'type'     => $_FILES['documents']['type'][$key],
                            'tmp_name' => $_FILES['documents']['tmp_name'][$key],
                            'error'    => $_FILES['documents']['error'][$key],
                            'size'     => $_FILES['documents']['size'][$key]
                        ];

                        $uploadRes = FileUploader::uploadDocument($fileSingle, $logged_user['id'], $request_id);
                        if (!$uploadRes['success']) {
                            // Non-fatal warning or continue
                            error_log("Upload notice for file {$filename}: " . $uploadRes['message']);
                        }
                    }
                }

                $conn->commit();

                security_log('TAX_REQUEST_CREATED', [
                    'request_number' => $request_number,
                    'service_type'   => $service_type
                ], $logged_user['id']);

                $success_message = "Your service request has been submitted successfully! Request ID: <strong>" . htmlspecialchars($request_number) . "</strong>";
                $created_request = [
                    'request_number' => $request_number,
                    'service_type'   => $service_type,
                    'customer_name'  => $customer_name
                ];

            } catch (Exception $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                error_log("Tax Request DB Error: " . $e->getMessage());
                $error_message = "An error occurred while saving your request. Please try again.";
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);">
  <div class="container">
    <h1 class="display-6 fw-bold mb-2"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Submit Tax & ITR Service Request</h1>
    <p class="lead opacity-90 mb-0" style="font-size: 1.05rem;">
      Upload your financial documents safely for Income Tax Return (ITR), GST Registration/Filing, or PAN Card services.
    </p>
  </div>
</section>

<!-- Form Section -->
<section class="py-5" style="background-color: var(--section-bg, #f8fafc);">
  <div class="container" style="max-width: 850px;">

    <?php if (!empty($success_message)): ?>
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 border-start border-4 border-success" style="background: var(--card-bg, #ffffff);">
        <div class="card-body p-4 text-center">
          <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Application Submitted Successfully!</h4>
          <p class="text-muted mb-4"><?php echo $success_message; ?></p>
          <div class="d-flex justify-content-center gap-3">
            <a href="dashboard.php#tab-requests" class="btn btn-primary fw-bold px-4">
              <i class="bi bi-grid-fill me-1"></i> Track in Dashboard
            </a>
            <a href="https://wa.me/919775890661?text=Hello%20Mukesh%20Da,%20I%20have%20submitted%20tax%20service%20request%20<?php echo urlencode($created_request['request_number'] ?? ''); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold px-4">
              <i class="bi bi-whatsapp me-1"></i> Update on WhatsApp
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger rounded-4 mb-4 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
      <div class="card-header bg-transparent border-bottom p-4">
        <h5 class="fw-bold text-dark mb-1">Service & Applicant Details</h5>
        <span class="small text-muted">All documents uploaded here are encrypted and strictly confidential</span>
      </div>
      <div class="card-body p-4 p-md-5">

        <form method="POST" action="tax-request.php" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <!-- Service Type Selection -->
          <div class="mb-4">
            <label class="form-label fw-bold text-dark small">Select Tax / Compliance Service *</label>
            <select name="service_type" class="form-select form-select-lg" required>
              <option value="Income Tax (ITR) Filing" <?php echo ($service_param === 'Income Tax (ITR) Filing' || $service_param === 'itr') ? 'selected' : ''; ?>>Income Tax (ITR) Filing (Salaried / Business / Freelancer)</option>
              <option value="GST Registration & Filing" <?php echo ($service_param === 'GST Registration & Filing' || $service_param === 'gst') ? 'selected' : ''; ?>>GST Registration & Monthly/Quarterly Filing</option>
              <option value="PAN Card Application / Correction" <?php echo ($service_param === 'pan') ? 'selected' : ''; ?>>New PAN Card Application / Name or DOB Correction</option>
              <option value="TDS Return & Compliance" <?php echo ($service_param === 'tds') ? 'selected' : ''; ?>>TDS Return Preparation & Form 16/16A Issuance</option>
              <option value="Trade License & MSME Udyam" <?php echo ($service_param === 'msme') ? 'selected' : ''; ?>>Trade License & MSME Udyam Registration</option>
              <option value="Accounting & Audit Services">Business Accounting Bookkeeping & Audit Consultation</option>
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Applicant Full Name *</label>
              <input type="text" name="customer_name" class="form-control" placeholder="e.g. Mukesh Bhattacharya" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? $logged_user['name']); ?>" required maxlength="80">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Mobile Number *</label>
              <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile" pattern="[6-9][0-9]{9}" maxlength="10" value="<?php echo htmlspecialchars($_POST['mobile'] ?? $logged_user['mobile']); ?>" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="client@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? $logged_user['email']); ?>" maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">PAN Number (10 Characters)</label>
              <input type="text" name="pan_number" class="form-control text-uppercase" placeholder="ABCDE1234F" maxlength="10" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" value="<?php echo htmlspecialchars($_POST['pan_number'] ?? ''); ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Financial Year</label>
              <select name="financial_year" class="form-select">
                <option value="2025-2026" selected>FY 2025-2026</option>
                <option value="2024-2025">FY 2024-2025</option>
                <option value="2023-2024">FY 2023-2024</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Assessment Year</label>
              <select name="assessment_year" class="form-select">
                <option value="2026-2027" selected>AY 2026-2027</option>
                <option value="2025-2026">AY 2025-2026</option>
                <option value="2024-2025">AY 2024-2025</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold text-dark">Postal Address / Locality</label>
            <input type="text" name="address" class="form-control" placeholder="Near Lokkhi Bajar Market, Dabadari" value="<?php echo htmlspecialchars($_POST['address'] ?? $logged_user['address'] ?? ''); ?>" maxlength="255">
          </div>

          <!-- Document Upload Box -->
          <div class="p-4 rounded-4 bg-subtle border mb-4">
            <label class="form-label fw-bold text-dark mb-1">Upload Supporting Documents (Optional)</label>
            <p class="small text-muted mb-3">Upload Form 16, Aadhaar, Bank Statement, PAN scan or previous ITR copy (Allowed: PDF, JPG, PNG, Excel; Max: 5MB per file)</p>
            <input type="file" name="documents[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls">
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold text-dark">Additional Notes or Instructions</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Provide any special instructions, deductions or tax details..." maxlength="1000"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
            <i class="bi bi-shield-check me-2"></i> Submit Confidential Request
          </button>
        </form>

      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
