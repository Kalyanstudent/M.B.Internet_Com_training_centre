/**
 * MB Internet And Digital Studio | Tax Service Request & Document Upload Controller
 * Supports Drag & Drop Document Upload, Multi-File Preview, Client Validation, and Form Submission
 */

let pendingUploadFiles = [];

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.syncSession();

  // Pre-select service from URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const serviceParam = urlParams.get('service');
  const serviceSelect = document.getElementById('req-service-type');
  if (serviceSelect && serviceParam) {
    if (serviceParam === 'itr-filing') serviceSelect.value = 'Income Tax (ITR) Filing';
    else if (serviceParam === 'gst-services') serviceSelect.value = 'GST Registration & Monthly Filing';
    else if (serviceParam === 'pan-services') serviceSelect.value = 'PAN & Aadhaar Services';
    else if (serviceParam === 'tds-returns') serviceSelect.value = 'TDS Returns & Refund Assistance';
    else if (serviceParam === 'dsc-token') serviceSelect.value = 'Class 3 Digital Signature (DSC)';
    else if (serviceParam === 'udyam-msme') serviceSelect.value = 'Udyam MSME Registration';
  }

  // Pre-fill user data if logged in
  if (user && user.id) {
    if (document.getElementById('req-name')) document.getElementById('req-name').value = user.name || '';
    if (document.getElementById('req-mobile')) document.getElementById('req-mobile').value = user.mobile || '';
    if (document.getElementById('req-email')) document.getElementById('req-email').value = user.email || '';
    if (document.getElementById('req-address')) document.getElementById('req-address').value = user.address || '';
  }

  initDragAndDrop();
  initFormSubmission();
});

function initDragAndDrop() {
  const dropZone = document.getElementById('tax-drop-zone');
  const fileInput = document.getElementById('tax-file-input');

  if (!dropZone || !fileInput) return;

  dropZone.addEventListener('click', () => fileInput.click());

  ['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropZone.style.borderColor = 'var(--primary)';
      dropZone.style.background = 'var(--primary-subtle)';
    }, false);
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropZone.style.borderColor = 'var(--border)';
      dropZone.style.background = 'var(--bg-subtle)';
    }, false);
  });

  dropZone.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
  });

  fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
  });
}

function handleFiles(files) {
  const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
  const maxSizeBytes = 5 * 1024 * 1024; // 5 MB

  Array.from(files).forEach(file => {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!allowedExtensions.includes(ext)) {
      showToast(`File "${file.name}" rejected: Only PDF, JPG, PNG, Excel files are permitted.`, 'error');
      return;
    }

    if (file.size > maxSizeBytes) {
      showToast(`File "${file.name}" exceeds the 5MB size limit.`, 'error');
      return;
    }

    // Check duplicate
    if (pendingUploadFiles.some(f => f.name === file.name && f.size === file.size)) {
      showToast(`"${file.name}" is already in the upload queue.`, 'warning');
      return;
    }

    pendingUploadFiles.push(file);
  });

  renderUploadedFilesList();
}

function renderUploadedFilesList() {
  const container = document.getElementById('uploaded-files-container');
  if (!container) return;

  if (pendingUploadFiles.length === 0) {
    container.innerHTML = '';
    return;
  }

  container.innerHTML = pendingUploadFiles.map((file, idx) => {
    const sizeKB = Math.round(file.size / 1024);
    const sizeStr = sizeKB > 1024 ? (sizeKB / 1024).toFixed(1) + ' MB' : sizeKB + ' KB';
    const isPDF = file.name.endsWith('.pdf');

    return `
      <div class="d-flex align-items-center justify-content-between p-3 bg-subtle rounded-3 border mb-2">
        <div class="d-flex align-items-center gap-3 text-truncate">
          <i class="bi ${isPDF ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary'} fs-4"></i>
          <div class="text-truncate">
            <div class="fw-semibold text-dark text-truncate small">${file.name}</div>
            <div class="small text-muted" style="font-size: 0.75rem;">${sizeStr}</div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePendingFile(${idx})" title="Remove">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
  }).join('');
}

function removePendingFile(index) {
  pendingUploadFiles.splice(index, 1);
  renderUploadedFilesList();
}

function initFormSubmission() {
  const form = document.getElementById('tax-request-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const user = Auth.getCurrentUser();
    if (!user || !user.id) {
      showToast('Please login or register before submitting a tax service request.', 'info');
      setTimeout(() => {
        window.location.href = `login.html?redirect=${encodeURIComponent(window.location.href)}`;
      }, 500);
      return;
    }

    const serviceType = document.getElementById('req-service-type').value;
    const name = document.getElementById('req-name').value.trim();
    const mobile = document.getElementById('req-mobile').value.trim();
    const email = document.getElementById('req-email').value.trim();
    const address = document.getElementById('req-address').value.trim();
    const pan = document.getElementById('req-pan').value.trim().toUpperCase();
    const fy = document.getElementById('req-fy').value;
    const ay = document.getElementById('req-ay').value;
    const notes = document.getElementById('req-notes').value.trim();

    if (!name || !/^[6-9]\d{9}$/.test(mobile) || !email) {
      showToast('Please provide your name, valid 10-digit mobile number, and email address.', 'error');
      return;
    }

    const btn = document.getElementById('btn-submit-tax-request');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Submitting Application...`;

    try {
      // 1. Submit Request Meta to API
      const reqRes = await API.post('api/service-requests/create.php', {
        service_type: serviceType,
        customer_name: name,
        mobile: mobile,
        email: email,
        address: address,
        pan_number: pan,
        financial_year: fy,
        assessment_year: ay,
        internal_notes: notes
      });

      if (!reqRes.success || !reqRes.data) {
        showToast(reqRes.message || 'Failed to submit request.', 'error');
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-send-check-fill me-1"></i> Submit Tax Service Request`;
        return;
      }

      const createdRequest = reqRes.data;

      // 2. Upload any attached documents to Secure Vault
      if (pendingUploadFiles.length > 0) {
        for (const file of pendingUploadFiles) {
          const formData = new FormData();
          formData.append('document', file);
          formData.append('request_id', createdRequest.id);

          await API.upload('api/documents/upload.php', formData);
        }
      }

      // 3. Show Success Modal
      document.getElementById('success-modal-req-id').textContent = createdRequest.request_number;
      document.getElementById('success-modal-service').textContent = createdRequest.service_type;
      document.getElementById('success-modal-name').textContent = createdRequest.customer_name;

      const successModal = new bootstrap.Modal(document.getElementById('taxSuccessModal'));
      successModal.show();
    } catch (err) {
      showToast('Network error while submitting tax request.', 'error');
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-send-check-fill me-1"></i> Submit Tax Service Request`;
    }
  });
}
