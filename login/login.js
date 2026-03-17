/* ============================================================
   CampusFind — login.js
   ============================================================ */

/* If already properly logged in, skip straight to dashboard */
(function() {
  try {
    var stored = localStorage.getItem('cf_currentUser');
    if (stored) {
      var u = JSON.parse(stored);
      /* Only redirect if the stored user has a real email — not a stale/empty object */
      if (u && u.email && u.name) {
        window.location.href = '../Item-management/dashboard.html';
      } else {
        /* Stale or corrupt data — clear it */
        localStorage.removeItem('cf_currentUser');
      }
    }
  } catch(e) {
    localStorage.removeItem('cf_currentUser');
  }
})();

/* Allow Enter key to submit */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') handleLogin();
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

/* ── LOGIN ─────────────────────────────────────────────── */
function handleLogin() {
  var email = document.getElementById('loginEmail').value.trim().toLowerCase();
  var pass  = document.getElementById('loginPass').value;

  clearErrors();

  var hasError = false;
  if (!email) { setError('loginEmail', true); showToast('⚠️ Please enter your email.', 'err'); hasError = true; }
  if (!pass)  { setError('loginPass',  true); if (!hasError) showToast('⚠️ Please enter your password.', 'err'); hasError = true; }
  if (hasError) return;

  var users = JSON.parse(localStorage.getItem('cf_users') || '[]');
  var user  = users.find(function(u) { return u.email === email && u.pass === pass; });

  if (!user) {
    setError('loginEmail', true);
    setError('loginPass',  true);
    showToast('❌ Incorrect email or password.', 'err');
    return;
  }

  /* Save session */
  localStorage.setItem('cf_currentUser', JSON.stringify(user));

  /* Remember me — keep key for 7 days (cosmetic; localStorage is always persistent) */
  if (document.getElementById('rememberMe').checked) {
    localStorage.setItem('cf_remember', '1');
  }

  showToast('👋 Welcome back, ' + user.first + '! Redirecting…');
  setTimeout(function() { window.location.href = '../Item-management/dashboard.html'; }, 1400);
}

/* ── HELPERS ────────────────────────────────────────────── */
function setError(inputId, state) {
  var input = document.getElementById(inputId);
  if (input) input.classList.toggle('error', state);
}

function clearErrors() {
  document.querySelectorAll('.input-wrap input').forEach(function(inp) {
    inp.classList.remove('error');
  });
}

function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (type ? ' ' + type : '');
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
}