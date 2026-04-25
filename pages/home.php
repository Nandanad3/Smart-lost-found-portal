<?php
$pageTitle = 'Browse Found Items';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

// Filters
$cat    = $_GET['category'] ?? '';
$search = $_GET['search']   ?? '';

$sql  = 'SELECT i.*, u.name as reporter_name, u.roll_number
         FROM items i
         JOIN users u ON i.reported_by = u.id
         WHERE i.status = "lost"';
$params = [];

if ($cat) {
    $sql .= ' AND i.category = ?';
    $params[] = $cat;
}
if ($search) {
    $sql .= ' AND i.category LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY i.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Stats
$total   = $pdo->query('SELECT COUNT(*) FROM items WHERE status="lost"')->fetchColumn();
$claimed = $pdo->query('SELECT COUNT(*) FROM items WHERE status="claimed"')->fetchColumn();
$found   = $pdo->query('SELECT COUNT(*) FROM items WHERE status="found"')->fetchColumn();
$users   = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>

<?php flash('home'); ?>

<!-- Hero -->
<div class="card" style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF);border-color:#BFDBFE;margin-bottom:2rem;padding:2.5rem 2rem">
  <div class="flex-between flex-wrap gap-1">
    <div>
      <div class="page-tag">🎓 Campus Portal</div>
      <h1 style="font-size:2rem;font-weight:800;color:var(--text);margin-bottom:.4rem">Found Something on Campus?</h1>
      <p style="color:var(--muted);max-width:480px">Browse all registered found items. If an item belongs to you, submit a claim by answering the secret questions.</p>
      <div style="margin-top:1.2rem;display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="/campusfind/items/report.php" class="btn btn-primary">➕ Register Found Item</a>
        <a href="/campusfind/pages/dashboard.php" class="btn btn-ghost">📋 My Items</a>
      </div>
    </div>
    <div style="font-size:5rem">🔍</div>
  </div>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card"><div class="stat-num c-red"><?= $total ?></div><div class="stat-lbl">Active Found Registrations</div></div>
  <div class="stat-card"><div class="stat-num c-blue"><?= $claimed ?></div><div class="stat-lbl">Successfully Claimed</div></div>
  <div class="stat-card"><div class="stat-num c-green"><?= $found ?></div><div class="stat-lbl">Found by Owner</div></div>
  <div class="stat-card"><div class="stat-num c-yellow"><?= $users ?></div><div class="stat-lbl">Registered Students</div></div>
</div>

<!-- Filters -->
<div class="card card-sm" style="margin-bottom:1.5rem">
  <form method="GET" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
    <select name="category" class="form-select" style="width:auto;flex:1;min-width:160px" onchange="this.form.submit()">
      <option value="">📦 All Categories</option>
      <?php foreach (getCategories() as $c): ?>
        <option value="<?= $c ?>" <?= $cat===$c ? 'selected' : '' ?>><?= getCategoryEmoji($c) ?> <?= $c ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    <?php if ($cat): ?>
      <a href="/campusfind/pages/home.php" class="btn btn-ghost btn-sm">✕ Clear</a>
    <?php endif; ?>
    <span class="text-muted text-sm" style="margin-left:auto"><?= count($items) ?> item<?= count($items)!=1?'s':'' ?> found</span>
  </form>
</div>

<!-- Items Grid -->
<?php if (empty($items)): ?>
  <div class="empty">
    <div class="empty-icon">🔎</div>
    <h3>No found items registered yet</h3>
    <p>Be the first to register a found item on campus.</p>
    <a href="/campusfind/items/report.php" class="btn btn-primary" style="margin-top:1.2rem">➕ Report an Item</a>
  </div>
<?php else: ?>
  <div class="items-grid">
    <?php foreach ($items as $item): ?>
    <div class="item-card">
      <div class="item-card-top lost"></div>
      <div class="item-card-body">
        <div class="item-card-emoji"><?= getCategoryEmoji($item['category']) ?></div>
        <div class="item-card-cat"><?= htmlspecialchars($item['category']) ?></div>
        <div class="item-card-name">Found <?= htmlspecialchars($item['category']) ?></div>
        <div class="item-card-meta">
          👤 Reported by <?= htmlspecialchars($item['reporter_name']) ?><br/>
          📅 <?= date('M j, Y', strtotime($item['created_at'])) ?>
        </div>
        <div class="item-card-footer">
          <span class="badge badge-lost">🔴 Lost</span>
          <?php if ($item['reported_by'] == $_SESSION['user_id']): ?>
            <a href="/campusfind/pages/dashboard.php" class="btn btn-ghost btn-sm">My Item</a>
          <?php elseif ($item['claim_pending'] == $_SESSION['user_id']): ?>
            <span class="badge badge-pending">⏳ Answer Sent</span>
          <?php else: ?>
            <a href="/campusfind/items/claim.php?id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">🤝 Claim This</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>