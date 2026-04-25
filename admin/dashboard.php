<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /campusfind/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?> — CampusFind</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>

<nav class="nav">
  <a href="/campusfind/pages/home.php" class="nav-logo">Campus<span>Find</span> <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--purple);margin-left:.35rem">Admin</span></a>
  
  <div class="nav-links">
    <a href="/campusfind/admin/dashboard.php" class="nav-link active">📊 Dashboard</a>
    <a href="/campusfind/admin/users.php" class="nav-link">👥 Users</a>
    <a href="/campusfind/admin/items.php" class="nav-link">📦 Items</a>
  </div>

  <div class="flex gap-sm" style="align-items:center">
    <div class="nav-user">
      <div class="nav-avatar">🛡️</div>
      <span style="font-weight:600;font-size:.84rem"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
    </div>
    <a href="/campusfind/admin/logout.php" class="btn btn-ghost btn-sm">Log Out</a>
  </div>
</nav>

<div class="container page-wrap">
  <?php flash('admin'); ?>

  <div class="page-hdr">
    <div class="page-tag">🛡️ Administration</div>
    <h1>Admin Dashboard</h1>
    <p>Manage users, view items, and monitor campus lost & found activity.</p>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <?php
    $totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $totalItems = $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
    $lostItems  = $pdo->query('SELECT COUNT(*) FROM items WHERE status="lost"')->fetchColumn();
    $claimed    = $pdo->query('SELECT COUNT(*) FROM items WHERE status="claimed"')->fetchColumn();
    $activeNotices = $pdo->query('SELECT COUNT(*) FROM lost_notices WHERE status="active"')->fetchColumn();
    $resolvedNotices = $pdo->query('SELECT COUNT(*) FROM lost_notices WHERE status="resolved"')->fetchColumn();
    ?>
    <div class="stat-card">
      <div class="stat-num c-blue"><?= $totalUsers ?></div>
      <div class="stat-lbl">Registered Users</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-yellow"><?= $totalItems ?></div>
      <div class="stat-lbl">Total Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-red"><?= $lostItems ?></div>
      <div class="stat-lbl">Lost Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-green"><?= $claimed ?></div>
      <div class="stat-lbl">Claimed Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-blue"><?= $activeNotices ?></div>
      <div class="stat-lbl">Active Lost Notices</div>
    </div>
    <div class="stat-card">
      <div class="stat-num c-green"><?= $resolvedNotices ?></div>
      <div class="stat-lbl">Resolved Notices</div>
    </div>
  </div>

  <!-- Actions -->
  <div class="card" style="margin-bottom:2rem">
    <div style="font-weight:700;margin-bottom:1rem">Quick Actions</div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
      <a href="/campusfind/admin/users.php" class="btn btn-primary">👥 Manage Users</a>
      <a href="/campusfind/admin/items.php" class="btn btn-primary">📦 Manage Items</a>
      <a href="/campusfind/pages/home.php" class="btn btn-ghost">🎓 View as Student</a>
    </div>
  </div>

  <!-- Recent items table -->
  <div class="card">
    <div style="font-weight:700;margin-bottom:1rem">📋 Recent Items</div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Reported By</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $pdo->query('SELECT i.*, u.name, u.roll_number FROM items i JOIN users u ON i.reported_by=u.id ORDER BY i.created_at DESC LIMIT 10');
          while ($item = $stmt->fetch()):
          ?>
          <tr>
            <td><?= getCategoryEmoji($item['category']) ?> <?= htmlspecialchars($item['category']) ?></td>
            <td><?= htmlspecialchars($item['name']) ?><br/><span style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($item['roll_number']) ?></span></td>
            <td>
              <?php if ($item['status'] === 'lost'): ?>
                <span class="badge badge-lost">🔴 Lost</span>
              <?php elseif ($item['status'] === 'claimed'): ?>
                <span class="badge badge-claimed">✅ Claimed</span>
              <?php else: ?>
                <span class="badge badge-found">🟢 Found</span>
              <?php endif; ?>
            </td>
            <td style="font-size:.82rem;color:var(--muted)"><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>