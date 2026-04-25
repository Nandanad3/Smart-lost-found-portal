<?php
require_once __DIR__ . '/../config/db.php';
if (isLoggedIn()) { header('Location: /campusfind/pages/home.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            setFlash('home', 'Welcome back, ' . explode(' ', $user['name'])[0] . '! 👋', 'success');
            $redirect = $_GET['redirect'] ?? '/campusfind/pages/home.php';
            header('Location: ' . $redirect); exit;
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Log In — CampusFind</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>
<nav class="nav">
  <a href="/campusfind/pages/home.php" class="nav-logo">Campus<span>Find</span></a>
  <div class="nav-links">
    <a href="/campusfind/auth/signup.php" class="btn btn-primary btn-sm">Sign Up</a>
  </div>
</nav>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">Campus<span>Find</span></div>
    <div class="auth-sub">Log in to browse, report, and claim lost items on campus.</div>

    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" placeholder="arjun@college.edu"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Your password" required/>
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:.5rem">
        Log In →
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="/campusfind/auth/signup.php">Sign up</a>
    </div>
  </div>
</div>
<script src="/campusfind/assets/js/main.js"></script>
</body>
</html>