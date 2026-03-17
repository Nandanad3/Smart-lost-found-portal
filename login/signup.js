/* ============================================================
   CampusFind — signup.js
   ============================================================ */

/* If already logged in, skip to dashboard */
if (localStorage.getItem('cf_currentUser')) {
  window.location.href = '../Item-management/dashboard.html';
}

/* Allow Enter key to submit */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') handleSignup();
});

/* Toggle password visibility */
function togglePass(inputId, btn) {
  var input = document.getElementById(inputId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

/* ── PROGRESS BAR ───────────────────────────────────────── */
var FIELDS = ['signupFirst','signupLast','signupRoll','signupEmail','signupDept','signupPass'];

function updateProgress() {
  var filled = FIELDS.filter(function(id) {
    return document.getElementById(id).value.trim().length > 0;
  }).length;
  var pct = Math.round((filled / FIELDS.length) * 100);
  document.getElementById('progressBar').style.width = pct + '%';
}

/* ── PASSWORD STRENGTH ──────────────────────────────────── */
function checkStrength() {
  var pass = document.getElementById('signupPass').value;
  var wrap = document.getElementById('strengthWrap');
  var bar  = document.getElementById('strengthBar');
  var lbl  = document.getElementById('strengthLabel');

  if (!pass) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'flex';

  var strong = pass.length >= 8 && /[A-Z]/.test(pass) && /[0-9]/.test(pass) && /[^a-zA-Z0-9]/.test(pass);
  var medium = pass.length >= 6 && (/[A-Z]/.test(pass) || /[0-9]/.test(pass));

  if (strong) {
    bar.className = 'strength-bar strong';
    lbl.className = 'strength-label strong';
    lbl.textContent = 'Strong';
  } else if (medium) {
    bar.className = 'strength-bar medium';
    lbl.className = 'strength-label medium';
    lbl.textContent = 'Medium';
  } else {
    bar.className = 'strength-bar weak';
    lbl.className = 'strength-label weak';
    lbl.textContent = 'Weak';
  }
}

/* ── SIGN UP ────────────────────────────────────────────── */
function handleSignup() {
  var first = document.getElementById('signupFirst').value.trim();
  var last  = document.getElementById('signupLast').value.trim();
  var roll  = document.getElementById('signupRoll').value.trim();
  var email = document.getElementById('signupEmail').value.trim().toLowerCase();
  var dept  = document.getElementById('signupDept').value.trim();
  var pass  = document.getElementById('signupPass').value;

  clearErrors();
  var hasError = false;

  function fail(id, msg) {
    setError(id, true);
    if (!hasError) showToast(msg, 'err');
    hasError = true;
  }

  if (!first) fail('signupFirst', '⚠️ Please enter your first name.');
  if (!last)  fail('signupLast',  '⚠️ Please enter your last name.');
  if (!roll)  fail('signupRoll',  '⚠️ Please enter your roll number.');
  if (!email || !email.includes('@')) fail('signupEmail', '⚠️ Please enter a valid email address.');
  if (!dept)  fail('signupDept',  '⚠️ Please enter your department.');
  if (pass.length < 6) fail('signupPass', '⚠️ Password must be at least 6 characters.');
  if (hasError) return;

  /* Check for duplicate email */
  var users  = JSON.parse(localStorage.getItem('cf_users') || '[]');
  var exists = users.find(function(u) { return u.email === email; });
  if (exists) {
    setError('signupEmail', true);
    showToast('⚠️ This email is already registered. Try logging in.', 'warn');
    return;
  }

  /* Build user object */
  var newUser = {
    name:   first + ' ' + last,
    first:  first,
    last:   last,
    roll:   roll,
    email:  email,
    dept:   dept,
    pass:   pass,
    joined: new Date().toLocaleDateString('en-IN', { month: 'short', year: 'numeric' })
  };

  users.push(newUser);
  localStorage.setItem('cf_users', JSON.stringify(users));

  /* Auto login after signup */
  localStorage.setItem('cf_currentUser', JSON.stringify(newUser));

  /* Mark inputs green */
  FIELDS.forEach(function(id) { setValid(id, true); });

  showToast('🎉 Account created! Welcome, ' + first + '! Redirecting…');
  setTimeout(function() { window.location.href = '../Item-management/dashboard.html'; }, 1500);
}

/* ── HELPERS ────────────────────────────────────────────── */
function setError(inputId, state) {
  var input = document.getElementById(inputId);
  if (input) { input.classList.toggle('error', state); input.classList.remove('valid'); }
}
function setValid(inputId, state) {
  var input = document.getElementById(inputId);
  if (input) { input.classList.toggle('valid', state); input.classList.remove('error'); }
}
function clearErrors() {
  document.querySelectorAll('.input-wrap input').forEach(function(inp) {
    inp.classList.remove('error', 'valid');
  });
}
function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (type ? ' ' + type : '');
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
}
