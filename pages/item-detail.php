<?php
$pageTitle = 'Item Detail';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$itemId = intval($_GET['id'] ?? 0);
if (!$itemId) {
    setFlash('home', 'Invalid item selected.', 'error');
    header('Location: /campusfind/pages/home.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT i.*, u.name AS reporter_name, u.phone AS reporter_phone, u.roll_number AS reporter_roll
    FROM items i
    JOIN users u ON i.reported_by = u.id
    WHERE i.id = ?
');
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('home', 'Item not found.', 'error');
    header('Location: /campusfind/pages/home.php');
    exit;
}

$isOwner = intval($item['reported_by']) === intval($_SESSION['user_id']);
$isPendingClaimant = !empty($item['claim_pending']) && intval($item['claim_pending']) === intval($_SESSION['user_id']);

$claimant = null;
if (!empty($item['claimed_by'])) {
    $cStmt = $pdo->prepare('SELECT name, phone, roll_number FROM users WHERE id = ?');
    $cStmt->execute([$item['claimed_by']]);
    $claimant = $cStmt->fetch();
}
?>

<?php flash('home'); ?>
<?php flash('dashboard'); ?>
<?php flash('claim'); ?>

<div class="page-hdr">
  <div class="page-tag">Item</div>
  <h1>Item Detail</h1>
  <p>View the report status and related claim information for this item.</p>
</div>

<div class="card" style="margin-bottom:1.3rem">
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
    <div style="font-size:3rem"><?= getCategoryEmoji($item['category']) ?></div>
    <div>
      <div style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700"><?= htmlspecialchars($item['category']) ?></div>
      <h2 style="font-size:1.25rem;font-weight:800">Lost <?= htmlspecialchars($item['category']) ?></h2>
      <div class="text-sm text-muted">Reported on <?= date('M j, Y', strtotime($item['created_at'])) ?></div>
    </div>
    <div style="margin-left:auto">
      <?php if ($item['status'] === 'lost'): ?>
        <span class="badge badge-lost">🔴 Lost</span>
      <?php elseif ($item['status'] === 'claimed'): ?>
        <span class="badge badge-claimed">✅ Claimed</span>
      <?php else: ?>
        <span class="badge badge-found">🟢 Found</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
  <div class="card">
    <div style="font-weight:700;margin-bottom:.7rem">Report Details</div>
    <div class="text-sm text-muted" style="display:flex;flex-direction:column;gap:.35rem">
      <div>Reporter: <strong style="color:var(--text)"><?= htmlspecialchars($item['reporter_name']) ?></strong></div>
      <div>Roll: <strong style="color:var(--text)"><?= htmlspecialchars($item['reporter_roll']) ?></strong></div>
      <?php if ($isOwner || $isPendingClaimant || ($item['status'] === 'claimed' && $claimant && intval($item['claimed_by']) === intval($_SESSION['user_id']))): ?>
        <div>Phone: <strong style="color:var(--text)"><?= htmlspecialchars($item['reporter_phone']) ?></strong></div>
      <?php endif; ?>
      <div>Current Status: <strong style="color:var(--text)"><?= htmlspecialchars(ucfirst($item['status'])) ?></strong></div>
      <?php if (!empty($item['claim_pending'])): ?>
        <div>Claim Request: <strong style="color:var(--text)">Pending</strong></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div style="font-weight:700;margin-bottom:.7rem">Actions</div>
    <div style="display:flex;gap:.55rem;flex-wrap:wrap">
      <a href="/campusfind/pages/home.php" class="btn btn-ghost btn-sm">Back to Browse</a>
      <a href="/campusfind/pages/dashboard.php" class="btn btn-ghost btn-sm">My Dashboard</a>

      <?php if (!$isOwner && $item['status'] === 'lost' && intval($item['claim_pending']) !== intval($_SESSION['user_id'])): ?>
        <a href="/campusfind/items/claim.php?id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">Claim This Item</a>
      <?php endif; ?>
    </div>

    <?php if ($isOwner): ?>
      <div style="margin-top:1rem;display:flex;gap:.55rem;flex-wrap:wrap">
        <?php if ($item['status'] === 'lost' && $item['claim_pending']): ?>
          <form method="POST" action="/campusfind/items/update-status.php">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
            <input type="hidden" name="action" value="confirm_claim"/>
            <button type="submit" class="btn btn-primary btn-sm">Mark as Claimed</button>
          </form>
          <form method="POST" action="/campusfind/items/update-status.php">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
            <input type="hidden" name="action" value="dismiss_claim"/>
            <button type="submit" class="btn btn-yellow btn-sm">Dismiss Claim</button>
          </form>
        <?php endif; ?>

        <?php if ($item['status'] === 'lost'): ?>
          <form method="POST" action="/campusfind/items/update-status.php">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>"/>
            <input type="hidden" name="action" value="mark_found"/>
            <button type="submit" class="btn btn-green btn-sm">Mark Found</button>
          </form>
        <?php endif; ?>
      </div>
    <?php elseif ($isPendingClaimant || ($item['status'] === 'claimed' && $claimant && intval($item['claimed_by']) === intval($_SESSION['user_id']))): ?>
      <div class="contact-box" style="margin-top:1rem">
        <div class="contact-label">Reporter Contact</div>
        <div class="contact-name"><?= htmlspecialchars($item['reporter_name']) ?></div>
        <div class="contact-detail">📞 <?= htmlspecialchars($item['reporter_phone']) ?></div>
        <div class="contact-detail">🎓 <?= htmlspecialchars($item['reporter_roll']) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($claimant && $isOwner): ?>
  <div class="card" style="margin-top:1rem">
    <div style="font-weight:700;margin-bottom:.7rem">Claimant Details</div>
    <div class="text-sm text-muted">
      <?= htmlspecialchars($claimant['name']) ?> · <?= htmlspecialchars($claimant['roll_number']) ?> · <?= htmlspecialchars($claimant['phone']) ?>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
