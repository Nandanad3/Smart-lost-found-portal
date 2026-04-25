<?php
$pageTitle = 'Register Found Item';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $q1 = trim($_POST['q1'] ?? ''); $a1 = strtolower(trim($_POST['a1'] ?? ''));
    $q2 = trim($_POST['q2'] ?? ''); $a2 = strtolower(trim($_POST['a2'] ?? ''));
    $q3 = trim($_POST['q3'] ?? ''); $a3 = strtolower(trim($_POST['a3'] ?? ''));
    $q4 = trim($_POST['q4'] ?? ''); $a4 = strtolower(trim($_POST['a4'] ?? ''));

    if (!$category)       $errors[] = 'Please select a category.';
    if (!$q1 || !$a1)     $errors[] = 'Question 1 and answer are required.';
    if (!$q2 || !$a2)     $errors[] = 'Question 2 and answer are required.';
    if (!$q3 || !$a3)     $errors[] = 'Question 3 and answer are required.';
    if (!$q4 || !$a4)     $errors[] = 'Question 4 and answer are required.';

    // Answers must be single words
    foreach ([[$a1,'Answer 1'],[$a2,'Answer 2'],[$a3,'Answer 3'],[$a4,'Answer 4']] as [$ans,$label]) {
        if (str_word_count($ans) > 1) $errors[] = $label . ' must be a single word.';
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        // Insert item
        $stmt = $pdo->prepare('INSERT INTO items (category, reported_by) VALUES (?, ?)');
        $stmt->execute([$category, $_SESSION['user_id']]);
        $itemId = $pdo->lastInsertId();

        // Insert questions
        $stmt = $pdo->prepare('INSERT INTO questions (item_id,question1,answer1,question2,answer2,question3,answer3,question4,answer4) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$itemId, $q1,$a1, $q2,$a2, $q3,$a3, $q4,$a4]);
        $pdo->commit();

        setFlash('dashboard','Your found item has been registered successfully! 🎉','success');
        header('Location: /campusfind/pages/dashboard.php'); exit;
    }
}
?>

<?php flash('report'); ?>

<div class="page-hdr">
  <div class="page-tag">Report</div>
  <h1>Register a Found Item</h1>
  <p>Select the category of item you found and set 4 secret questions. Only the real owner can answer them correctly to claim it back.</p>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error">
    ❌ <div><?php foreach($errors as $e) echo htmlspecialchars($e) . '<br/>'; ?></div>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start">

  <form method="POST" class="card">
    <!-- Category -->
    <div class="form-group">
      <label class="form-label">Item Category <span class="req">*</span></label>
      <select name="category" class="form-select" required>
        <option value="">Select what you found…</option>
        <?php foreach (getCategories() as $c): ?>
          <option value="<?= $c ?>" <?= (($_POST['category']??'')===$c)?'selected':'' ?>>
            <?= getCategoryEmoji($c) ?> <?= $c ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Only the category is shown publicly — no description needed.</div>
    </div>

    <!-- 4 Questions -->
    <div class="alert alert-info" style="margin-bottom:1.5rem">
      🔐 Set <strong>4 secret questions</strong> with <strong>one-word answers</strong> that only you would know.
      Someone must answer ALL 4 correctly to claim your item.
    </div>

    <?php
    $qExamples = [
        ['What color is it?', 'black'],
        ['What brand is it?', 'samsung'],
        ['Where did you last use it?', 'library'],
        ['What is written/marked on it?', 'arjun'],
    ];
    for ($i = 1; $i <= 4; $i++):
      $ex = $qExamples[$i-1];
    ?>
    <div class="qa-item">
      <div class="form-group" style="margin-bottom:.65rem">
        <label class="form-label">Question <?= $i ?> <span class="req">*</span></label>
        <input type="text" name="q<?= $i ?>" class="form-input"
               placeholder="e.g. <?= $ex[0] ?>"
               value="<?= htmlspecialchars($_POST['q'.$i] ?? '') ?>" required/>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Answer <?= $i ?> <span class="req">*</span> <span style="font-size:.72rem;color:var(--muted);font-weight:400">(one word only)</span></label>
        <input type="text" name="a<?= $i ?>" class="form-input"
               placeholder="e.g. <?= $ex[1] ?>"
               value="<?= htmlspecialchars($_POST['a'.$i] ?? '') ?>"
               required oninput="validateOneWord(this)"/>
        <div class="form-error" id="err-a<?= $i ?>">Answer must be a single word.</div>
      </div>
    </div>
    <?php endfor; ?>

    <div style="display:flex;gap:.75rem;margin-top:1.2rem">
      <button type="submit" class="btn btn-primary btn-lg">Submit Report →</button>
      <a href="/campusfind/pages/home.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>

  <!-- Tips panel -->
  <div style="display:flex;flex-direction:column;gap:1rem">
    <div class="card" style="background:linear-gradient(135deg,#EFF6FF,#E0EAFF);border-color:#BFDBFE">
      <div style="font-size:2rem;margin-bottom:.6rem">💡</div>
      <div style="font-weight:700;margin-bottom:.7rem">Good Question Tips</div>
      <div style="font-size:.84rem;color:var(--muted);display:flex;flex-direction:column;gap:.5rem">
        <div>✅ Ask about <strong>color, brand, or model</strong></div>
        <div>✅ Ask about <strong>something written on it</strong></div>
        <div>✅ Ask about <strong>where you last had it</strong></div>
        <div>✅ Ask about a <strong>unique feature or marking</strong></div>
        <div>❌ Avoid obvious questions everyone knows</div>
        <div>❌ Avoid questions with multiple word answers</div>
      </div>
    </div>
    <div class="card" style="background:#FFFBEB;border-color:#FDE68A">
      <div style="font-weight:700;margin-bottom:.5rem">🔒 Privacy Note</div>
      <p style="font-size:.84rem;color:var(--muted)">
        Only the <strong>category</strong> of your item is visible to others.
        Your questions, answers, and contact details are <strong>completely hidden</strong> until a claim is approved.
      </p>
    </div>
    <div class="card" style="background:#F0FDF4;border-color:#BBF7D0">
      <div style="font-weight:700;margin-bottom:.5rem">📋 How Claiming Works</div>
      <div style="font-size:.84rem;color:var(--muted);display:flex;flex-direction:column;gap:.4rem">
        <div>1️⃣ Someone sees your report and clicks <strong>Claim This</strong></div>
        <div>2️⃣ They answer all 4 of your questions</div>
        <div>3️⃣ You get notified to <strong>confirm the claim</strong></div>
        <div>4️⃣ You confirm → contact details revealed</div>
      </div>
    </div>
  </div>
</div>

<script>
function validateOneWord(input) {
  var errEl = document.getElementById('err-' + input.name.replace('a', 'err-a').replace('err-err-a','err-a'));
  // simpler:
  var words = input.value.trim().split(/\s+/);
  var errId = 'err-a' + input.name.replace('a','');
  var err   = document.getElementById(errId);
  if (err) err.style.display = words.length > 1 ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>