/**
 * MB Internet And Digital Studio | Admin Console Controller
 * Strict Server Authentication Guard, Real Database Statistics, Chart.js, and CRUD
 */

let revenueChartInstance = null;
let courseSalesChartInstance = null;

function toggleSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  if (sidebar && backdrop) {
    sidebar.classList.toggle('show');
    backdrop.classList.toggle('show');
  }
}

// 1. Guard & Auth Init
document.addEventListener('DOMContentLoaded', async () => {
  ThemeManager.init();
  const user = await Auth.syncSession();

  // Strict Server Role Verification for client-side HTML files
  if (!user || (user.role !== 'admin' && user.role !== 'superadmin')) {
    if (window.location.pathname.endsWith('.html')) {
      if (typeof showToast === 'function') showToast('Unauthorized access. Redirecting to login...', 'error');
      setTimeout(() => {
        window.location.href = '../login.php?redirect=' + encodeURIComponent(window.location.href);
      }, 600);
      return;
    }
  }

  const nameEl = document.getElementById('admin-header-name');
  if (nameEl && user && user.name) nameEl.textContent = user.name;

  // If on Dashboard page, load KPIs and charts
  if (document.getElementById('kpi-total-revenue')) {
    loadDashboardData();
  }
});

// 2. Dashboard KPIs & Charts
async function loadDashboardData() {
  try {
    const res = await API.get('api/admin/dashboard.php');
    if (res.success && res.data) {
      const { stats, charts, recent_orders, recent_requests } = res.data;

      // Update KPI Numbers
      if (document.getElementById('kpi-total-revenue')) {
        document.getElementById('kpi-total-revenue').textContent = formatINR(stats.total_revenue || 0);
      }
      if (document.getElementById('kpi-total-orders')) {
        document.getElementById('kpi-total-orders').textContent = stats.total_orders || 0;
      }
      if (document.getElementById('kpi-tax-requests')) {
        document.getElementById('kpi-tax-requests').textContent = stats.pending_requests + stats.processing_requests + stats.completed_requests;
      }
      if (document.getElementById('kpi-total-users')) {
        document.getElementById('kpi-total-users').textContent = stats.total_users || 0;
      }

      // Render Charts
      renderRevenueTrendChart(charts.revenue_trend);
      renderCourseSalesChart(charts.course_sales);

      // Render Recent Tables
      renderRecentOrdersTable(recent_orders);
      renderRecentTaxRequestsTable(recent_requests);
    }
  } catch (err) {
    console.error('Failed to load admin dashboard data:', err);
    showToast('Failed to load live metrics from database.', 'error');
  }
}

function renderRevenueTrendChart(chartData) {
  const ctx = document.getElementById('revenueTrendChart');
  if (!ctx) return;

  if (revenueChartInstance) {
    revenueChartInstance.destroy();
  }

  const isDark = ThemeManager.getTheme() === 'dark';
  const gridColor = isDark ? '#2B3340' : '#e2e8f0';
  const textColor = isDark ? '#B8C0CC' : '#64748b';

  revenueChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels || ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
      datasets: [{
        label: 'Revenue (₹)',
        data: chartData.data || [0, 0, 0, 0, 0, 0],
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.12)',
        borderWidth: 2.5,
        tension: 0.35,
        fill: true,
        pointBackgroundColor: '#2563eb',
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          grid: { color: gridColor },
          ticks: { color: textColor }
        },
        y: {
          grid: { color: gridColor },
          ticks: {
            color: textColor,
            callback: (val) => '₹' + Number(val).toLocaleString('en-IN')
          }
        }
      }
    }
  });
}

function renderCourseSalesChart(chartData) {
  const ctx = document.getElementById('courseSalesChart');
  if (!ctx) return;

  if (courseSalesChartInstance) {
    courseSalesChartInstance.destroy();
  }

  const isDark = ThemeManager.getTheme() === 'dark';
  const textColor = isDark ? '#B8C0CC' : '#64748b';

  courseSalesChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: chartData.labels || ['Tally Prime + GST', 'Advanced Excel', 'Basic Computer'],
      datasets: [{
        data: chartData.data || [1, 1, 1],
        backgroundColor: ['#10b981', '#2563eb', '#8b5cf6', '#f59e0b', '#06b6d4'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: textColor, boxWidth: 12, font: { size: 11 } }
        }
      }
    }
  });
}

function renderRecentOrdersTable(orders = []) {
  const tbody = document.getElementById('table-recent-orders');
  if (!tbody) return;

  if (orders.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">No recent course orders recorded.</td></tr>`;
    return;
  }

  tbody.innerHTML = orders.map(o => `
    <tr>
      <td class="fw-bold text-dark">${o.order_number}</td>
      <td>
        <div class="fw-semibold text-dark">${o.customer_name}</div>
        <div class="small text-muted">${o.course_title}</div>
      </td>
      <td class="fw-bold text-primary">${formatINR(o.final_amount)}</td>
      <td><span class="status-pill status-paid">${o.payment_status}</span></td>
    </tr>
  `).join('');
}

function renderRecentTaxRequestsTable(requests = []) {
  const tbody = document.getElementById('table-recent-requests');
  if (!tbody) return;

  if (requests.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">No tax requests submitted yet.</td></tr>`;
    return;
  }

  tbody.innerHTML = requests.map(r => {
    const pillClass = r.status === 'Completed' ? 'status-completed' : r.status === 'Processing' ? 'status-processing' : 'status-pending';
    return `
      <tr>
        <td class="fw-bold text-dark">${r.request_number}</td>
        <td>${r.customer_name}</td>
        <td><span class="small text-muted">${r.service_type}</span></td>
        <td><span class="status-pill ${pillClass}">${r.status}</span></td>
      </tr>
    `;
  }).join('');
}

// 3. Courses CRUD
async function loadAdminCourses() {
  const tbody = document.getElementById('table-admin-courses');
  const countEl = document.getElementById('admin-courses-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/courses.php');
    const courses = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = courses.length;

    if (courses.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No courses registered.</td></tr>`;
      return;
    }

    tbody.innerHTML = courses.map(c => `
      <tr>
        <td>
          <div class="fw-bold text-dark">${c.title}</div>
          <div class="small text-muted">${c.course_code}</div>
        </td>
        <td><span class="badge bg-subtle text-dark border">${c.category}</span></td>
        <td>${c.duration} / ${c.hours || 'N/A'}</td>
        <td class="fw-bold text-primary">${formatINR(c.price)}</td>
        <td>
          <button type="button" class="btn btn-sm ${c.status === 'active' ? 'btn-success' : 'btn-secondary'}" onclick="toggleCourseStatus(${c.id})">
            ${c.status === 'active' ? 'Active' : 'Inactive'}
          </button>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCourse(${c.id})">
              <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${c.id})">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load courses.</td></tr>`;
  }
}

function openAddCourseModal() {
  document.getElementById('form-course-editor').reset();
  document.getElementById('course-id').value = '';
  document.getElementById('courseModalLabel').textContent = 'Add New Computer Course';
  const modal = new bootstrap.Modal(document.getElementById('courseModal'));
  modal.show();
}

async function editCourse(id) {
  try {
    const res = await API.get(`api/courses/detail.php?id=${id}`);
    if (res.success && res.data) {
      const c = res.data;
      document.getElementById('course-id').value = c.id;
      document.getElementById('course-title').value = c.title;
      document.getElementById('course-category').value = c.category;
      document.getElementById('course-duration').value = c.duration;
      document.getElementById('course-hours').value = c.hours || '45 Hours';
      document.getElementById('course-price').value = c.price;
      document.getElementById('course-short-desc').value = c.short_desc;
      document.getElementById('course-badge').value = c.badge || '';
      document.getElementById('course-level').value = c.level || 'Beginner to Advanced';

      document.getElementById('courseModalLabel').textContent = 'Edit Course';
      const modal = new bootstrap.Modal(document.getElementById('courseModal'));
      modal.show();
    }
  } catch (e) {
    showToast('Failed to fetch course details.', 'error');
  }
}

async function handleSaveCourse(e) {
  e.preventDefault();
  const id = document.getElementById('course-id').value;
  const data = {
    id: id || undefined,
    title: document.getElementById('course-title').value.trim(),
    category: document.getElementById('course-category').value,
    duration: document.getElementById('course-duration').value.trim(),
    hours: document.getElementById('course-hours').value.trim(),
    price: parseFloat(document.getElementById('course-price').value),
    short_desc: document.getElementById('course-short-desc').value.trim(),
    badge: document.getElementById('course-badge').value.trim(),
    level: document.getElementById('course-level').value.trim()
  };

  const btn = document.getElementById('btn-save-course');
  btn.disabled = true;

  try {
    const res = await API.post('api/admin/courses.php', data);
    if (res.success) {
      showToast(res.message || 'Course saved successfully!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('courseModal'));
      if (modal) modal.hide();
      loadAdminCourses();
    } else {
      showToast(res.message || 'Failed to save course.', 'error');
    }
  } catch (err) {
    showToast('Network error saving course.', 'error');
  } finally {
    btn.disabled = false;
  }
}

async function toggleCourseStatus(id) {
  try {
    const res = await API.post('api/admin/courses.php', { action: 'toggle', id });
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminCourses();
    }
  } catch (e) {
    showToast('Error toggling course status.', 'error');
  }
}

async function deleteCourse(id) {
  if (!confirm('Are you sure you want to delete this course from the database?')) return;
  try {
    const res = await API.delete(`api/admin/courses.php?id=${id}`);
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminCourses();
    }
  } catch (e) {
    showToast('Error deleting course.', 'error');
  }
}

// 4. Orders List
async function loadAdminOrders() {
  const tbody = document.getElementById('table-admin-orders');
  const countEl = document.getElementById('admin-orders-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/orders.php');
    const orders = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = orders.length;

    if (orders.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No course orders recorded.</td></tr>`;
      return;
    }

    tbody.innerHTML = orders.map(o => `
      <tr>
        <td class="fw-bold text-dark">${o.order_number}</td>
        <td><span class="badge bg-primary-subtle text-primary border">${o.enrollment_number || 'Pending'}</span></td>
        <td>
          <div class="fw-semibold text-dark">${o.customer_name}</div>
          <div class="small text-muted">${o.mobile} | ${o.email}</div>
        </td>
        <td>${o.course_title}</td>
        <td class="fw-bold text-primary">${formatINR(o.final_amount)}</td>
        <td><span class="status-pill ${o.payment_status === 'Paid' ? 'status-paid' : 'status-pending'}">${o.payment_status}</span></td>
        <td class="small text-muted">${formatDate(o.created_at)}</td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load orders.</td></tr>`;
  }
}

// 5. Payments List
async function loadAdminPayments() {
  const tbody = document.getElementById('table-admin-payments');
  const countEl = document.getElementById('admin-payments-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/payments.php');
    const payments = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = payments.length;

    if (payments.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No payment logs recorded.</td></tr>`;
      return;
    }

    tbody.innerHTML = payments.map(p => `
      <tr>
        <td class="fw-bold text-dark">${p.payment_number}</td>
        <td>${p.order_number || ('#' + p.order_id)}</td>
        <td>
          <div class="fw-semibold text-dark">${p.student_name || 'Student'}</div>
          <div class="small text-muted">${p.student_email || ''}</div>
        </td>
        <td>
          <div class="small fw-semibold text-dark">${p.gateway}</div>
          <div class="small text-muted" style="font-size: 0.72rem;">${p.gateway_payment_id || 'N/A'}</div>
        </td>
        <td class="fw-bold text-success">${formatINR(p.amount)}</td>
        <td><span class="status-pill status-paid">${p.status}</span></td>
        <td class="small text-muted">${formatDate(p.created_at)}</td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load payments.</td></tr>`;
  }
}

// 6. Users List
async function loadAdminUsers() {
  const tbody = document.getElementById('table-admin-users');
  const countEl = document.getElementById('admin-users-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/users.php');
    const users = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = users.length;

    if (users.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No registered student records.</td></tr>`;
      return;
    }

    tbody.innerHTML = users.map(u => `
      <tr>
        <td class="fw-bold text-dark">${u.user_code}</td>
        <td class="fw-semibold text-dark">${u.name}</td>
        <td>${u.mobile}</td>
        <td>${u.email}</td>
        <td>
          <button type="button" class="btn btn-sm ${u.status === 'active' ? 'btn-success' : 'btn-secondary'}" onclick="toggleUserStatus(${u.id})">
            ${u.status}
          </button>
        </td>
        <td class="small text-muted">${formatDate(u.created_at)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="showToast('Student: ' + '${u.name}' + ' (${u.email})', 'info')">
            <i class="bi bi-eye"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load users.</td></tr>`;
  }
}

async function toggleUserStatus(id) {
  try {
    const res = await API.post('api/admin/users.php', { action: 'toggle', id });
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminUsers();
    }
  } catch (e) {
    showToast('Error updating user status.', 'error');
  }
}

// 7. Tax Requests Manager & Internal Notes
async function loadAdminTaxRequests() {
  const tbody = document.getElementById('table-admin-tax-requests');
  const countEl = document.getElementById('admin-tax-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/service-requests.php');
    const requests = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = requests.length;

    if (requests.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No tax service requests found.</td></tr>`;
      return;
    }

    tbody.innerHTML = requests.map(r => {
      const pillClass = r.status === 'Completed' ? 'status-completed' : r.status === 'Processing' ? 'status-processing' : 'status-pending';
      return `
        <tr>
          <td class="fw-bold text-dark">${r.request_number}</td>
          <td>
            <div class="fw-semibold text-dark">${r.customer_name}</div>
            <div class="small text-muted">${r.mobile}</div>
          </td>
          <td>${r.service_type}</td>
          <td>
            <span class="badge bg-subtle text-dark border">${r.pan_number || 'No PAN'}</span>
            <div class="small text-muted">AY ${r.assessment_year}</div>
          </td>
          <td><span class="status-pill ${pillClass}">${r.status}</span></td>
          <td class="small text-muted">${formatDate(r.created_at)}</td>
          <td>
            <button type="button" class="btn btn-sm btn-primary" onclick="openTaxRequestDetails(${r.id})">
              <i class="bi bi-pencil-square me-1"></i> Review
            </button>
          </td>
        </tr>
      `;
    }).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load tax requests.</td></tr>`;
  }
}

async function openTaxRequestDetails(id) {
  try {
    const res = await API.get(`api/service-requests/detail.php?id=${id}`);
    if (res.success && res.data) {
      const r = res.data;
      document.getElementById('modal-tax-id').value = r.id;
      document.getElementById('modal-tax-req-no').textContent = r.request_number;
      document.getElementById('modal-tax-name').textContent = r.customer_name;
      document.getElementById('modal-tax-contact').textContent = `${r.mobile} | ${r.email}`;
      document.getElementById('modal-tax-pan').textContent = `${r.pan_number || 'N/A'} (FY: ${r.financial_year} / AY: ${r.assessment_year})`;
      document.getElementById('modal-tax-status').value = r.status;
      document.getElementById('modal-tax-notes').value = r.internal_notes || '';

      const docsWrap = document.getElementById('modal-tax-attached-docs');
      if (docsWrap) {
        if (!r.documents || r.documents.length === 0) {
          docsWrap.innerHTML = `<span class="text-muted small">No attached documents.</span>`;
        } else {
          docsWrap.innerHTML = r.documents.map(d => `
            <div class="d-flex align-items-center justify-content-between p-2 bg-subtle rounded border small">
              <div class="d-flex align-items-center gap-2 text-truncate">
                <i class="bi bi-file-earmark-lock2-fill text-primary fs-5"></i>
                <span class="fw-semibold text-dark text-truncate">${d.original_name}</span>
                <span class="text-muted">(${d.file_size_formatted})</span>
              </div>
              <div class="d-flex gap-2">
                <a href="../api/documents/view.php?id=${d.id}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                <a href="../api/documents/download.php?id=${d.id}" class="btn btn-sm btn-primary">Download</a>
              </div>
            </div>
          `).join('');
        }
      }

      const modal = new bootstrap.Modal(document.getElementById('taxRequestModal'));
      modal.show();
    }
  } catch (e) {
    showToast('Failed to fetch request details.', 'error');
  }
}

async function saveTaxRequestStatus() {
  const id = document.getElementById('modal-tax-id').value;
  const status = document.getElementById('modal-tax-status').value;
  const notes = document.getElementById('modal-tax-notes').value.trim();

  try {
    const res = await API.post('api/admin/service-requests.php', {
      id: id,
      status: status,
      internal_notes: notes
    });

    if (res.success) {
      showToast('Service request status & notes updated successfully!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('taxRequestModal'));
      if (modal) modal.hide();
      loadAdminTaxRequests();
    } else {
      showToast(res.message || 'Failed to update request.', 'error');
    }
  } catch (e) {
    showToast('Network error saving request status.', 'error');
  }
}

// 8. Documents Vault
async function loadAdminDocuments() {
  const tbody = document.getElementById('table-admin-documents');
  const countEl = document.getElementById('admin-docs-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/documents.php');
    const docs = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = docs.length;

    if (docs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No documents stored in the vault.</td></tr>`;
      return;
    }

    tbody.innerHTML = docs.map(d => `
      <tr>
        <td>
          <div class="fw-semibold text-dark text-truncate" style="max-width: 240px;">${d.original_name}</div>
          <div class="small text-muted">${d.file_type}</div>
        </td>
        <td>
          <div class="fw-semibold text-dark">${d.user_name || 'Client'}</div>
          <div class="small text-muted">${d.user_email || ''}</div>
        </td>
        <td><span class="badge bg-subtle text-dark border">${d.request_number || 'Direct Upload'}</span></td>
        <td>${d.file_size_formatted}</td>
        <td class="small text-muted">${formatDate(d.created_at)}</td>
        <td>
          <div class="d-flex gap-1">
            <a href="../api/documents/view.php?id=${d.id}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
            <a href="../api/documents/download.php?id=${d.id}" class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Download</a>
          </div>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load documents.</td></tr>`;
  }
}

// 9. Important Links CRUD
async function loadAdminLinks() {
  const tbody = document.getElementById('table-admin-links');
  const countEl = document.getElementById('admin-links-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/links.php');
    const links = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = links.length;

    if (links.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No portal links configured.</td></tr>`;
      return;
    }

    tbody.innerHTML = links.map(l => `
      <tr>
        <td>
          <div class="fw-bold text-dark">${l.title}</div>
          <div class="small text-muted">${l.description.substring(0, 50)}...</div>
        </td>
        <td><span class="badge bg-subtle text-dark border">${l.category}</span></td>
        <td><a href="${l.url}" target="_blank" class="small text-truncate d-inline-block" style="max-width: 200px;">${l.url}</a></td>
        <td>${l.display_order}</td>
        <td>
          <button type="button" class="btn btn-sm ${l.status === 'active' ? 'btn-success' : 'btn-secondary'}" onclick="toggleLinkStatus(${l.id})">
            ${l.status}
          </button>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLink(${l.id})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load links.</td></tr>`;
  }
}

function openAddLinkModal() {
  document.getElementById('form-link-editor').reset();
  document.getElementById('link-id').value = '';
  const modal = new bootstrap.Modal(document.getElementById('linkModal'));
  modal.show();
}

async function handleSaveLink(e) {
  e.preventDefault();
  const data = {
    title: document.getElementById('link-title').value.trim(),
    category: document.getElementById('link-category').value.trim() || 'Taxation',
    display_order: parseInt(document.getElementById('link-order').value) || 1,
    url: document.getElementById('link-url').value.trim(),
    description: document.getElementById('link-desc').value.trim(),
    button_text: 'Visit Portal',
    icon: 'bi-link-45deg',
    status: 'active'
  };

  try {
    const res = await API.post('api/admin/links.php', data);
    if (res.success) {
      showToast('Portal link saved successfully!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('linkModal'));
      if (modal) modal.hide();
      loadAdminLinks();
    }
  } catch (e) {
    showToast('Failed to save portal link.', 'error');
  }
}

async function toggleLinkStatus(id) {
  try {
    const res = await API.post('api/admin/links.php', { action: 'toggle', id });
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminLinks();
    }
  } catch (e) {
    showToast('Error updating link status.', 'error');
  }
}

async function deleteLink(id) {
  if (!confirm('Delete this portal link?')) return;
  try {
    const res = await API.delete(`api/admin/links.php?id=${id}`);
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminLinks();
    }
  } catch (e) {
    showToast('Error deleting link.', 'error');
  }
}

// 10. Digital Services & Mobile/Gift Products Admin
async function loadAdminDigitalServices() {
  const tbody = document.getElementById('table-admin-digital-services');
  const countEl = document.getElementById('admin-digital-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/digital-services.php');
    const services = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = services.length;

    tbody.innerHTML = services.map(s => `
      <tr>
        <td class="fw-semibold text-dark">${s.title}</td>
        <td><span class="badge bg-subtle text-dark border">${s.category}</span></td>
        <td>${s.price_text}</td>
        <td>
          <button type="button" class="btn btn-sm ${s.status === 'active' ? 'btn-success' : 'btn-secondary'}" onclick="toggleDigitalStatus(${s.id})">
            ${s.status}
          </button>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDigitalService(${s.id})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Failed to load digital services.</td></tr>`;
  }
}

function openAddDigitalServiceModal() {
  document.getElementById('form-digital-service').reset();
  document.getElementById('dig-id').value = '';
  const modal = new bootstrap.Modal(document.getElementById('digitalModal'));
  modal.show();
}

async function handleSaveDigitalService(e) {
  e.preventDefault();
  const data = {
    title: document.getElementById('dig-title').value.trim(),
    category: document.getElementById('dig-category').value.trim() || 'Application Services',
    price_text: document.getElementById('dig-price-text').value.trim() || 'Nominal Fee',
    description: document.getElementById('dig-desc').value.trim()
  };

  try {
    const res = await API.post('api/admin/digital-services.php', data);
    if (res.success) {
      showToast('Digital service saved!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('digitalModal'));
      if (modal) modal.hide();
      loadAdminDigitalServices();
    }
  } catch (e) {
    showToast('Failed to save service.', 'error');
  }
}

async function toggleDigitalStatus(id) {
  try {
    const res = await API.post('api/admin/digital-services.php', { action: 'toggle', id });
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminDigitalServices();
    }
  } catch (e) {
    showToast('Error updating status.', 'error');
  }
}

async function deleteDigitalService(id) {
  if (!confirm('Delete this digital service?')) return;
  try {
    const res = await API.delete(`api/admin/digital-services.php?id=${id}`);
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminDigitalServices();
    }
  } catch (e) {
    showToast('Error deleting service.', 'error');
  }
}

// 11. Mobile & Gift Products Admin
async function loadAdminProducts() {
  const tbody = document.getElementById('table-admin-products');
  const countEl = document.getElementById('admin-products-count');
  if (!tbody) return;

  try {
    const res = await API.get('api/admin/products.php');
    const prods = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countEl) countEl.textContent = prods.length;

    tbody.innerHTML = prods.map(p => `
      <tr>
        <td>
          <div class="fw-semibold text-dark">${p.name}</div>
          <div class="small text-muted">${p.code}</div>
        </td>
        <td><span class="badge bg-subtle text-dark border">${p.category}</span></td>
        <td class="fw-bold text-primary">${p.price ? formatINR(p.price) : 'In-Store'}</td>
        <td><span class="badge bg-warning-subtle text-warning border small">${p.badge || 'Standard'}</span></td>
        <td>
          <button type="button" class="btn btn-sm ${p.status === 'active' ? 'btn-success' : 'btn-secondary'}" onclick="toggleProductStatus(${p.id})">
            ${p.status}
          </button>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load catalog.</td></tr>`;
  }
}

function openAddProductModal() {
  document.getElementById('form-product-editor').reset();
  document.getElementById('prod-id').value = '';
  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();
}

async function handleSaveProduct(e) {
  e.preventDefault();
  const data = {
    name: document.getElementById('prod-name').value.trim(),
    category: document.getElementById('prod-category').value,
    price: parseFloat(document.getElementById('prod-price').value) || null,
    badge: document.getElementById('prod-badge').value.trim() || null,
    description: document.getElementById('prod-desc').value.trim()
  };

  try {
    const res = await API.post('api/admin/products.php', data);
    if (res.success) {
      showToast('Product saved to catalog!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
      if (modal) modal.hide();
      loadAdminProducts();
    }
  } catch (e) {
    showToast('Failed to save product.', 'error');
  }
}

async function toggleProductStatus(id) {
  try {
    const res = await API.post('api/admin/products.php', { action: 'toggle', id });
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminProducts();
    }
  } catch (e) {
    showToast('Error updating product status.', 'error');
  }
}

async function deleteProduct(id) {
  if (!confirm('Delete this product from catalog?')) return;
  try {
    const res = await API.delete(`api/admin/products.php?id=${id}`);
    if (res.success) {
      showToast(res.message, 'success');
      loadAdminProducts();
    }
  } catch (e) {
    showToast('Error deleting product.', 'error');
  }
}

// 12. Settings Admin
async function loadAdminSettings() {
  try {
    const res = await API.get('api/admin/settings.php');
    if (res.success && res.data) {
      const s = res.data;
      if (document.getElementById('set-business-name')) document.getElementById('set-business-name').value = s.business_name || 'MB Internet And Digital Studio';
      if (document.getElementById('set-sub-brand')) document.getElementById('set-sub-brand').value = s.sub_brand || 'Mobile & Gift House';
      if (document.getElementById('set-owner-name')) document.getElementById('set-owner-name').value = s.owner_name || 'Mukesh Bhattacharya';
      if (document.getElementById('set-gstin')) document.getElementById('set-gstin').value = s.gstin || '';
      if (document.getElementById('set-phone')) document.getElementById('set-phone').value = s.phone || '';
      if (document.getElementById('set-email')) document.getElementById('set-email').value = s.email || '';
      if (document.getElementById('set-facebook')) document.getElementById('set-facebook').value = s.facebook_url || 'https://www.facebook.com/MBIDS/photos_by';
      if (document.getElementById('set-address')) document.getElementById('set-address').value = s.address || '';
      if (document.getElementById('set-rzp-key')) document.getElementById('set-rzp-key').value = s.razorpay_key_id || '';
    }
  } catch (e) {
    console.warn('Failed to load settings:', e);
  }
}

async function handleSaveSettings(e) {
  e.preventDefault();
  const settings = {
    business_name: document.getElementById('set-business-name').value.trim(),
    sub_brand: document.getElementById('set-sub-brand').value.trim(),
    owner_name: document.getElementById('set-owner-name').value.trim(),
    gstin: document.getElementById('set-gstin').value.trim(),
    phone: document.getElementById('set-phone').value.trim(),
    email: document.getElementById('set-email').value.trim(),
    facebook_url: document.getElementById('set-facebook').value.trim(),
    address: document.getElementById('set-address').value.trim(),
    razorpay_key_id: document.getElementById('set-rzp-key').value.trim(),
    razorpay_key_secret: document.getElementById('set-rzp-secret').value.trim()
  };

  const btn = document.getElementById('btn-save-settings');
  btn.disabled = true;

  try {
    const res = await API.post('api/admin/settings.php', settings);
    if (res.success) {
      showToast('Settings saved successfully!', 'success');
    } else {
      showToast(res.message || 'Failed to save settings.', 'error');
    }
  } catch (e) {
    showToast('Error saving settings.', 'error');
  } finally {
    btn.disabled = false;
  }
}
