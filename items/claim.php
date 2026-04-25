<?php
$pageTitle = 'Claim Item';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$itemId = intval($_GET['id'] ?? 0);
if (!$itemId) { header('Location: /campusfind/pages/home.php'); exit; }

// Fetch item
$stmt = $pdo->prepare('SELECT i.*, u.name as reporter_name, u.phone as reporter_phone, u.roll_number as reporter_roll FROM items i JOIN users u ON i.reported_by=u.id WHERE i.id=? AND i.status="lost"');
$stmt->execute([$itemId]);
$item = $stmt->fetch();
if (!$item) {
    setFlash('home','This item is no longer available.','warn');
    header('Location: /campusfind/pages/home.php'); exit;
}

// Cannot claim own item
if ($item['reported_by'] == $_SESSION['user_id']) {
    setFlash('home','You cannot claim your own report.','error');
    header('Location: /campusfind/pages/home.php'); exit;
}

// Already pending
if ($item['claim_pending'] == $_SESSION['user_id']) {
    setFlash('claim', 'You already answered correctly. Reporter contact is available below.', 'success');
    header('Location: /campusfind/pages/item-detail.php?id=' . $itemId); exit;
}

// Fetch questions
$stmt = $pdo->prepare('SELECT * FROM questions WHERE item_id = ?');
$stmt->execute([$itemId]);
$qs = $stmt->fetch();
if (!$qs) {
    setFlash('home','This item has no questions set.','warn');
    header('Location: /campusfind/pages/home.php'); exit;
}

$errors  = [];
$correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ua1 = strtolower(trim($_POST['a1'] ?? ''));
    $ua2 = strtolower(trim($_POST['a2'] ?? ''));
    $ua3 = strtolower(trim($_POST['a3'] ?? ''));
    $ua4 = strtolower(trim($_POST['a4'] ?? ''));

    if (!$ua1||!$ua2||!$ua3||!$ua4) {
        $errors[] = 'Please answer all 4 questions.';
    } else {
        $score = 0;
        if ($ua1 === $qs['answer1']) $score++;
        if ($ua2 === $qs['answer2']) $score++;
        if ($ua3 === $qs['answer3']) $score++;
        if ($ua4 === $qs['answer4']) $score++;

        if ($score === 4) {
            // All correct — mark claim_pending
            $stmt = $pdo->prepare('UPDATE items SET claim_pending=? WHERE id=?');
            $stmt->execute([$_SESSION['user_id'], $itemId]);
            setFlash('claim', 'All answers correct. Reporter contact details are now visible.', 'success');
            header('Location: /campusfind/pages/item-detail.php?id=' . $itemId);
            exit;
        } else {
            $errors[] = 'Your answers are incorrect (' . $score . '/4 correct). Please try again.';
        }
    }
}
?>

<?php flash('claim'); ?>

<div class="page-hdr">
  <div class="page-tag">Claim</div>
  <h1>Claim This Item</h1>
  <p>Answer all 4 secret questions set by the reporter. All answers must be correct to submit your claim.</p>
</div>

<!-- Item info card -->
<div class="card card-sm" style="margin-bottom:1.5rem;background:linear-gradient(135deg,#EFF6FF,#F5F3FF);border-color:#BFDBFE">
  <div style="display:flex;align-items:center;gap:1rem">
    <div style="font-size:2.5rem"><?= getCategoryEmoji($item['category']) ?></div>
    <div>
      <div style="font-weight:700;font-size:1.05rem"><?= htmlspecialchars($item['category']) ?></div>
      <div style="font-size:.82rem;color:var(--muted)">
        Reported by <?= htmlspecialchars($item['reporter_name']) ?> ·
        <?= date('M j, Y', strtotime($item['created_at'])) ?>
      </div>
    </div>
    <span class="badge badge-lost" style="margin-left:auto">🔴 Lost</span>
  </div>
</div>

<?php if ($correct): ?>
  <!-- Success state -->
  <div class="card" style="text-align:center;padding:3rem 2rem">
    <div style="font-size:3rem;margin-bottom:1rem">🎉</div>
    <h2 style="font-weight:800;margin-bottom:.5rem;color:var(--green)">All Answers Correct!</h2>
    <p style="color:var(--muted);margin-bottom:1.5rem">
      You answered all questions correctly. Reporter details are now visible for contact.
    </p>
    <div class="alert alert-success" style="text-align:left;margin-bottom:1.5rem">
      <strong>Reporter:</strong> <?= htmlspecialchars($item['reporter_name']) ?><br/>
      <strong>Roll:</strong> <?= htmlspecialchars($item['reporter_roll']) ?><br/>
      <strong>Phone:</strong> <?= htmlspecialchars($item['reporter_phone']) ?>
    </div>
    <a href="/campusfind/pages/item-detail.php?id=<?= $item['id'] ?>" class="btn btn-primary">View Full Item Detail →</a>
  </div>

<?php else: ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">❌ <?= htmlspecialchars($errors[0]) ?></div>
  <?php endif; ?>

  <div class="alert alert-warn" style="margin-bottom:1.5rem">
    ⚠️ You must answer <strong>ALL 4 questions correctly</strong>.
    Answers are case-insensitive single words.
  </div>

  <form method="POST" class="card">
    <?php for ($i = 1; $i <= 4; $i++): ?>
    <div class="qa-item">
      <div class="qa-question">Question <?= $i ?> — <?= htmlspecialchars($qs['question' . $i]) ?></div>
      <div class="qa-hint">Enter a single word answer</div>
      <input type="text" name="a<?= $i ?>" class="form-input"
             placeholder="Your answer…"
             value="<?= htmlspecialchars($_POST['a'.$i] ?? '') ?>"
             required autocomplete="off"/>
    </div>
    <?php endfor; ?>

    <div style="display:flex;gap:.75rem;margin-top:1.2rem">
      <button type="submit" class="btn btn-primary btn-lg">Submit Answers →</button>
      <a href="/campusfind/pages/home.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>