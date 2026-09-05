/**
 * MB Internet And Digital Studio | Course Catalog & Details Controller
 * Loads Courses from Backend REST API, Handles Search, Category Tabs, Sorting, and Details
 */

let allCoursesCache = [];

document.addEventListener('DOMContentLoaded', async () => {
  // 1. Homepage Featured Courses
  if (document.getElementById('home-popular-courses')) {
    await loadHomeCourses();
  }

  // 2. Courses Catalog Page
  if (document.getElementById('courses-grid-container')) {
    await loadCoursesCatalog();
    initCoursesFilter();
  }

  // 3. Course Details Page
  if (document.getElementById('course-details-wrapper')) {
    await loadCourseDetails();
  }
});

// Load Homepage Featured Courses
async function loadHomeCourses() {
  const container = document.getElementById('home-popular-courses');
  if (!container) return;

  try {
    const res = await API.get('api/courses/list.php');
    const courses = (res.success && Array.isArray(res.data)) ? res.data.slice(0, 4) : [];

    if (courses.length === 0) {
      container.innerHTML = `<div class="col-12 text-center text-muted py-4">No courses listed at the moment.</div>`;
      return;
    }

    container.innerHTML = courses.map(c => renderCourseCardHTML(c)).join('');
  } catch (e) {
    container.innerHTML = `<div class="col-12 text-center text-danger py-4">Error loading courses.</div>`;
  }
}

// Load Catalog Page Courses
async function loadCoursesCatalog() {
  const container = document.getElementById('courses-grid-container');
  const countBadge = document.getElementById('courses-count-badge');
  if (!container) return;

  try {
    const res = await API.get('api/courses/list.php');
    allCoursesCache = (res.success && Array.isArray(res.data)) ? res.data : [];

    if (countBadge) countBadge.textContent = `Showing all ${allCoursesCache.length} professional courses`;

    if (allCoursesCache.length === 0) {
      container.innerHTML = `<div class="col-12 text-center text-muted py-5">No courses found in database.</div>`;
      return;
    }

    container.innerHTML = allCoursesCache.map(c => renderCourseCardHTML(c)).join('');
  } catch (e) {
    container.innerHTML = `<div class="col-12 text-center text-danger py-5">Failed to fetch courses.</div>`;
  }
}

function renderCourseCardHTML(c) {
  return `
    <div class="col-md-6 col-lg-4 col-xl-3">
      <div class="course-card">
        <div class="course-card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-subtle text-dark border" style="font-size: 0.72rem;">${c.category}</span>
            ${c.badge ? `<span class="badge bg-warning-subtle text-warning border small" style="font-size: 0.68rem;">${c.badge}</span>` : ''}
          </div>

          <div class="card-icon-wrap accent-course mb-2" style="width: 44px; height: 44px; font-size: 1.25rem;">
            <i class="bi ${c.icon || 'bi-laptop'}"></i>
          </div>

          <h3 class="fw-bold fs-6 mb-1 text-dark">${c.title}</h3>
          <p class="small text-muted mb-2 flex-grow-1" style="font-size: 0.82rem;">${c.short_desc}</p>

          <div class="course-meta-row mb-2 pb-2">
            <span><i class="bi bi-clock me-1"></i> ${c.duration}</span>
            <span><i class="bi bi-star-fill text-warning me-1"></i> ${c.rating || '4.9'}</span>
          </div>

          <div class="course-price-row mb-3">
            <span class="price-current">${formatINR(c.price)}</span>
            ${c.original_price ? `<span class="price-original">${formatINR(c.original_price)}</span>` : ''}
          </div>

          <div class="d-flex gap-2 pt-2 border-top">
            <a href="course-details.html?slug=${c.slug}" class="btn btn-sm btn-outline-primary flex-fill">
              Syllabus
            </a>
            <button type="button" class="btn btn-sm btn-primary flex-fill" onclick="handleDirectEnroll('${c.slug}')">
              Enroll <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

function initCoursesFilter() {
  const searchInput = document.getElementById('course-search-input');
  const sortSelect = document.getElementById('course-sort-select');
  const catButtons = document.querySelectorAll('.category-filter-btn');

  let activeCategory = 'all';

  function applyFilters() {
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const sortBy = sortSelect ? sortSelect.value : 'popular';

    let filtered = allCoursesCache.filter(c => {
      const matchCat = activeCategory === 'all' || c.category === activeCategory;
      const matchQuery = !query || c.title.toLowerCase().includes(query) || (c.short_desc && c.short_desc.toLowerCase().includes(query));
      return matchCat && matchQuery;
    });

    if (sortBy === 'price-low') {
      filtered.sort((a, b) => a.price - b.price);
    } else if (sortBy === 'price-high') {
      filtered.sort((a, b) => b.price - a.price);
    }

    const container = document.getElementById('courses-grid-container');
    const countBadge = document.getElementById('courses-count-badge');
    if (countBadge) countBadge.textContent = `Showing ${filtered.length} courses`;

    if (filtered.length === 0) {
      container.innerHTML = `<div class="col-12 text-center text-muted py-5">No courses matching your filter criteria.</div>`;
    } else {
      container.innerHTML = filtered.map(c => renderCourseCardHTML(c)).join('');
    }
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (sortSelect) sortSelect.addEventListener('change', applyFilters);

  catButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      catButtons.forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-outline-primary');
      });
      btn.classList.add('btn-primary', 'active');
      btn.classList.remove('btn-outline-primary');
      activeCategory = btn.getAttribute('data-category');
      applyFilters();
    });
  });
}

// Load Details Page
async function loadCourseDetails() {
  const urlParams = new URLSearchParams(window.location.search);
  const slug = urlParams.get('slug') || urlParams.get('course');
  const id = urlParams.get('id');

  if (!slug && !id) {
    window.location.href = 'courses.html';
    return;
  }

  try {
    const res = await API.get('api/courses/detail.php', { slug, id });
    if (res.success && res.data) {
      const c = res.data;

      document.title = `${c.title} | MB Internet And Digital Studio`;
      document.getElementById('breadcrumb-course-title').textContent = c.title;
      document.getElementById('course-title-main').textContent = c.title;
      document.getElementById('course-desc-main').textContent = c.overview || c.short_desc;
      document.getElementById('course-badge-header').textContent = c.badge || c.category;
      document.getElementById('course-duration-pill').innerHTML = `<i class="bi bi-clock me-1"></i> ${c.duration} (${c.hours || '45 Hours'})`;
      document.getElementById('course-level-pill').innerHTML = `<i class="bi bi-bar-chart me-1"></i> ${c.level || 'All Levels'}`;
      document.getElementById('course-eligibility-pill').innerHTML = `<i class="bi bi-person-check me-1"></i> ${c.eligibility || 'Open to all'}`;

      document.getElementById('sidebar-course-price').textContent = formatINR(c.price);
      if (c.original_price) {
        document.getElementById('sidebar-course-old-price').textContent = formatINR(c.original_price);
        const discountPct = Math.round(((c.original_price - c.price) / c.original_price) * 100);
        document.getElementById('sidebar-discount-tag').textContent = `${discountPct}% OFF`;
      }

      // Learnings List
      const learningsList = document.getElementById('course-learnings-list');
      if (learningsList && Array.isArray(c.learnings)) {
        learningsList.innerHTML = c.learnings.map(item => `
          <li class="col-md-6 mb-2 d-flex align-items-start gap-2">
            <i class="bi bi-check-circle-fill text-success mt-1"></i>
            <span class="small text-secondary">${item}</span>
          </li>
        `).join('');
      }

      // Syllabus Accordion
      const syllabusAcc = document.getElementById('syllabus-accordion');
      if (syllabusAcc && Array.isArray(c.syllabus)) {
        syllabusAcc.innerHTML = c.syllabus.map((mod, idx) => `
          <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
            <h2 class="accordion-header" id="headingMod${idx}">
              <button class="accordion-button ${idx > 0 ? 'collapsed' : ''} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMod${idx}" aria-expanded="${idx === 0}">
                <span class="badge bg-primary me-2">${mod.module || ('Module ' + (idx + 1))}</span>
                ${mod.title}
              </button>
            </h2>
            <div id="collapseMod${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}" data-bs-parent="#syllabus-accordion">
              <div class="accordion-body bg-subtle p-3">
                <ul class="mb-0 small text-muted">
                  ${(mod.topics || []).map(t => `<li class="mb-1">${t}</li>`).join('')}
                </ul>
              </div>
            </div>
          </div>
        `).join('');
      }

      // Benefits
      const benefitsList = document.getElementById('course-benefits-list');
      if (benefitsList && Array.isArray(c.benefits)) {
        benefitsList.innerHTML = c.benefits.map(b => `
          <div class="col-md-6">
            <div class="p-3 bg-subtle rounded-3 border d-flex align-items-center gap-2 small">
              <i class="bi bi-award text-warning fs-5"></i>
              <span class="fw-semibold text-dark">${b}</span>
            </div>
          </div>
        `).join('');
      }

      // Batches
      const batchesWrap = document.getElementById('course-batches-container');
      if (batchesWrap && Array.isArray(c.batches)) {
        batchesWrap.innerHTML = c.batches.map(b => `
          <div class="d-flex align-items-center justify-content-between p-3 bg-subtle rounded-3 border mb-2">
            <div>
              <div class="fw-bold text-dark small">${b.type}</div>
              <div class="small text-muted">${b.time}</div>
            </div>
            <span class="badge bg-success-subtle text-success border small">${b.seats || 'Seats Available'}</span>
          </div>
        `).join('');
      }

      // Buy Now CTA Button
      const buyBtn = document.getElementById('sidebar-buy-now-btn');
      if (buyBtn) {
        buyBtn.onclick = () => handleDirectEnroll(c.slug);
      }
    }
  } catch (e) {
    showToast('Failed to load course curriculum.', 'error');
  }
}

function handleDirectEnroll(slug) {
  const user = Auth.getCurrentUser();
  if (!user || !user.id) {
    showToast('Please login or register to complete course admission.', 'info');
    setTimeout(() => {
      window.location.href = `login.html?redirect=${encodeURIComponent('checkout.html?course=' + slug)}`;
    }, 400);
    return;
  }

  window.location.href = `checkout.html?course=${slug}`;
}

function downloadSyllabus() {
  showToast('Syllabus PDF will open for print/download.', 'info');
  window.print();
}
