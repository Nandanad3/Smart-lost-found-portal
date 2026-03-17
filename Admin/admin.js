/* ============================================================
   CampusFind — admin.js  (rebuilt)

   HOW IT WORKS:
   - Admin logs in via admin-login.html → sessionStorage 'cf_admin'
   - This page guards itself: if no session, redirects to admin-login.html
   - Reads/writes shared localStorage keys:
       cf_users  → array of student accounts
       cf_items  → array of all reports (each has a .status field)
   - Item statuses: "pending" | "approved"
   - Students only see "approved" items in Browse
   - Admin can Approve or Remove any item
   - Admin can delete user accounts (+ their items)
   ============================================================ */

/* ── AUTH GUARD ──────────────────────────────────────────── */
if (!sessionStorage.getItem('cf_admin')) {
  window.location.href = '../Admin/admin-login.html';
}

/* ── STATE ───────────────────────────────────────────────── */
var rFilter = 'pending';
var iFilter = 'all';

/* ── BOOT ────────────────────────────────────────────────── */
(function init() {
  migrateItems();        // give existing items a status if missing
  refreshStats();
  renderOverview();
  populateDepts();
})();

/* ── MIGRATIONS ──────────────────────────────────────────── */
function migrateItems() {
  var items = getItems();
  var dirty = false;
  items.forEach(function(it) {
    if (!it.status) { it.status = 'approved'; dirty = true; }
  });
  if (dirty) saveItems(items);
}

/* ── DATA ────────────────────────────────────────────────── */
function getItems() { return JSON.parse(localStorage.getItem('cf_items') || '[]'); }
function getUsers() { return JSON.parse(localStorage.getItem('cf_users') || '[]'); }
function saveItems(a) { localStorage.setItem('cf_items', JSON.stringify(a)); }
function saveUsers(a) { localStorage.setItem('cf_users', JSON.stringify(a)); }

/* ── SECTION SWITCHER ────────────────────────────────────── */
function showSection(name) {
  var map = { overview:0, reports:1, users:2, items:3 };
  document.querySelectorAll('.section').forEach(function(s) { s.classList.remove('active'); });
  document.getElementById('s-' + name).classList.add('active');

  var tabs = document.querySelectorAll('.nav-tab');
  var sbtns = document.querySelectorAll('.sb-btn');
  var idx = map[name] || 0;
  tabs.forEach(function(t) { t.classList.remove('active'); });
  sbtns.forEach(function(b) { b.classList.remove('active'); });
  if (tabs[idx]) tabs[idx].classList.add('active');
  if (sbtns[idx]) sbtns[idx].classList.add('active');

  if (name === 'reports') renderReports();
  if (name === 'users')   renderUsers();
  if (name === 'items')   renderItems();
  if (name === 'overview') renderOverview();
  refreshStats();
}

/* ── STATS ───────────────────────────────────────────────── */
function refreshStats() {
  var items = getItems();
  var users = getUsers();
  var pend  = items.filter(function(i) { return i.status === 'pending'; }).length;
  var appr  = items.filter(function(i) { return i.status === 'approved'; }).length;
  setText('sbTotal',   items.length);
  setText('sbUsers',   users.length);
  setText('sbPending', pend);
  setText('sbApproved',appr);
  setText('pendingBadge', pend);
}

/* ── OVERVIEW ────────────────────────────────────────────── */
function renderOverview() {
  var items = getItems();
  var users = getUsers();
  var pend  = items.filter(function(i) { return i.status === 'pending';  });
  var appr  = items.filter(function(i) { return i.status === 'approved'; });
  var lost  = items.filter(function(i) { return i.type === 'lost';   });
  var found = items.filter(function(i) { return i.type === 'found';  });

  /* KPI cards */
  var kpis = [
    { ico:'📦', num:items.length,   lbl:'Total Reports',    cls:'c-black'   },
    { ico:'👥', num:users.length,   lbl:'Registered Users', cls:'c-black' },
    { ico:'⏳', num:pend.length,    lbl:'Pending Approval', cls:'c-black' },
    { ico:'✅', num:appr.length,    lbl:'Approved Items',   cls:'c-black'  },
    { ico:'🔴', num:lost.length,    lbl:'Lost Reports',     cls:'c-black'    },
    { ico:'🟢', num:found.length,   lbl:'Found Reports',    cls:'c-black'  },
  ];
  var row = document.getElementById('kpiRow');
  row.innerHTML = '';
  kpis.forEach(function(k) {
    var d = document.createElement('div');
    d.className = 'kpi';
    d.innerHTML = '<div class="kpi-ico">' + k.ico + '</div>' +
      '<div class="kpi-num ' + k.cls + '">' + k.num + '</div>' +
      '<div class="kpi-lbl">' + k.lbl + '</div>';
    row.appendChild(d);
  });

  /* Pending mini list */
  var pList  = document.getElementById('ov-pending');
  var pEmpty = document.getElementById('ov-pending-empty');
  pList.innerHTML = '';
  if (pend.length === 0) { pEmpty.classList.remove('hidden'); }
  else {
    pEmpty.classList.add('hidden');
    pend.slice(0,6).forEach(function(item) {
      var r = document.createElement('div');
      r.className = 'mini-row';
      r.innerHTML =
        '<div class="mini-dot" style="background:' + (item.type==='lost'?'#ef4444':'#22c55e') + '"></div>' +
        '<div class="mini-name">' + esc(item.name) + '</div>' +
        '<div class="mini-meta">' + esc(item.location||'') + '</div>' +
        '<div class="mini-acts">' +
          '<button class="tb tb-app" onclick="approveItem(' + item.id + ',true)">✓</button>' +
          '<button class="tb tb-rem" onclick="askRemove(' + item.id + ')">✕</button>' +
        '</div>';
      pList.appendChild(r);
    });
  }

  /* Recent users */
  var uList  = document.getElementById('ov-users');
  var uEmpty = document.getElementById('ov-users-empty');
  uList.innerHTML = '';
  if (users.length === 0) { uEmpty.classList.remove('hidden'); }
  else {
    uEmpty.classList.add('hidden');
    users.slice(-5).reverse().forEach(function(u) {
      var r = document.createElement('div');
      r.className = 'user-mini';
      var ini = u.first ? u.first[0].toUpperCase() : '?';
      r.innerHTML =
        '<div class="user-mini-av">' + ini + '</div>' +
        '<div><div class="user-mini-name">' + esc(u.name) + '</div>' +
        '<div class="user-mini-email">' + esc(u.email) + '</div></div>';
      uList.appendChild(r);
    });
  }
}

/* ── REPORTS ─────────────────────────────────────────────── */
function setRFilter(f, btn) {
  rFilter = f;
  document.querySelectorAll('#s-reports .f-tab').forEach(function(t) { t.classList.remove('active'); });
  btn.classList.add('active');
  renderReports();
}

function renderReports() {
  var items  = getItems();
  var q      = val('rSearch').toLowerCase();
  var type   = val('rTypeFilter');
  var cat    = val('rCatFilter');

  var list = items.filter(function(it) {
    var okS = rFilter === 'all' || it.status === rFilter;
    var okT = !type || it.type === type;
    var okC = !cat  || it.category === cat;
    var okQ = !q || esc(it.name).toLowerCase().includes(q) ||
              (it.location||'').toLowerCase().includes(q) ||
              (it.ownerName||'').toLowerCase().includes(q);
    return okS && okT && okC && okQ;
  });

  var tbody = document.getElementById('rTbody');
  var empty = document.getElementById('rEmpty');
  var tbl   = document.getElementById('rTable');
  tbody.innerHTML = '';

  if (list.length === 0) {
    empty.classList.remove('hidden'); tbl.style.display = 'none'; return;
  }
  empty.classList.add('hidden'); tbl.style.display = 'table';

  list.forEach(function(it, idx) {
    var ds = new Date(it.date).toLocaleDateString('en-IN',{month:'short',day:'numeric',year:'numeric'});
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td style="color:var(--muted);font-size:.75rem">' + (idx+1) + '</td>' +
      '<td><strong>' + esc(it.name) + '</strong></td>' +
      '<td>' + typeBdg(it.type) + '</td>' +
      '<td>' + esc(it.category||'–') + '</td>' +
      '<td>📍 ' + esc(it.location||'–') + '</td>' +
      '<td>' + esc(it.ownerName||'–') + '<br><span style="font-size:.7rem;color:var(--muted)">' + esc(it.ownerRoll||'') + '</span></td>' +
      '<td style="white-space:nowrap">' + ds + '</td>' +
      '<td>' + statusBdg(it.status) + '</td>' +
      '<td><div style="display:flex;gap:.35rem;flex-wrap:wrap">' +
        '<button class="tb tb-view" onclick="openDetail(' + it.id + ')">👁 View</button>' +
        (it.status !== 'approved' ? '<button class="tb tb-app" onclick="approveItem(' + it.id + ',false)">✅ Approve</button>' : '<span style="font-size:.72rem;color:var(--muted)">✅ Approved</span>') +
        '<button class="tb tb-rem" onclick="askRemove(' + it.id + ')">🗑 Remove</button>' +
      '</div></td>';
    tbody.appendChild(tr);
  });
}

/* ── USERS ───────────────────────────────────────────────── */
function populateDepts() {
  var users = getUsers();
  var depts = [];
  users.forEach(function(u) { if (u.dept && depts.indexOf(u.dept) < 0) depts.push(u.dept); });
  var sel = document.getElementById('uDeptFilter');
  depts.forEach(function(d) {
    var o = document.createElement('option');
    o.value = d; o.textContent = d; sel.appendChild(o);
  });
}

function renderUsers() {
  var users  = getUsers();
  var items  = getItems();
  var q      = val('uSearch').toLowerCase();
  var dept   = val('uDeptFilter');

  var list = users.filter(function(u) {
    var okQ = !q || u.name.toLowerCase().includes(q) ||
              u.email.toLowerCase().includes(q) || (u.roll||'').toLowerCase().includes(q);
    var okD = !dept || u.dept === dept;
    return okQ && okD;
  });

  setText('uCount', list.length + ' user' + (list.length !== 1 ? 's' : '') + ' found');

  var tbody = document.getElementById('uTbody');
  var empty = document.getElementById('uEmpty');
  var tbl   = document.getElementById('uTable');
  tbody.innerHTML = '';

  if (list.length === 0) {
    empty.classList.remove('hidden'); tbl.style.display = 'none'; return;
  }
  empty.classList.add('hidden'); tbl.style.display = 'table';

  list.forEach(function(u) {
    var rc  = items.filter(function(i) { return i.ownerEmail === u.email; }).length;
    var ini = u.first ? u.first[0].toUpperCase() : '?';
    var tr  = document.createElement('tr');
    tr.innerHTML =
      '<td><div style="display:flex;align-items:center;gap:.6rem">' +
        '<div style="width:30px;height:30px;border-radius:8px;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-family:Syne,sans-serif;font-weight:700;font-size:.82rem;flex-shrink:0">' + ini + '</div>' +
        '<strong>' + esc(u.name) + '</strong></div></td>' +
      '<td>' + esc(u.roll||'–') + '</td>' +
      '<td style="color:var(--muted);font-size:.8rem">' + esc(u.email) + '</td>' +
      '<td>' + esc(u.dept||'–') + '</td>' +
      '<td>' + esc(u.joined||'–') + '</td>' +
      '<td><span class="bdg bdg-approved">' + rc + ' report' + (rc!==1?'s':'') + '</span></td>' +
      '<td><div style="display:flex;gap:.35rem">' +
        '<button class="tb tb-view" onclick="viewUserItems(\'' + u.email + '\')">📋 Items</button>' +
        '<button class="tb tb-del"  onclick="askDeleteUser(\'' + u.email + '\')">🗑 Remove</button>' +
      '</div></td>';
    tbody.appendChild(tr);
  });
}

/* ── ITEMS GRID ──────────────────────────────────────────── */
function setIFilter(f, btn) {
  iFilter = f;
  document.querySelectorAll('#s-items .f-tab').forEach(function(t) { t.classList.remove('active'); });
  btn.classList.add('active');
  renderItems();
}

function renderItems() {
  var items  = getItems();
  var q      = val('iSearch').toLowerCase();
  var cat    = val('iCatFilter');
  var status = val('iStatusFilter');

  var list = items.filter(function(it) {
    var okT = iFilter === 'all' || it.type === iFilter;
    var okQ = !q || it.name.toLowerCase().includes(q) || (it.location||'').toLowerCase().includes(q);
    var okC = !cat    || it.category === cat;
    var okS = !status || it.status === status;
    return okT && okQ && okC && okS;
  });

  var grid  = document.getElementById('iGrid');
  var empty = document.getElementById('iEmpty');
  grid.innerHTML = '';

  if (list.length === 0) { empty.classList.remove('hidden'); return; }
  empty.classList.add('hidden');

  list.forEach(function(it) {
    var ds = new Date(it.date).toLocaleDateString('en-IN',{month:'short',day:'numeric',year:'numeric'});
    var imgH = it.image
      ? '<img class="icard-img" src="' + it.image + '" alt=""/>'
      : '<div class="icard-ph">' + catEmoji(it.category) + '</div>';
    var c = document.createElement('div');
    c.className = 'icard';
    c.innerHTML = imgH +
      '<div class="icard-stripe ' + it.type + '"></div>' +
      '<div class="icard-body">' +
        '<div class="icard-top"><span class="icard-cat">' + esc(it.category||'') + '</span><span class="icard-date">' + ds + '</span></div>' +
        '<div class="icard-name">' + esc(it.name) + '</div>' +
        '<div class="icard-loc">📍 ' + esc(it.location||'') + '</div>' +
        '<div class="icard-foot">' +
          '<div style="display:flex;flex-direction:column;gap:.25rem">' +
            typeBdg(it.type) + ' <span class="icard-status ' + it.status + '">' + (it.status==='approved'?'✅ Approved':'⏳ Pending') + '</span>' +
          '</div>' +
          '<div class="icard-acts">' +
            '<button class="tb tb-view" onclick="openDetail(' + it.id + ')">👁</button>' +
            (it.status !== 'approved' ? '<button class="tb tb-app" onclick="approveItem(' + it.id + ',false)">✅</button>' : '') +
            '<button class="tb tb-rem" onclick="askRemove(' + it.id + ')">🗑</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    grid.appendChild(c);
  });
}

/* ── APPROVE ─────────────────────────────────────────────── */
function approveItem(id, fromOverview) {
  var items = getItems();
  var it    = items.find(function(i) { return i.id === id; });
  if (!it) return;
  it.status = 'approved';
  saveItems(items);
  toast('✅ "' + it.name + '" approved and now visible to students.');
  refreshStats();
  renderOverview();
  if (!fromOverview) renderReports();
  renderItems();
}

function approveAll() {
  var items = getItems();
  var n = 0;
  items.forEach(function(i) { if (i.status === 'pending') { i.status = 'approved'; n++; } });
  saveItems(items);
  refreshStats();
  toast('✅ ' + n + ' report' + (n!==1?'s':'') + ' approved.');
  renderReports(); renderOverview();
}

/* ── REMOVE ──────────────────────────────────────────────── */
function askRemove(id) {
  var items = getItems();
  var it    = items.find(function(i) { return i.id === id; });
  if (!it) return;
  confirm2('🗑️', 'Remove this report?',
    '"' + it.name + '" will be permanently deleted.',
    function() { doRemove(id); });
}

function doRemove(id) {
  var items = getItems().filter(function(i) { return i.id !== id; });
  saveItems(items);
  refreshStats(); renderReports(); renderItems(); renderOverview();
  closeDetailModal();
  toast('🗑️ Report removed.', 'warn');
}

function removeAllPending() {
  var items = getItems();
  var pend  = items.filter(function(i) { return i.status === 'pending'; });
  if (!pend.length) { toast('No pending reports to remove.'); return; }
  confirm2('⚠️', 'Remove all ' + pend.length + ' pending reports?',
    'This will permanently delete all unreviewed reports.',
    function() {
      saveItems(items.filter(function(i) { return i.status !== 'pending'; }));
      refreshStats(); renderReports(); renderOverview();
      toast('🗑️ All pending reports removed.', 'warn');
    });
}

/* ── DELETE USER ─────────────────────────────────────────── */
function askDeleteUser(email) {
  var users = getUsers();
  var u     = users.find(function(x) { return x.email === email; });
  if (!u) return;
  confirm2('⚠️', 'Remove user account?',
    'Delete ' + u.name + '\'s account and all their reports. Cannot be undone.',
    function() {
      saveUsers(getUsers().filter(function(x) { return x.email !== email; }));
      saveItems(getItems().filter(function(i) { return i.ownerEmail !== email; }));
      refreshStats(); renderUsers(); renderOverview();
      toast('🗑️ User and their reports removed.', 'warn');
    });
}

function viewUserItems(email) {
  showSection('items');
  document.getElementById('iSearch').value = email;
  renderItems();
}

/* ── CLEAR ALL ITEMS ─────────────────────────────────────── */
function clearAll() {
  var n = getItems().length;
  if (!n) { toast('No items to clear.'); return; }
  confirm2('⚠️', 'Clear ALL ' + n + ' items?',
    'Every item report will be permanently deleted. Cannot be undone.',
    function() {
      saveItems([]);
      refreshStats(); renderItems(); renderOverview();
      toast('🗑️ All items cleared.', 'warn');
    });
}

/* ── DETAIL MODAL ────────────────────────────────────────── */
function openDetail(id) {
  var it = getItems().find(function(i) { return i.id === id; });
  if (!it) return;
  var ds = new Date(it.date).toLocaleDateString('en-IN',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
  var imgH = it.image
    ? '<img class="d-img" src="' + it.image + '" alt=""/>'
    : '<div class="d-img-ph">' + catEmoji(it.category) + '</div>';
  var appBtn = it.status !== 'approved'
    ? '<button class="d-app" onclick="approveItem(' + id + ',false);closeDetailModal()">✅ Approve Report</button>'
    : '<button style="opacity:.4;cursor:default;flex:1;padding:.72rem;border-radius:10px;background:rgba(34,197,94,.08);color:#4ade80;border:1px solid rgba(34,197,94,.2);font-family:DM Sans,sans-serif" disabled>✅ Already Approved</button>';

  document.getElementById('detailContent').innerHTML = imgH +
    '<div class="d-body">' +
      '<div class="d-type ' + it.type + '">' + (it.type==='lost'?'🔴 Lost Item':'🟢 Found Item') + '</div>' +
      '<div class="d-name">' + esc(it.name) + '</div>' +
      '<div class="d-rows">' +
        dr('📦','Category', it.category||'–') +
        dr('📅','Date', ds) +
        dr('📍','Location', it.location||'–') +
        (it.desc ? dr('📝','Description', it.desc) : '') +
        dr('👤','Reported by', (it.ownerName||'–') + ' · ' + (it.ownerRoll||'–')) +
        dr('📧','Contact', it.contact||'–') +
        dr('🔖','Status', it.status==='approved'?'✅ Approved':'⏳ Pending') +
      '</div>' +
      '<div class="d-acts">' + appBtn +
        '<button class="d-rem" onclick="askRemove(' + id + ')">🗑️ Remove Report</button>' +
      '</div>' +
    '</div>';
  document.getElementById('detailOverlay').classList.add('show');
}

function closeDetailModal() { document.getElementById('detailOverlay').classList.remove('show'); }
document.getElementById('detailOverlay').addEventListener('click', function(e) { if (e.target === this) closeDetailModal(); });

/* ── CONFIRM MODAL ───────────────────────────────────────── */
function confirm2(ico, title, msg, cb) {
  setText('cIcon',  ico);
  setText('cTitle', title);
  setText('cMsg',   msg);
  document.getElementById('cYes').onclick = function() { cb(); closeConfirmModal(); };
  document.getElementById('confirmOverlay').classList.add('show');
}
function closeConfirmModal() { document.getElementById('confirmOverlay').classList.remove('show'); }
document.getElementById('confirmOverlay').addEventListener('click', function(e) { if (e.target === this) closeConfirmModal(); });

/* ── LOGOUT ──────────────────────────────────────────────── */
function doLogout() {
  sessionStorage.removeItem('cf_admin');
  window.location.href = '../Admin/admin-login.html';
}

/* ── INTERCEPT NEW ITEM SUBMISSIONS ─────────────────────── */
/* Override localStorage.setItem so any items saved by students
   without a status get marked "pending" automatically.
   This means new submissions from dashboard.html won't appear
   in student Browse until admin approves them. */
(function() {
  var _set = localStorage.setItem.bind(localStorage);
  localStorage.setItem = function(key, value) {
    if (key === 'cf_items') {
      try {
        var arr = JSON.parse(value);
        arr = arr.map(function(it) {
          if (!it.status) it.status = 'pending';
          return it;
        });
        value = JSON.stringify(arr);
      } catch(e) {}
    }
    _set(key, value);
  };
})();

/* ── HELPERS ─────────────────────────────────────────────── */
function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function val(id) {
  var el = document.getElementById(id);
  return el ? el.value : '';
}
function setText(id, v) {
  var el = document.getElementById(id);
  if (el) el.textContent = v;
}
function typeBdg(type) {
  return type === 'lost'
    ? '<span class="bdg bdg-lost">🔴 Lost</span>'
    : '<span class="bdg bdg-found">🟢 Found</span>';
}
function statusBdg(s) {
  return s === 'approved'
    ? '<span class="bdg bdg-approved">✅ Approved</span>'
    : '<span class="bdg bdg-pending">⏳ Pending</span>';
}
function dr(ico, lbl, val) {
  return '<div class="d-row"><div class="d-ico">' + ico + '</div>' +
    '<div><div class="d-lbl">' + lbl + '</div><div class="d-val">' + esc(val) + '</div></div></div>';
}
function catEmoji(cat) {
  var m={'Electronics':'📱','Stationery':'✏️','Clothing':'👕','Accessories':'💍',
         'ID / Documents':'🪪','Books':'📚','Keys':'🔑','Bags':'👜','Sports Equipment':'⚽','Other':'📦'};
  return m[cat]||'📦';
}
function toast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (type ? ' ' + type : '');
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
}
