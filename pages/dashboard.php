<?php
$pageTitle = 'My Items';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$stmt = $pdo->prepare('
    SELECT i.*, c.name AS claimant_name, c.phone AS claimant_phone, c.roll_number AS claimant_roll
    FROM items i
    LEFT JOIN users c ON i.claimed_by = c.id
    WHERE i.reported_by = ?
    ORDER BY i.created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$totalItems = count($items);
$lostCount = 0;
$claimedCount = 0;
$foundCount = 0;

foreach ($items as $it) {
    if ($it['status'] === 'lost') {
        $lostCount++;
    } elseif ($it['status'] === 'claimed') {
        $claimedCount++;
    } elseif ($it['status'] === 'found') {
        $foundCount++;
    }
}
?>

<?php flash('dashboard'); ?>

<div class="page-hdr">
  <div class="page-tag">Dashboard</div>
  <h1>My Reported Items</h1>
  <p>Track claims, confirm owners, and update the status of your reported items.</p>
</div>

<div class="stats-row">
  <div class="stat-card"><div class="stat-num c-yellow"><?= $totalItems ?></div><div class="stat-lbl">Total Reports</div></div>
  <div class="stat-card"><div class="stat-num c-red"><?= $lostCount ?></div><div class="stat-lbl">Active Lost</div></div>
  <div class="stat-card"><div class="stat-num c-blue"><?= $claimedCount ?></div><div class="stat-lbl">Claimed</div></div>
  <div class="stat-card"><div class="stat-num c-green"><?= $foundCount ?></div><div class="stat-lbl">Found by Me</div></div>
</div>

<div class="card card-sm" style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
  <div class="text-muted text-sm">Need to add a new found item registration?</div>
  <a href="/campusfind/items/report.php" class="btn btn-primary btn-sm">➕ Register Found Item</a>
</div>

<?php if (empty($items)): ?>
  <div class="empty">
    <div class="empty-icon">📭</div>
    <h3>No reports yet</h3>
    <p>You have not registered any found items so far.</p>
    <a href="/campusfind/items/report.php" class="btn btn-primary" style="margin-top:1.2rem">Create First Report</a>
  </div>
<?php else: ?>
  <div class="items-grid">
    <?php foreach ($items as $item): ?>
      <div class="item-card">
        <div class="item-card-top <?= $item['status'] === 'claimed' ? 'claimed' : ($item['status'] === 'found' ? 'found' : 'lost') ?>"></div>
        <div class="item-card-body">
          <div class="item-card-emoji"><?= getCategoryEmoji($item['category']) ?></div>
          <div class="item-card-cat"><?= htmlspecialchars($item['category']) ?></div>
          <div class="item-card-name">My <?= htmlspecialchars($item['category']) ?></div>
          <div class="item-card-meta">
            📅 Reported on <?= date('M j, Y', strtotime($item['created_at'])) ?><br/>
            <?php if ($item['claim_pending']): ?>
              ⏳ Claim request pending review
            <?php else: ?>
              ✅ No pending claim request
            <?php endif; ?>
          </div>

          <div style="display:flex;gap:.45rem;flex-wrap:wrap;margin-bottom:.8rem">
            <?php if ($item['status'] === 'lost'): ?>
              <span class="badge badge-lost">🔴 Lost</span>
            <?php elseif ($item['status'] === 'claimed'): ?>
              <span class="badge badge-claimed">✅ Claimed</span>
            <?php else: ?>
              <span class="badge badge-found">🟢 Found</span>
            <?php endif; ?>
          </div>

          <?php if ($item['status'] === 'claimed' && $item['claimant_name']): ?>
            <div class="contact-box" style="margin-bottom:.8rem">
              <div class="contact-label">Claimant Details</div>
              <div class="contact-name"><?= htmlspecialchars($item['claimant_name']) ?></div>
              <div class="contact-detail">📞 <?= htmlspecialchars($item['claimant_phone']) ?></div>
              <div class="contact-detail">🎓 <?= htmlspecialchars($item['claimant_roll']) ?></div>
            </div>
          <?php endif; ?>

          <div class="item-card-footer" style="align-items:flex-start">
            <div style="display:flex;gap:.45rem;flex-wrap:wrap">
              <a href="/campusfind/pages/item-detail.php?id=<?= $item['id'] ?>" class="btn btn-ghost btn-sm">View</a>

              <?php if ($item['status'] === 'lost' && $item['claim_pending']): ?>
                <form method="POST" action="/campusfind/items/update-status.php">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
                  <input type="hidden" name="action" value="confirm_claim"/>
                  <button type="submit" class="btn btn-primary btn-sm">Mark as Claimed</button>
                </form>
                <form method="POST" action="/campusfind/items/update-status.php">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
                  <input type="hidden" name="action" value="dismiss_claim"/>
                  <button type="submit" class="btn btn-yellow btn-sm">Dismiss</button>
                </form>
              <?php endif; ?>

              <?php if ($item['status'] === 'lost'): ?>
                <form method="POST" action="/campusfind/items/update-status.php">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
                  <input type="hidden" name="action" value="mark_found"/>
                  <button type="submit" class="btn btn-green btn-sm">Mark Found</button>
                </form>
              <?php endif; ?>

              <form method="POST" action="/campusfind/items/update-status.php" onsubmit="return confirm('Delete this report?');">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
                <input type="hidden" name="action" value="delete"/>
                <button type="submit" class="btn btn-red btn-sm">Delete</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
