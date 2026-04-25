<?php
$pageTitle = 'Manage Items';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemId = (int)($_POST['item_id'] ?? 0);

    if ($action === 'delete_item' && $itemId > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('DELETE FROM questions WHERE item_id = ?');
            $stmt->execute([$itemId]);

            $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
            $stmt->execute([$itemId]);

            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                setFlash('admin', 'Item deleted successfully.', 'success');
            } else {
                $pdo->rollBack();
                setFlash('admin', 'Item not found.', 'warn');
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('admin', 'Delete failed: ' . $e->getMessage(), 'error');
        }
    }

    header('Location: /campusfind/admin/items.php');
    exit;
}

$items = $pdo->query('
    SELECT i.*, u.name AS reporter_name, u.roll_number
    FROM items i
    JOIN users u ON i.reported_by = u.id
    ORDER BY i.created_at DESC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle) ?> — CampusFind</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>
<nav class="nav">
  <a href="/campusfind/admin/dashboard.php" class="nav-logo">Campus<span>Find</span> <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--purple);margin-left:.35rem">Admin</span></a>
  <div class="nav-links">
    <a href="/campusfind/admin/dashboard.php" class="nav-link">📊 Dashboard</a>
    <a href="/campusfind/admin/users.php" class="nav-link">👥 Users</a>
    <a href="/campusfind/admin/items.php" class="nav-link active">📦 Items</a>
  </div>
  <div class="flex gap-sm" style="align-items:center">
    <div class="nav-user">
      <div class="nav-avatar">🛡️</div>
      <span style="font-weight:600;font-size:.84rem"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'admin') ?></span>
    </div>
    <a href="/campusfind/admin/logout.php" class="btn btn-ghost btn-sm">Log Out</a>
  </div>
</nav>

<div class="container page-wrap">
  <?php flash('admin'); ?>

  <div class="page-hdr">
    <div class="page-tag">🛡️ Administration</div>
    <h1>Manage Items</h1>
    <p>Review reported items and delete invalid or duplicate posts.</p>
  </div>

  <div class="card">
    <div style="font-weight:700;margin-bottom:1rem">All Items (<?= count($items) ?>)</div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Reporter</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= getCategoryEmoji($it['category']) ?> <?= htmlspecialchars($it['category']) ?></td>
              <td>
                <?= htmlspecialchars($it['reporter_name']) ?><br/>
                <span style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($it['roll_number']) ?></span>
              </td>
              <td>
                <?php if ($it['status'] === 'lost'): ?>
                  <span class="badge badge-lost">🔴 Lost</span>
                <?php elseif ($it['status'] === 'claimed'): ?>
                  <span class="badge badge-claimed">✅ Claimed</span>
                <?php else: ?>
                  <span class="badge badge-found">🟢 Found</span>
                <?php endif; ?>
              </td>
              <td style="font-size:.82rem;color:var(--muted)"><?= date('M j, Y', strtotime($it['created_at'])) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this item permanently?');">
                  <input type="hidden" name="action" value="delete_item"/>
                  <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>"/>
                  <button type="submit" class="btn btn-red btn-sm">Delete Item</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="/campusfind/assets/js/main.js"></script>
</body>
</html>
