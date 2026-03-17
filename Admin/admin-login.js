/* ============================================================
   CampusFind — admin-login.js
   ============================================================ */

/* If already authenticated this session, skip straight to admin dashboard */
if (sessionStorage.getItem('cf_admin')) {
  window.location.href = 'admin.html';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') doLogin();
});

function toggleEye() {
  var inp = document.getElementById('adminPass');
  var btn = document.querySelector('.eye-btn');
  if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
  else { inp.type = 'password'; btn.textContent = '👁'; }
}

function doLogin() {
  var user = document.getElementById('adminUser').value.trim();
  var pass = document.getElementById('adminPass').value;

  if (!user || !pass) {
    shake('adminUser'); shake('adminPass');
    showToast('⚠️ Please enter username and password.', 'err');
    return;
  }

  if (user === 'admin' && pass === 'admin123') {
    sessionStorage.setItem('cf_admin', '1');
    showToast('🛡️ Access granted. Redirecting…');
    setTimeout(function() { window.location.href = 'admin.html'; }, 1200);
  } else {
    shake('adminUser'); shake('adminPass');
    showToast('❌ Invalid admin credentials.', 'err');
  }
}

function shake(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.add('shake');
  setTimeout(function() { el.classList.remove('shake'); }, 500);
}

function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (type ? ' ' + type : '');
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3000);
}
