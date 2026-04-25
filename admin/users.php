<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete_user' && $userId > 0) {
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId) {
            setFlash('admin', 'Cannot delete currently logged-in student session user.', 'warn');
            header('Location: /campusfind/admin/users.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('DELETE FROM lost_notices WHERE user_id = ?');
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare('DELETE q FROM questions q JOIN items i ON q.item_id = i.id WHERE i.reported_by = ?');
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare('DELETE FROM items WHERE reported_by = ?');
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare('UPDATE items SET claim_pending = NULL WHERE claim_pending = ?');
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare('UPDATE items SET claimed_by = NULL, status = IF(status = "claimed", "lost", status) WHERE claimed_by = ?');
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                setFlash('admin', 'User deleted successfully.', 'success');
            } else {
                $pdo->rollBack();
                setFlash('admin', 'User not found.', 'warn');
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('admin', 'Delete failed: ' . $e->getMessage(), 'error');
        }
    }

    header('Location: /campusfind/admin/users.php');
    exit;
}

$users = $pdo->query('
    SELECT
        u.*,
        (SELECT COUNT(*) FROM items i WHERE i.reported_by = u.id) AS items_count,
        (SELECT COUNT(*) FROM lost_notices ln WHERE ln.user_id = u.id) AS notices_count
    FROM users u
    ORDER BY u.id DESC
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
    <a href="/campusfind/admin/users.php" class="nav-link active">👥 Users</a>
    <a href="/campusfind/admin/items.php" class="nav-link">📦 Items</a>
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
    <h1>Manage Users</h1>
    <p>View registered users and remove accounts when required.</p>
  </div>

  <div class="card">
    <div style="font-weight:700;margin-bottom:1rem">Registered Users (<?= count($users) ?>)</div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Contact</th>
            <th>Academic</th>
            <th>Activity</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <?= htmlspecialchars($u['name']) ?><br/>
                <span style="font-size:.75rem;color:var(--muted)">ID #<?= (int)$u['id'] ?></span>
              </td>
              <td>
                <?= htmlspecialchars($u['email']) ?><br/>
                <span style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($u['phone']) ?></span>
              </td>
              <td>
                <?= htmlspecialchars($u['roll_number']) ?><br/>
                <span style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($u['department']) ?></span>
              </td>
              <td>
                <span class="badge badge-claimed">📦 <?= (int)$u['items_count'] ?> items</span>
                <span class="badge badge-found">📌 <?= (int)$u['notices_count'] ?> notices</span>
              </td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this user and all their data?');">
                  <input type="hidden" name="action" value="delete_user"/>
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"/>
                  <button type="submit" class="btn btn-red btn-sm">Delete User</button>
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
