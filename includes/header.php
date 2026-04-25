<?php
require_once __DIR__ . '/../config/db.php';

// Get current user info if logged in
$currentUser = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — CampusFind' : 'CampusFind' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>

<nav class="nav">
  <a href="/campusfind/pages/home.php" class="nav-logo">Campus<span>Find</span></a>

  <div class="nav-links">
    <?php if ($currentUser): ?>
      <a href="/campusfind/pages/notice-board.php" class="nav-link <?= $currentPage === 'notice-board.php' ? 'active' : '' ?>">📌 Notice Board</a>
      <a href="/campusfind/pages/home.php"      class="nav-link <?= $currentPage === 'home.php'      ? 'active' : '' ?>">🔍 Browse</a>
      <a href="/campusfind/pages/dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">📋 My Items</a>
      <a href="/campusfind/items/report.php"    class="nav-link <?= $currentPage === 'report.php'    ? 'active' : '' ?>">➕ Report</a>
      <a href="/campusfind/items/report-lost.php" class="nav-link <?= $currentPage === 'report-lost.php' ? 'active' : '' ?>">📝 Lost Report</a>
    <?php else: ?>
      <a href="/campusfind/pages/notice-board.php" class="nav-link <?= $currentPage === 'notice-board.php' ? 'active' : '' ?>">📌 Notice Board</a>
      <a href="/campusfind/pages/home.php"   class="nav-link <?= $currentPage === 'home.php'  ? 'active' : '' ?>">🔍 Browse</a>
      <a href="/campusfind/auth/login.php"   class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>">Log In</a>
      <a href="/campusfind/auth/signup.php"  class="nav-link btn btn-primary btn-sm" style="margin-left:.3rem">Sign Up</a>
    <?php endif; ?>
  </div>

  <?php if ($currentUser): ?>
  <div class="flex gap-sm" style="align-items:center">
    <div class="nav-user">
      <div class="nav-avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></div>
      <span style="font-weight:600;font-size:.84rem"><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?></span>
    </div>
    <a href="/campusfind/auth/logout.php" class="btn btn-ghost btn-sm">Log Out</a>
  </div>
  <?php endif; ?>
</nav>

<div class="container page-wrap">