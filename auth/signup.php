<?php
require_once __DIR__ . '/../config/db.php';
if (isLoggedIn()) { header('Location: /campusfind/pages/home.php'); exit; }

$errors = [];
$vals   = ['name'=>'','email'=>'','phone'=>'','roll'=>'','dept'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $roll       = trim($_POST['roll_number']?? '');
    $dept       = trim($_POST['department'] ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm_password']?? '';

    $vals = compact('name','email','phone','roll','dept');

    if (!$name)                            $errors['name']     = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
    if (!preg_match('/^[0-9+\- ]{7,15}$/', $phone)) $errors['phone'] = 'Valid phone required.';
    if (!$roll)                            $errors['roll']     = 'Roll number required.';
    if (!$dept)                            $errors['dept']     = 'Department required.';
    if (strlen($password) < 6)             $errors['password'] = 'Min 6 characters.';
    if ($password !== $confirm)            $errors['confirm']  = 'Passwords do not match.';

    if (empty($errors)) {
        // Check duplicate email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (name,email,phone,roll_number,department,password) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name,$email,$phone,$roll,$dept,$hash]);
            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            setFlash('home','Welcome to CampusFind, ' . explode(' ',$name)[0] . '! 🎉','success');
            header('Location: /campusfind/pages/home.php'); exit;
        }
    }
}

$pageTitle = 'Sign Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up — CampusFind</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>
<nav class="nav">
  <a href="/campusfind/pages/home.php" class="nav-logo">Campus<span>Find</span></a>
  <div class="nav-links">
    <a href="/campusfind/auth/login.php" class="nav-link">Log In</a>
  </div>
</nav>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">Campus<span>Find</span></div>
    <div class="auth-sub">Create your student account to report and find lost items.</div>

    <?php if (!empty($errors) && isset($errors['general'])): ?>
      <div class="alert alert-error">❌ <?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" id="signupForm">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-input <?= isset($errors['name']) ? 'error' : '' ?>"
                 value="<?= htmlspecialchars($vals['name']) ?>" placeholder="Arjun Kumar" required/>
          <?php if (isset($errors['name'])): ?>
            <div class="form-error show"><?= $errors['name'] ?></div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Roll Number <span class="req">*</span></label>
          <input type="text" name="roll_number" class="form-input <?= isset($errors['roll']) ? 'error' : '' ?>"
                 value="<?= htmlspecialchars($vals['roll']) ?>" placeholder="CS21001" required/>
          <?php if (isset($errors['roll'])): ?>
            <div class="form-error show"><?= $errors['roll'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address <span class="req">*</span></label>
        <input type="email" name="email" class="form-input <?= isset($errors['email']) ? 'error' : '' ?>"
               value="<?= htmlspecialchars($vals['email']) ?>" placeholder="arjun@college.edu" required/>
        <?php if (isset($errors['email'])): ?>
          <div class="form-error show"><?= $errors['email'] ?></div>
        <?php endif; ?>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Phone Number <span class="req">*</span></label>
          <input type="tel" name="phone" class="form-input <?= isset($errors['phone']) ? 'error' : '' ?>"
                 value="<?= htmlspecialchars($vals['phone']) ?>" placeholder="+91 98765 43210" required/>
          <?php if (isset($errors['phone'])): ?>
            <div class="form-error show"><?= $errors['phone'] ?></div>
          <?php endif; ?>
          <div class="form-hint">📞 Shared only when a claim is approved</div>
        </div>
        <div class="form-group">
          <label class="form-label">Department <span class="req">*</span></label>
          <select name="department" class="form-select <?= isset($errors['dept']) ? 'error' : '' ?>" required>
            <option value="">Select department…</option>
            <?php foreach(['CSE','ECE','EEE','ME','CE','IT','MCA','MBA','Other'] as $d): ?>
              <option value="<?= $d ?>" <?= ($vals['dept']==$d)?'selected':'' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['dept'])): ?>
            <div class="form-error show"><?= $errors['dept'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <input type="password" name="password" class="form-input <?= isset($errors['password']) ? 'error' : '' ?>"
                 placeholder="Min 6 characters" required/>
          <?php if (isset($errors['password'])): ?>
            <div class="form-error show"><?= $errors['password'] ?></div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span class="req">*</span></label>
          <input type="password" name="confirm_password" class="form-input <?= isset($errors['confirm']) ? 'error' : '' ?>"
                 placeholder="Repeat password" required/>
          <?php if (isset($errors['confirm'])): ?>
            <div class="form-error show"><?= $errors['confirm'] ?></div>
          <?php endif; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:.5rem">
        Create Account →
      </button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="/campusfind/auth/login.php">Log in</a>
    </div>
  </div>
</div>
<script src="/campusfind/assets/js/main.js"></script>
</body>
</html>