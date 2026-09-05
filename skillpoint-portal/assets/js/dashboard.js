/**
 * MB Internet And Digital Studio | Student Dashboard Controller
 * Enforces Student Auth Guard, Loads Enrolled Courses, Orders, Tax Requests, Documents & Profile
 */

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.syncSession();

  // Strict Student User Guard
  if (!user || user.role !== 'user') {
    showToast('Please log in with your student account to access the dashboard.', 'error');
    setTimeout(() => {
      window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.href);
    }, 600);
    return;
  }

  // Populate Greeting & Info
  const greetingEl = document.getElementById('dash-user-greeting');
  const emailEl = document.getElementById('dash-user-email');
  if (greetingEl) greetingEl.textContent = `Welcome, ${user.name}!`;
  if (emailEl) emailEl.textContent = `${user.email} | Mobile: ${user.mobile}`;

  // Populate Profile Form
  if (document.getElementById('prof-name')) document.getElementById('prof-name').value = user.name || '';
  if (document.getElementById('prof-mobile')) document.getElementById('prof-mobile').value = user.mobile || '';
  if (document.getElementById('prof-email')) document.getElementById('prof-email').value = user.email || '';
  if (document.getElementById('prof-address')) document.getElementById('prof-address').value = user.address || '';

  // Handle Hash Navigation
  if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    if (hash === 'my-courses') {
      const btn = document.getElementById('tab-my-courses-btn');
      if (btn) new bootstrap.Tab(btn).show();
    } else if (hash === 'tax-requests') {
      const btn = document.getElementById('tab-my-requests-btn');
      if (btn) new bootstrap.Tab(btn).show();
    } else if (hash === 'profile') {
      const btn = document.getElementById('tab-my-profile-btn');
      if (btn) new bootstrap.Tab(btn).show();
    }
  }

  await loadStudentDashboardData();
});

async function loadStudentDashboardData() {
  try {
    const res = await API.get('api/user/dashboard.php');
    if (res.success && res.data) {
      const { courses, orders, requests, documents, stats } = res.data;

      // Update KPI Badges
      if (document.getElementById('kpi-courses-count')) {
        document.getElementById('kpi-courses-count').textContent = stats.enrolled_courses || 0;
      }
      if (document.getElementById('kpi-requests-count')) {
        document.getElementById('kpi-requests-count').textContent = stats.tax_requests || 0;
      }
      if (document.getElementById('kpi-orders-count')) {
        document.getElementById('kpi-orders-count').textContent = stats.total_orders || 0;
      }

      // Render Tab Sections
      renderMyCourses(courses);
      renderMyOrders(orders);
      renderMyRequests(requests);
      renderMyDocuments(documents);
    }
  } catch (err) {
    console.error('Failed to load dashboard:', err);
    showToast('Failed to load dashboard records.', 'error');
  }
}

function renderMyCourses(courses = []) {
  const container = document.getElementById('dash-courses-list');
  if (!container) return;

  if (courses.length === 0) {
    container.innerHTML = `
      <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-mortarboard display-4 d-block mb-3 text-muted"></i>
        <h5 class="fw-bold text-dark">No Enrolled Courses Yet</h5>
        <p class="small text-muted mb-3">Browse our computer courses catalog to learn Tally Prime, Excel, Word, or Web Development.</p>
        <a href="courses.html" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Browse Courses</a>
      </div>
    `;
    return;
  }

  container.innerHTML = courses.map(c => `
    <div class="col-md-6">
      <div class="p-3 bg-subtle rounded-4 border h-100 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-primary-subtle text-primary border small">${c.enrollment_number}</span>
          <span class="badge bg-success-subtle text-success border small">Active</span>
        </div>
        <h5 class="fw-bold fs-6 text-dark mb-1">${c.title}</h5>
        <div class="small text-muted mb-3"><i class="bi bi-clock me-1"></i> ${c.duration} (${c.hours || '45 Hours'})</div>
        <div class="d-flex gap-2 mt-auto pt-2 border-top">
          <a href="course-details.html?slug=${c.slug}" class="btn btn-sm btn-outline-primary flex-fill">
            <i class="bi bi-journal-text me-1"></i> View Syllabus
          </a>
          <a href="contact.html" class="btn btn-sm btn-primary flex-fill">
            <i class="bi bi-calendar-check me-1"></i> Batch Timing
          </a>
        </div>
      </div>
    </div>
  `).join('');
}

function renderMyOrders(orders = []) {
  const tbody = document.getElementById('dash-orders-table-body');
  if (!tbody) return;

  if (orders.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No course purchase orders found.</td></tr>`;
    return;
  }

  tbody.innerHTML = orders.map(o => `
    <tr>
      <td class="fw-bold text-dark">${o.order_number}</td>
      <td class="fw-semibold text-dark">${o.course_title}</td>
      <td class="fw-bold text-primary">${formatINR(o.final_amount)}</td>
      <td><span class="status-pill ${o.payment_status === 'Paid' ? 'status-paid' : 'status-pending'}">${o.payment_status}</span></td>
      <td class="small text-muted">${formatDate(o.created_at)}</td>
      <td>
        <a href="payment-success.html?order_number=${o.order_number}" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-receipt"></i> Receipt
        </a>
      </td>
    </tr>
  `).join('');
}

function renderMyRequests(requests = []) {
  const tbody = document.getElementById('dash-requests-table-body');
  if (!tbody) return;

  if (requests.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No tax or service requests submitted.</td></tr>`;
    return;
  }

  tbody.innerHTML = requests.map(r => {
    const pillClass = r.status === 'Completed' ? 'status-completed' : r.status === 'Processing' ? 'status-processing' : 'status-pending';
    return `
      <tr>
        <td class="fw-bold text-dark">${r.request_number}</td>
        <td class="fw-semibold text-dark">${r.service_type}</td>
        <td>FY ${r.financial_year} / AY ${r.assessment_year}</td>
        <td><span class="status-pill ${pillClass}">${r.status}</span></td>
        <td class="small text-muted">${formatDate(r.created_at)}</td>
      </tr>
    `;
  }).join('');
}

function renderMyDocuments(documents = []) {
  const tbody = document.getElementById('dash-docs-table-body');
  if (!tbody) return;

  if (documents.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded.</td></tr>`;
    return;
  }

  tbody.innerHTML = documents.map(d => `
    <tr>
      <td class="fw-semibold text-dark">
        <i class="bi bi-file-earmark-lock2 text-primary me-1"></i> ${d.original_name}
      </td>
      <td><span class="badge bg-subtle text-dark border">${d.file_type}</span></td>
      <td class="small text-muted">${d.file_size_formatted}</td>
      <td class="small text-muted">${formatDate(d.created_at)}</td>
      <td>
        <div class="d-flex gap-2">
          <a href="api/documents/view.php?id=${d.id}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
          <a href="api/documents/download.php?id=${d.id}" class="btn btn-sm btn-primary">Download</a>
        </div>
      </td>
    </tr>
  `).join('');
}

async function handleProfileUpdate(e) {
  e.preventDefault();
  const name = document.getElementById('prof-name').value.trim();
  const mobile = document.getElementById('prof-mobile').value.trim();
  const address = document.getElementById('prof-address').value.trim();
  const currentPassword = document.getElementById('prof-curr-pass').value;
  const newPassword = document.getElementById('prof-new-pass').value;

  if (!name || !mobile) {
    showToast('Name and mobile number are required.', 'error');
    return;
  }

  const btn = document.getElementById('btn-save-profile');
  btn.disabled = true;

  try {
    const res = await API.post('api/user/update-profile.php', {
      name, mobile, address,
      current_password: currentPassword || undefined,
      new_password: newPassword || undefined
    });

    if (res.success) {
      showToast('Profile updated successfully!', 'success');
      await Auth.syncSession();
      renderNavbarAuth();
    } else {
      showToast(res.message || 'Failed to update profile.', 'error');
    }
  } catch (err) {
    showToast('Network error updating profile.', 'error');
  } finally {
    btn.disabled = false;
  }
}
