<?php
$pageTitle = 'Lost Notice Board';
require_once __DIR__ . '/../includes/header.php';

$category = trim($_GET['category'] ?? '');
$q        = trim($_GET['q'] ?? '');
$status   = trim($_GET['status'] ?? 'active');
if (!in_array($status, ['active', 'resolved', 'all'], true)) {
    $status = 'active';
}

$sql = '
    SELECT ln.*, u.name AS reporter_name, u.roll_number AS reporter_roll, u.department AS reporter_department
    FROM lost_notices ln
    JOIN users u ON ln.user_id = u.id
    WHERE 1=1
';
$params = [];

if ($status !== 'all') {
    $sql .= ' AND ln.status = ?';
    $params[] = $status;
}
if ($category !== '') {
    $sql .= ' AND ln.category = ?';
    $params[] = $category;
}
if ($q !== '') {
    $sql .= ' AND (ln.title LIKE ? OR ln.lost_location LIKE ? OR ln.description LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY ln.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notices = $stmt->fetchAll();

$activeCount = (int)$pdo->query('SELECT COUNT(*) FROM lost_notices WHERE status = "active"')->fetchColumn();
$resolvedCount = (int)$pdo->query('SELECT COUNT(*) FROM lost_notices WHERE status = "resolved"')->fetchColumn();
$totalCount = (int)$pdo->query('SELECT COUNT(*) FROM lost_notices')->fetchColumn();
?>

<?php flash('notice'); ?>

<div class="card" style="background:linear-gradient(135deg,#FDF2F8,#EEF2FF);border-color:#C7D2FE;margin-bottom:2rem;padding:2rem 1.8rem">
  <div class="flex-between flex-wrap gap-1">
    <div>
      <div class="page-tag">Public Notice Board</div>
      <h1 style="font-size:2rem;font-weight:800;margin-bottom:.35rem">Lost Items Around Campus</h1>
      <p style="color:var(--muted);max-width:560px">Anyone can view this board. If you found an item listed below, contact the owner directly and hand it over without registration.</p>
      <div style="margin-top:1rem;display:flex;gap:.7rem;flex-wrap:wrap">
        <?php if (isLoggedIn()): ?>
          <a href="/campusfind/items/report-lost.php" class="btn btn-primary">📝 Report Lost Item</a>
        <?php else: ?>
          <a href="/campusfind/auth/login.php?redirect=/campusfind/items/report-lost.php" class="btn btn-primary">📝 Report Lost Item</a>
        <?php endif; ?>
      </div>
    </div>
    <div style="font-size:4.8rem">📌</div>
  </div>
</div>

<div class="stats-row">
  <div class="stat-card"><div class="stat-num c-red"><?= $activeCount ?></div><div class="stat-lbl">Active Notices</div></div>
  <div class="stat-card"><div class="stat-num c-green"><?= $resolvedCount ?></div><div class="stat-lbl">Resolved</div></div>
  <div class="stat-card"><div class="stat-num c-blue"><?= $totalCount ?></div><div class="stat-lbl">Total Posts</div></div>
</div>

<div class="card card-sm" style="margin-bottom:1.2rem">
  <form method="GET" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.6rem;align-items:center">
    <input type="text" name="q" class="form-input" placeholder="Search title, location, description..."
           value="<?= htmlspecialchars($q) ?>"/>
    <select name="category" class="form-select">
      <option value="">All categories</option>
      <?php foreach (getCategories() as $c): ?>
        <option value="<?= $c ?>" <?= $category === $c ? 'selected' : '' ?>><?= getCategoryEmoji($c) ?> <?= $c ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="form-select">
      <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
      <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
    </select>
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
  </form>
</div>

<?php if (empty($notices)): ?>
  <div class="empty">
    <div class="empty-icon">🗒️</div>
    <h3>No notices found</h3>
    <p>Try changing filters or create the first lost-item notice.</p>
  </div>
<?php else: ?>
  <div class="items-grid">
    <?php foreach ($notices as $n): ?>
      <?php
        $digits = preg_replace('/\D+/', '', (string)$n['contact_phone']);
        $waNumber = '';
        if ($digits !== '') {
            $waNumber = (strlen($digits) === 10 ? '91' . $digits : $digits);
        }
      ?>
      <div class="item-card">
        <div class="item-card-top <?= $n['status'] === 'resolved' ? 'found' : 'lost' ?>"></div>
        <div class="item-card-body">
          <div class="item-card-emoji"><?= getCategoryEmoji($n['category']) ?></div>
          <div class="item-card-cat"><?= htmlspecialchars($n['category']) ?></div>
          <div class="item-card-name"><?= htmlspecialchars($n['title']) ?></div>
          <div class="item-card-meta">
            📍 <?= htmlspecialchars($n['lost_location']) ?><br/>
            📅 <?= $n['lost_date'] ? date('M j, Y', strtotime($n['lost_date'])) : 'Date not specified' ?><br/>
            👤 Posted by <?= htmlspecialchars($n['reporter_name']) ?><br/>
            ⏱️ <?= date('M j, Y', strtotime($n['created_at'])) ?>
          </div>

          <?php if (!empty($n['description'])): ?>
            <div class="alert alert-info" style="margin-bottom:.8rem;padding:.65rem .8rem;font-size:.8rem">
              <?= nl2br(htmlspecialchars($n['description'])) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($n['reward_note'])): ?>
            <div class="badge badge-pending" style="margin-bottom:.7rem">🎁 <?= htmlspecialchars($n['reward_note']) ?></div>
          <?php endif; ?>

          <div class="item-card-footer" style="display:block">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem">
              <?php if ($n['status'] === 'resolved'): ?>
                <span class="badge badge-found">✅ Resolved</span>
              <?php else: ?>
                <span class="badge badge-lost">🔴 Still Missing</span>
              <?php endif; ?>
            </div>
            <div style="display:flex;gap:.45rem;flex-wrap:wrap">
              <button
                type="button"
                class="btn btn-primary btn-sm js-contact-toggle"
                data-target="contact-details-<?= (int)$n['id'] ?>">
                📞 Contact Details
              </button>
              <?php if (isLoggedIn() && intval($n['user_id']) === intval($_SESSION['user_id']) && $n['status'] === 'active'): ?>
                <form method="POST" action="/campusfind/items/update-lost-notice.php" onsubmit="return confirm('Mark this lost notice as found/resolved?');">
                  <input type="hidden" name="notice_id" value="<?= (int)$n['id'] ?>"/>
                  <input type="hidden" name="action" value="mark_resolved"/>
                  <button type="submit" class="btn btn-green btn-sm">✅ Mark Found</button>
                </form>
              <?php elseif (isLoggedIn() && intval($n['user_id']) === intval($_SESSION['user_id']) && $n['status'] === 'resolved'): ?>
                <form method="POST" action="/campusfind/items/update-lost-notice.php">
                  <input type="hidden" name="notice_id" value="<?= (int)$n['id'] ?>"/>
                  <input type="hidden" name="action" value="reopen"/>
                  <button type="submit" class="btn btn-yellow btn-sm">↩ Reopen</button>
                </form>
              <?php endif; ?>
            </div>
            <div id="contact-details-<?= (int)$n['id'] ?>" class="notice-contact-details hidden">
              <div><strong>Phone:</strong> <?= htmlspecialchars($n['contact_phone']) ?></div>
              <div><strong>Email:</strong> <?= !empty($n['contact_email']) ? htmlspecialchars($n['contact_email']) : 'Not provided' ?></div>
              <div><strong>Roll No:</strong> <?= !empty($n['reporter_roll']) ? htmlspecialchars($n['reporter_roll']) : 'Not available' ?></div>
              <div><strong>Department:</strong> <?= !empty($n['reporter_department']) ? htmlspecialchars($n['reporter_department']) : 'Not available' ?></div>
              <div class="notice-contact-actions">
                <a href="tel:<?= htmlspecialchars($n['contact_phone']) ?>" class="btn btn-ghost btn-sm">Call</a>
                <?php if ($waNumber !== ''): ?>
                  <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">WhatsApp</a>
                <?php endif; ?>
                <?php if (!empty($n['contact_email'])): ?>
                  <a href="mailto:<?= htmlspecialchars($n['contact_email']) ?>" class="btn btn-ghost btn-sm">Email</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
  document.querySelectorAll('.js-contact-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetId = btn.getAttribute('data-target');
      var panel = document.getElementById(targetId);
      if (!panel) {
        return;
      }
      panel.classList.toggle('hidden');
      btn.textContent = panel.classList.contains('hidden') ? '📞 Contact Details' : '🙈 Hide Contact Details';
    });
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
