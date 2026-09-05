<?php
/**
 * MB Internet And Digital Studio | Course Management (Core PHP)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

require_admin();

$page_title = "Manage Computer Courses";
$success_msg = "";
$error_msg = "";

// ১. কোর্স ডিলিট হ্যান্ডলিং
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $del_id = (int)$_GET['id'];
        $del_stmt = $conn->prepare("DELETE FROM courses WHERE id = :id");
        $del_stmt->execute([':id' => $del_id]);
        security_log('ADMIN_COURSE_DELETED', ['course_id' => $del_id]);
        $success_msg = "Course deleted successfully!";
    }
}

// ২. স্ট্যাটাস টগল হ্যান্ডলিং (Active / Inactive)
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error_msg = "Security token mismatch. Action aborted.";
    } else {
        $t_id = (int)$_GET['id'];
        $stmt = $conn->prepare("UPDATE courses SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
        $stmt->execute([':id' => $t_id]);
        security_log('ADMIN_COURSE_STATUS_TOGGLED', ['course_id' => $t_id]);
        $success_msg = "Course status updated successfully!";
    }
}

// ৩. নতুন কোর্স যোগ বা এডিট হ্যান্ডলিং (Core PHP POST with Validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    if (!verify_csrf()) {
        $error_msg = "Security validation failed. Please refresh the page and try again.";
    } else {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $price = (float)($_POST['price'] ?? 0);
        $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $duration = trim($_POST['duration'] ?? '');
        $hours = trim($_POST['hours'] ?? '45 Hours');
        $short_desc = trim($_POST['short_desc'] ?? '');
        $badge = trim($_POST['badge'] ?? '');
        $level = trim($_POST['level'] ?? 'Beginner to Advanced');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        // ইনপুট ভ্যালিডেশন
        if (empty($title)) {
            $error_msg = "Course title is required.";
        } elseif ($price <= 0) {
            $error_msg = "Course fee must be greater than zero.";
        } elseif (empty($duration)) {
            $error_msg = "Course duration (e.g. 2 Months) is required.";
        } else {
            if ($course_id > 0) {
                // Update
                $u_stmt = $conn->prepare("
                    UPDATE courses 
                    SET title = :title, slug = :slug, category = :category, price = :price, original_price = :oprice,
                        duration = :duration, hours = :hours, short_desc = :sdesc, badge = :badge, level = :level
                    WHERE id = :id
                ");
                $u_stmt->execute([
                    ':title'    => $title,
                    ':slug'     => $slug,
                    ':category' => $category,
                    ':price'    => $price,
                    ':oprice'   => $original_price,
                    ':duration' => $duration,
                    ':hours'    => $hours,
                    ':sdesc'    => $short_desc,
                    ':badge'    => $badge ?: null,
                    ':level'    => $level,
                    ':id'       => $course_id
                ]);
                security_log('ADMIN_COURSE_UPDATED', ['course_id' => $course_id]);
                $success_msg = "Course updated successfully!";
            } else {
                // Insert
                $course_code = "MB-" . rand(100, 999);
                $i_stmt = $conn->prepare("
                    INSERT INTO courses 
                    (course_code, title, slug, category, price, original_price, duration, hours, short_desc, badge, level, status, created_at)
                    VALUES 
                    (:ccode, :title, :slug, :cat, :price, :oprice, :dur, :hrs, :sdesc, :badge, :level, 'active', NOW())
                ");
                $i_stmt->execute([
                    ':ccode'   => $course_code,
                    ':title'   => $title,
                    ':slug'    => $slug,
                    ':cat'     => $category,
                    ':price'   => $price,
                    ':oprice'  => $original_price,
                    ':dur'     => $duration,
                    ':hrs'     => $hours,
                    ':sdesc'   => $short_desc,
                    ':badge'   => $badge ?: null,
                    ':level'   => $level
                ]);
                $new_cid = (int)$conn->lastInsertId();
                security_log('ADMIN_COURSE_CREATED', ['course_id' => $new_cid]);
                $success_msg = "New course added successfully!";
            }
        }
    }
}

// ৪. এডিট করার জন্য নির্দিষ্ট কোর্স আনা
$edit_course = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    $e_stmt = $conn->prepare("SELECT * FROM courses WHERE id = :id LIMIT 1");
    $e_stmt->execute([':id' => $e_id]);
    $edit_course = $e_stmt->fetch();
}

// ৫. সার্চ ও পেজিনেশন সহ কোর্স লিস্ট
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 8;

$where_clauses = ["1=1"];
$params = [];

if (!empty($search_q)) {
    $where_clauses[] = "(title LIKE :q OR course_code LIKE :q OR short_desc LIKE :q)";
    $params[':q'] = "%$search_q%";
}
if (!empty($filter_cat)) {
    $where_clauses[] = "category = :cat";
    $params[':cat'] = $filter_cat;
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = :status";
    $params[':status'] = $filter_status;
}

$where_sql = implode(" AND ", $where_clauses);

// Count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch Paginated
$list_sql = "SELECT * FROM courses WHERE $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($list_sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Course Form (Add or Edit) -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0">
      <i class="bi <?php echo $edit_course ? 'bi-pencil-square text-warning' : 'bi-plus-circle-fill text-primary'; ?> me-2"></i>
      <?php echo $edit_course ? 'Edit Course: ' . htmlspecialchars($edit_course['title']) : 'Add New Computer Course'; ?>
    </h6>
    <?php if ($edit_course): ?>
      <a href="courses.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i> Close Edit Mode</a>
    <?php endif; ?>
  </div>
  <div class="card-body p-4">
    <form method="POST" action="courses.php">
      <?php echo csrf_field(); ?>
      <?php if ($edit_course): ?>
        <input type="hidden" name="course_id" value="<?php echo $edit_course['id']; ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Course Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. TallyPrime 4.0 with GST & E-Way Bill" value="<?php echo htmlspecialchars($edit_course['title'] ?? ''); ?>" required maxlength="120">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Accounting & Finance" <?php echo (($edit_course['category'] ?? '') === 'Accounting & Finance') ? 'selected' : ''; ?>>Accounting & Finance</option>
            <option value="Office Productivity" <?php echo (($edit_course['category'] ?? '') === 'Office Productivity') ? 'selected' : ''; ?>>Office Productivity</option>
            <option value="Foundation & Literacy" <?php echo (($edit_course['category'] ?? '') === 'Foundation & Literacy') ? 'selected' : ''; ?>>Foundation & Literacy</option>
            <option value="Programming & Web" <?php echo (($edit_course['category'] ?? '') === 'Programming & Web') ? 'selected' : ''; ?>>Programming & Web</option>
            <option value="Design & Multimedia" <?php echo (($edit_course['category'] ?? '') === 'Design & Multimedia') ? 'selected' : ''; ?>>Design & Multimedia</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Course Fee (₹) *</label>
          <input type="number" step="0.01" min="1" name="price" class="form-control" placeholder="2999" value="<?php echo htmlspecialchars($edit_course['price'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Original Price (₹)</label>
          <input type="number" step="0.01" min="0" name="original_price" class="form-control" placeholder="4500" value="<?php echo htmlspecialchars($edit_course['original_price'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Duration *</label>
          <input type="text" name="duration" class="form-control" placeholder="e.g. 2 Months" value="<?php echo htmlspecialchars($edit_course['duration'] ?? '2 Months'); ?>" required maxlength="50">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-dark">Total Hours</label>
          <input type="text" name="hours" class="form-control" placeholder="e.g. 45 Hours" value="<?php echo htmlspecialchars($edit_course['hours'] ?? '45 Hours'); ?>" maxlength="50">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Badge / Tag (Optional)</label>
          <input type="text" name="badge" class="form-control" placeholder="e.g. Most Popular / Best Seller" value="<?php echo htmlspecialchars($edit_course['badge'] ?? ''); ?>" maxlength="50">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Level</label>
          <input type="text" name="level" class="form-control" placeholder="e.g. Beginner to Advanced" value="<?php echo htmlspecialchars($edit_course['level'] ?? 'Beginner to Advanced'); ?>" maxlength="50">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-bold text-dark">Short Description *</label>
        <textarea name="short_desc" class="form-control" rows="2" placeholder="Brief summary of skills learned..." required minlength="10" maxlength="500"><?php echo htmlspecialchars($edit_course['short_desc'] ?? ''); ?></textarea>
      </div>

      <input type="hidden" name="save_course" value="1">
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary fw-bold px-4">
          <i class="bi bi-save me-1"></i> <?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>
        </button>
        <?php if ($edit_course): ?>
          <a href="courses.php" class="btn btn-outline-secondary">Cancel Edit</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Search & Filter Row for Course Directory -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: var(--card-bg, #ffffff);">
  <div class="card-body p-3">
    <form method="GET" action="courses.php" class="row g-2 align-items-center">
      <div class="col-md-5">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-subtle"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by title, code or keywords..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>
      </div>
      <div class="col-md-3">
        <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <option value="Accounting & Finance" <?php echo ($filter_cat === 'Accounting & Finance') ? 'selected' : ''; ?>>Accounting & Finance</option>
          <option value="Office Productivity" <?php echo ($filter_cat === 'Office Productivity') ? 'selected' : ''; ?>>Office Productivity</option>
          <option value="Foundation & Literacy" <?php echo ($filter_cat === 'Foundation & Literacy') ? 'selected' : ''; ?>>Foundation & Literacy</option>
          <option value="Programming & Web" <?php echo ($filter_cat === 'Programming & Web') ? 'selected' : ''; ?>>Programming & Web</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="active" <?php echo ($filter_status === 'active') ? 'selected' : ''; ?>>Active Only</option>
          <option value="inactive" <?php echo ($filter_status === 'inactive') ? 'selected' : ''; ?>>Inactive Only</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
        <?php if (!empty($search_q) || !empty($filter_cat) || !empty($filter_status)): ?>
          <a href="courses.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- All Courses Directory Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
  <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul text-primary me-2"></i> All Registered Courses (<?php echo $total_rows; ?>)</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Course Title</th>
            <th>Category</th>
            <th>Duration</th>
            <th>Fee</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($courses)): ?>
            <?php foreach ($courses as $c): ?>
              <tr>
                <td>
                  <strong class="d-block text-dark"><?php echo htmlspecialchars($c['title']); ?></strong>
                  <span class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($c['course_code']); ?></span>
                </td>
                <td><span class="badge bg-subtle text-dark border"><?php echo htmlspecialchars($c['category']); ?></span></td>
                <td><?php echo htmlspecialchars($c['duration']); ?></td>
                <td class="fw-bold text-primary">₹<?php echo number_format($c['price']); ?></td>
                <td>
                  <a href="courses.php?action=toggle&id=<?php echo $c['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($c['status'] === 'active') ? 'btn-success' : 'btn-secondary'; ?>" style="font-size: 0.75rem;">
                    <?php echo ucfirst($c['status']); ?>
                  </a>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="courses.php?edit_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="courses.php?action=delete&id=<?php echo $c['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this course?');" title="Delete">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No courses found matching criteria.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Showing page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page - 1; ?>">Prev</a>
          </li>
          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?php echo ($page == $p) ? 'active' : ''; ?>">
              <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($search_q); ?>&cat=<?php echo urlencode($filter_cat); ?>&status=<?php echo urlencode($filter_status); ?>&page=<?php echo $page + 1; ?>">Next</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
