/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Central Application Layer: Session Auth Sync, Theme Manager, Dynamic Navbar & Helpers
 */

// 1. Theme Manager (Light Mode / Dark Mode)
class ThemeManager {
  static getTheme() {
    return localStorage.getItem('mb_theme') || 'light';
  }

  static setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (document.body) {
      if (theme === 'dark') {
        document.body.classList.add('dark-theme');
      } else {
        document.body.classList.remove('dark-theme');
      }
    }
    localStorage.setItem('mb_theme', theme);
    this.updateToggleIcons(theme);
  }

  static toggleTheme() {
    const current = this.getTheme();
    const next = current === 'dark' ? 'light' : 'dark';
    this.setTheme(next);
  }

  static updateToggleIcons(theme) {
    // Update themeIcon by ID
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
      themeIcon.className = (theme === 'dark') ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill';
    }

    // Update all theme toggle buttons across the DOM
    document.querySelectorAll('.theme-toggle-btn, #themeToggleBtn').forEach(btn => {
      if (theme === 'dark') {
        btn.innerHTML = `<i class="bi bi-sun-fill text-warning"></i>`;
        btn.setAttribute('title', 'Switch to Light Mode');
        btn.setAttribute('aria-label', 'Switch to Light Mode');
      } else {
        btn.innerHTML = `<i class="bi bi-moon-stars-fill text-primary"></i>`;
        btn.setAttribute('title', 'Switch to Dark Mode');
        btn.setAttribute('aria-label', 'Switch to Dark Mode');
      }
    });
  }

  static init() {
    const current = this.getTheme();
    this.setTheme(current);
  }
}

// 2. Authentication Service Helper (Connected to PHP REST API)
class Auth {
  static currentUserCache = null;
  static sessionChecked = false;

  static async syncSession() {
    try {
      const res = await API.get('api/auth/me.php');
      if (res.success && res.data && (res.data.authenticated || res.data.logged_in) && res.data.user) {
        this.currentUserCache = res.data.user;
        this.sessionChecked = true;
        return this.currentUserCache;
      } else {
        this.currentUserCache = null;
        this.sessionChecked = true;
        return null;
      }
    } catch (e) {
      this.currentUserCache = null;
      this.sessionChecked = true;
      return null;
    }
  }

  static getCurrentUser() {
    return this.currentUserCache;
  }

  static isLoggedIn() {
    return this.currentUserCache !== null && !!this.currentUserCache.id;
  }

  static isUser() {
    return this.isLoggedIn() && (this.currentUserCache.role === 'user' || this.currentUserCache.role === 'student');
  }

  static isAdmin() {
    return this.isLoggedIn() && (this.currentUserCache.role === 'admin' || this.currentUserCache.role === 'superadmin');
  }

  static async login(emailOrMobile, password) {
    try {
      const res = await API.post('api/auth/login.php', {
        identifier: emailOrMobile,
        password: password
      });

      if (res.success && res.data && res.data.user) {
        this.currentUserCache = res.data.user;
        this.sessionChecked = true;
        return { success: true, user: res.data.user };
      }

      return { success: false, message: res.message || 'Invalid email/mobile or password.' };
    } catch (e) {
      return { success: false, message: 'Network error connecting to authentication service.' };
    }
  }

  static async register(userData) {
    try {
      const res = await API.post('api/auth/register.php', userData);
      if (res.success && res.data && res.data.user) {
        this.currentUserCache = res.data.user;
        this.sessionChecked = true;
        return { success: true, user: res.data.user };
      }
      return { success: false, message: res.message || 'Registration failed.' };
    } catch (e) {
      return { success: false, message: 'Network error connecting to registration service.' };
    }
  }

  static async logout() {
    try {
      await API.post('api/auth/logout.php');
    } catch (e) {}
    this.currentUserCache = null;
    this.sessionChecked = true;
    
    const isInAdminDir = window.location.pathname.includes('/admin/');
    window.location.href = isInAdminDir ? "../login.html" : "login.html";
  }

  static requireAuth(redirectUrl = window.location.href) {
    if (!this.isLoggedIn()) {
      const target = encodeURIComponent(redirectUrl);
      const isInAdminDir = window.location.pathname.includes('/admin/');
      const loginPath = isInAdminDir ? `../login.html?redirect=${target}` : `login.html?redirect=${target}`;
      window.location.href = loginPath;
      return false;
    }
    return true;
  }
}

// 3. Format Utilities
function formatINR(amount) {
  const num = typeof amount === 'number' ? amount : parseFloat(amount) || 0;
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0
  }).format(num);
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch (e) {
    return dateStr;
  }
}

// 4. Toast Notifications Helper
function showToast(message, type = 'success') {
  let container = document.getElementById('sp-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'sp-toast-container';
    container.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; max-width: 380px;';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  const bgClass = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#2563eb';
  const iconClass = type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill';

  toast.style.cssText = `
    background: var(--bg-card-elevated, #1B202A);
    color: var(--text-primary, #ffffff);
    padding: 12px 16px;
    border-radius: var(--radius-md, 12px);
    box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.25));
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.9rem;
    font-family: 'Inter', sans-serif;
    border-left: 4px solid ${bgClass};
    border-top: 1px solid var(--border, #2B3340);
    border-right: 1px solid var(--border, #2B3340);
    border-bottom: 1px solid var(--border, #2B3340);
  `;

  toast.innerHTML = `
    <i class="bi ${iconClass}" style="color: ${bgClass}; font-size: 1.25rem;"></i>
    <div style="flex: 1; font-weight: 500;">${message}</div>
    <button type="button" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.2rem; line-height: 1;" onclick="this.parentElement.remove()">&times;</button>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// 5. Global Document Initializer & Dynamic Navbar
document.addEventListener('DOMContentLoaded', async () => {
  ThemeManager.init();
  await Auth.syncSession();
  renderNavbarAuth();

  // Highlight Active Nav Link
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  // Sticky Navbar Scroll Elevation
  const navbar = document.querySelector('.site-navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

  // Render Homepage Important Links if present
  renderHomeImportantLinks();
});

// Dynamic Navbar State Renderer
function renderNavbarAuth() {
  const authContainer = document.getElementById('navbar-auth-container');
  if (!authContainer) return;

  const currentUser = Auth.getCurrentUser();
  const theme = ThemeManager.getTheme();
  const themeIcon = theme === 'dark' ? 'bi-sun-fill text-warning' : 'bi-moon-stars-fill text-primary';

  let authContent = '';

  if (currentUser && currentUser.id) {
    if (currentUser.role === 'admin' || currentUser.role === 'superadmin') {
      authContent = `
        <div class="d-flex align-items-center gap-2">
          <a href="admin/index.html" class="btn btn-primary btn-sm">
            <i class="bi bi-speedometer2 me-1"></i> Admin Console
          </a>
          <button type="button" class="btn btn-outline-danger btn-sm" onclick="Auth.logout()" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      `;
    } else {
      const firstName = currentUser.name ? currentUser.name.split(' ')[0] : 'Student';
      authContent = `
        <div class="dropdown">
          <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-6"></i>
            <span class="fw-semibold text-truncate" style="max-width: 120px;">${firstName}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3 mt-2 p-2">
            <li>
              <div class="px-3 py-2 border-bottom mb-2">
                <div class="fw-bold small text-dark">${currentUser.name}</div>
                <div class="text-muted" style="font-size: 0.75rem;">${currentUser.email}</div>
                <span class="badge bg-primary-subtle text-primary border mt-1" style="font-size: 0.68rem;">Student Account</span>
              </div>
            </li>
            <li><a class="dropdown-item rounded-2 py-2 small" href="dashboard.html"><i class="bi bi-grid-1x2-fill text-primary me-2"></i> My Dashboard</a></li>
            <li><a class="dropdown-item rounded-2 py-2 small" href="dashboard.html#my-courses"><i class="bi bi-mortarboard-fill text-success me-2"></i> My Courses</a></li>
            <li><a class="dropdown-item rounded-2 py-2 small" href="dashboard.html#tax-requests"><i class="bi bi-receipt text-warning me-2"></i> My Service Requests</a></li>
            <li><a class="dropdown-item rounded-2 py-2 small" href="dashboard.html#profile"><i class="bi bi-person-gear text-secondary me-2"></i> Profile & Settings</a></li>
            <li><hr class="dropdown-divider my-2"></li>
            <li><button class="dropdown-item rounded-2 py-2 small text-danger" type="button" onclick="Auth.logout()"><i class="bi bi-box-arrow-right me-2"></i> Logout</button></li>
          </ul>
        </div>
      `;
    }
  } else {
    authContent = `
      <div class="d-flex align-items-center gap-2">
        <a href="login.html" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
        <a href="login.html?tab=register" class="btn btn-primary btn-sm">
          <i class="bi bi-person-plus"></i> Register
        </a>
      </div>
    `;
  }

  authContainer.innerHTML = `
    <div class="d-flex align-items-center gap-2">
      ${authContent}
      <button type="button" class="theme-toggle-btn" onclick="ThemeManager.toggleTheme()" title="Toggle Dark/Light Mode" aria-label="Toggle Dark/Light Mode">
        <i class="bi ${themeIcon}"></i>
      </button>
    </div>
  `;
}

// Render Homepage Important Links from Backend API
async function renderHomeImportantLinks() {
  const container = document.getElementById('home-important-links-list');
  if (!container) return;

  try {
    const res = await API.get('api/important-links/list.php');
    const links = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (links.length === 0) {
      container.innerHTML = `<div class="col-12 text-center text-muted small py-3">No active public directory links available.</div>`;
      return;
    }

    container.innerHTML = links.map(link => {
      return `
        <div class="col-md-6 col-lg-3 mb-4">
          <div class="important-link-card">
            <div class="card-icon-wrap accent-links mb-3">
              <i class="bi ${link.icon || 'bi-link-45deg'}"></i>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-1">
              <h5 class="fw-bold fs-6 mb-0 text-dark">${link.title}</h5>
            </div>
            <p class="small text-muted mb-3 flex-grow-1" style="font-size: 0.82rem;">${link.description}</p>
            <div class="pt-2 border-top mt-auto">
              <a href="${link.url}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary w-100">
                ${link.buttonText || 'Visit Portal'} <i class="bi bi-arrow-up-right"></i>
              </a>
            </div>
          </div>
        </div>
      `;
    }).join('');
  } catch (e) {
    console.warn('Error loading important links:', e);
  }
}
