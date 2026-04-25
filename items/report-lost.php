<?php
$pageTitle = 'Report Lost Item';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$stmt = $pdo->prepare('SELECT phone, email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch() ?: ['phone' => '', 'email' => ''];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $lostLocation = trim($_POST['lost_location'] ?? '');
    $lostDate     = trim($_POST['lost_date'] ?? '');
    $phone        = trim($_POST['contact_phone'] ?? '');
    $email        = trim($_POST['contact_email'] ?? '');
    $reward       = trim($_POST['reward_note'] ?? '');

    if ($title === '') $errors[] = 'Item name/title is required.';
    if ($category === '') $errors[] = 'Please select a category.';
    if ($lostLocation === '') $errors[] = 'Lost location is required.';
    if ($phone === '') $errors[] = 'Contact phone is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid contact email.';

    if ($lostDate !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $lostDate);
        if (!$d || $d->format('Y-m-d') !== $lostDate) {
            $errors[] = 'Lost date must be in valid format.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('
            INSERT INTO lost_notices
            (user_id, title, category, description, lost_location, lost_date, contact_phone, contact_email, reward_note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")
        ');
        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $category,
            $description ?: null,
            $lostLocation,
            $lostDate ?: null,
            $phone,
            $email ?: null,
            $reward ?: null
        ]);

        setFlash('notice', 'Lost item notice published successfully.', 'success');
        header('Location: /campusfind/pages/notice-board.php');
        exit;
    }
}
?>

<?php flash('notice'); ?>

<div class="page-hdr">
  <div class="page-tag">Public Notice</div>
  <h1>Report Lost Item</h1>
  <p>This post appears on the public notice board so anyone can contact you directly if they found it.</p>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error">❌ <div><?php foreach($errors as $e) echo htmlspecialchars($e) . '<br/>'; ?></div></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 330px;gap:1.2rem;align-items:start">
  <form method="POST" class="card">
    <div class="form-group">
      <label class="form-label">Item Name / Title <span class="req">*</span></label>
      <input type="text" name="title" class="form-input" maxlength="140" placeholder="e.g. Black Wallet with College ID"
             value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required/>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Category <span class="req">*</span></label>
        <select name="category" class="form-select" required>
          <option value="">Select category</option>
          <?php foreach (getCategories() as $c): ?>
            <option value="<?= $c ?>" <?= (($_POST['category'] ?? '') === $c) ? 'selected' : '' ?>>
              <?= getCategoryEmoji($c) ?> <?= $c ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Lost Date</label>
        <input type="date" name="lost_date" class="form-input" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['lost_date'] ?? '') ?>"/>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Where did you lose it? <span class="req">*</span></label>
      <input type="text" name="lost_location" class="form-input" maxlength="190" placeholder="e.g. Library 2nd floor"
             value="<?= htmlspecialchars($_POST['lost_location'] ?? '') ?>" required/>
    </div>

    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-textarea" placeholder="Add colors, marks, stickers, or any identifying details..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Contact Phone <span class="req">*</span></label>
        <input type="text" name="contact_phone" class="form-input" placeholder="+91 98765 43210"
               value="<?= htmlspecialchars($_POST['contact_phone'] ?? $me['phone']) ?>" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Email</label>
        <input type="email" name="contact_email" class="form-input" placeholder="optional"
               value="<?= htmlspecialchars($_POST['contact_email'] ?? $me['email']) ?>"/>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Reward Note (optional)</label>
      <input type="text" name="reward_note" class="form-input" maxlength="190" placeholder="e.g. Reward available"
             value="<?= htmlspecialchars($_POST['reward_note'] ?? '') ?>"/>
    </div>

    <div style="display:flex;gap:.75rem;margin-top:1rem">
      <button type="submit" class="btn btn-primary btn-lg">Publish Notice →</button>
      <a href="/campusfind/pages/notice-board.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>

  <div style="display:flex;flex-direction:column;gap:1rem">
    <div class="card" style="background:#EFF6FF;border-color:#BFDBFE">
      <div style="font-weight:700;margin-bottom:.55rem">📌 Public Visibility</div>
      <p class="text-sm text-muted">This notice is visible to everyone, even without login. Anyone who finds your item can call/message you directly.</p>
    </div>
    <div class="card" style="background:#FFFBEB;border-color:#FDE68A">
      <div style="font-weight:700;margin-bottom:.55rem">🔒 Privacy Tip</div>
      <p class="text-sm text-muted">Do not add sensitive details (passwords, PIN, exact ID number). Share only safe identifying hints.</p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
