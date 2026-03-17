/* ============================================================
   CampusFind — dashboard.js  (Light theme + Claim system)
   ============================================================ */

/* ── AUTH GUARD ── */
var currentUser = null;
(function() {
  try {
    var stored = localStorage.getItem('cf_currentUser');
    if (stored) {
      var u = JSON.parse(stored);
      if (u && u.email && u.name) { currentUser = u; }
      else { localStorage.removeItem('cf_currentUser'); }
    }
  } catch(e) { localStorage.removeItem('cf_currentUser'); }
  if (!currentUser) window.location.href = '../index.html';
})();

/* ── STATE ── */
var selectedType  = 'lost';
var selectedImage = '';
var activeFilter  = 'all';
var myFilter      = 'all';
var claimsTab     = 'sent';

/* ── PROFILE ── */
function loadUserProfile() {
  var ini = currentUser.first ? currentUser.first[0].toUpperCase() : (currentUser.name[0]||'?').toUpperCase();
  document.getElementById('userAvatar').textContent = ini;
  document.getElementById('userName').textContent   = currentUser.name;
  document.getElementById('userRoll').textContent   = currentUser.roll || '';
  document.getElementById('sideAvatar').textContent = ini;
  document.getElementById('sideName').textContent   = currentUser.name;
  document.getElementById('sideDept').textContent   = currentUser.dept || '';
  document.getElementById('sideRoll').textContent   = currentUser.roll || '';
}

/* ── SECTION SWITCHER ── */
function showSection(name) {
  document.querySelectorAll('.content-section').forEach(function(s) { s.classList.remove('active-section'); });
  document.getElementById('section-' + name).classList.add('active-section');
  var tabs   = document.querySelectorAll('.nav-tab');
  var sbBtns = document.querySelectorAll('.sidebar-btn');
  var map    = { browse:0, myitems:1, claims:2, register:3 };
  var i      = map[name] !== undefined ? map[name] : 0;
  tabs.forEach(function(t)   { t.classList.remove('active'); });
  sbBtns.forEach(function(b) { b.classList.remove('active'); });
  if (tabs[i])   tabs[i].classList.add('active');
  if (sbBtns[i]) sbBtns[i].classList.add('active');
  if (name === 'myitems')  renderMyItems();
  if (name === 'browse')   renderAll();
  if (name === 'claims')   renderClaims();
}

function logout() {
  localStorage.removeItem('cf_currentUser');
  window.location.href = '../index.html';
}

/* ── IMAGE UPLOAD ── */
function previewImage(input) {
  if (!input.files || !input.files[0]) return;
  if (input.files[0].size > 5*1024*1024) { showToast('⚠️ Image too large. Max 5 MB.','err'); return; }
  var reader = new FileReader();
  reader.onload = function(e) {
    selectedImage = e.target.result;
    var prev = document.getElementById('imagePreview');
    prev.src = selectedImage;
    prev.classList.remove('hidden');
    document.getElementById('uploadPlaceholder').classList.add('hidden');
    document.getElementById('removeImgBtn').classList.remove('hidden');
  };
  reader.readAsDataURL(input.files[0]);
}
function removeImage() {
  selectedImage = '';
  document.getElementById('itemImage').value = '';
  document.getElementById('imagePreview').classList.add('hidden');
  document.getElementById('uploadPlaceholder').classList.remove('hidden');
  document.getElementById('removeImgBtn').classList.add('hidden');
}

/* ── TYPE SELECTOR ── */
function selectType(type) {
  selectedType = type;
  document.getElementById('type-lost').classList.remove('active');
  document.getElementById('type-found').classList.remove('active');
  document.getElementById('type-' + type).classList.add('active');
  document.getElementById('dateLabelText').innerHTML =
    (type === 'lost' ? 'Date Lost' : 'Date Found') + ' <span class="req">*</span>';
}

/* ── SUBMIT REPORT ── */
function submitReport() {
  var name     = document.getElementById('itemName').value.trim();
  var category = document.getElementById('itemCategory').value;
  var date     = document.getElementById('itemDate').value;
  var location = document.getElementById('itemLocation').value;
  var desc     = document.getElementById('itemDesc').value.trim();
  var secret   = document.getElementById('itemSecret').value.trim();

  if (!name)     { showToast('⚠️ Please enter item name.','err');      return; }
  if (!category) { showToast('⚠️ Please select a category.','err');    return; }
  if (!date)     { showToast('⚠️ Please select a date.','err');        return; }
  if (!location) { showToast('⚠️ Please select a location.','err');    return; }
  if (!secret)   { showToast('⚠️ Please fill the Secret Detail field.','err'); return; }

  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var newItem = {
    id:           Date.now(),
    type:         selectedType,
    name:         name,
    category:     category,
    date:         date,
    location:     location,
    publicDesc:   desc,
    secretDetail: secret,          // 🔒 hidden from public browse
    image:        selectedImage,
    ownerEmail:   currentUser.email,
    ownerName:    currentUser.name,
    ownerRoll:    currentUser.roll  || '',
    ownerDept:    currentUser.dept  || '',
    ownerContact: currentUser.email, // 🔒 hidden until claim approved
    status:       'pending',
    submittedAt:  new Date().toISOString()
  };
  items.push(newItem);
  localStorage.setItem('cf_items', JSON.stringify(items));
  updateStats();
  clearForm();
  showToast('✅ Report submitted! Awaiting admin approval.','success');
  showSection('myitems');
}

function clearForm() {
  document.getElementById('itemName').value     = '';
  document.getElementById('itemCategory').value = '';
  document.getElementById('itemDesc').value     = '';
  document.getElementById('itemSecret').value   = '';
  document.getElementById('itemLocation').value = '';
  setDefaultDate();
  removeImage();
  selectType('lost');
}

/* ── RENDER BROWSE ── */
function renderAll() {
  var items    = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var search   = document.getElementById('searchInput').value.toLowerCase();
  var cat      = document.getElementById('catFilter').value;
  var location = document.getElementById('locationFilter').value;

  var filtered = items.filter(function(item) {
    // ── KEY FIX: skip own items completely from browse ──
    if (item.ownerEmail === currentUser.email) return false;
    var okA = item.status === 'approved';
    var okT = activeFilter === 'all' || item.type === activeFilter;
    var okS = !search   || item.name.toLowerCase().includes(search) || (item.location||'').toLowerCase().includes(search);
    var okC = !cat      || item.category === cat;
    var okL = !location || item.location === location;
    return okA && okT && okS && okC && okL;
  });

  // Hero stats
  var allApproved = items.filter(function(i){ return i.status==='approved'; });
  document.getElementById('heroLostCount').textContent    = allApproved.filter(function(i){return i.type==='lost';}).length;
  document.getElementById('heroFoundCount').textContent   = allApproved.filter(function(i){return i.type==='found';}).length;
  document.getElementById('heroClaimedCount').textContent = items.filter(function(i){return i.status==='claimed';}).length;

  var container = document.getElementById('browseGrid');
  var emptyEl   = document.getElementById('browseEmpty');
  container.innerHTML = '';
  if (filtered.length === 0) { emptyEl.classList.remove('hidden'); return; }
  emptyEl.classList.add('hidden');

  filtered.forEach(function(item, i) {
    var dateStr = fmtDate(item.date);
    var imgHTML = item.image
      ? '<img class="card-image" src="' + item.image + '" alt=""/>'
      : '<div class="card-image-placeholder">' + getCatEmoji(item.category) + '</div>';

    // Check if user already sent a claim on this item
    var claims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
    var alreadyClaimed = claims.some(function(c) {
      return c.foundId === item.id && c.claimantEmail === currentUser.email;
    });

    var claimBtn = item.type === 'found'
      ? (alreadyClaimed
          ? '<span style="font-size:.75rem;color:var(--muted);font-weight:500">✅ Claim Sent</span>'
          : '<span class="card-link" onclick="openClaimModal(' + item.id + ')">🤝 Claim This →</span>')
      : '<span class="card-link" onclick="openModal(' + item.id + ')">View Details →</span>';

    var card = document.createElement('div');
    card.className = 'item-card';
    card.style.animationDelay = (i * 0.04) + 's';
    card.innerHTML = imgHTML +
      '<div class="card-stripe ' + item.type + '"></div>' +
      '<div class="card-body">' +
        '<div class="card-top"><span class="card-cat">' + esc(item.category) + '</span><span class="card-date">' + dateStr + '</span></div>' +
        '<div class="card-name">' + esc(item.name) + '</div>' +
        '<div class="card-loc">📍 ' + esc(item.location) + '</div>' +
        '<div class="card-footer">' +
          '<span class="status-badge ' + item.type + '">' + (item.type==='lost'?'🔴 Lost':'🟢 Found') + '</span>' +
          claimBtn +
        '</div>' +
      '</div>';
    // Click card body to open detail (not claim btn)
    card.querySelector('.card-body').addEventListener('click', function(e) {
      if (e.target.classList.contains('card-link')) return;
      openModal(item.id);
    });
    container.appendChild(card);
  });
}

function setFilter(type, btn) {
  activeFilter = type;
  document.querySelectorAll('#section-browse .filter-tab').forEach(function(t) { t.classList.remove('active'); });
  btn.classList.add('active');
  renderAll();
}

/* ── BROWSE DETAIL MODAL ── */
function openModal(id) {
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var item  = items.find(function(i) { return i.id === id; });
  if (!item) return;
  var imgHTML = item.image
    ? '<img class="modal-img" src="' + item.image + '" alt=""/>'
    : '<div class="modal-img-placeholder">' + getCatEmoji(item.category) + '</div>';

  // Check existing claim
  var claims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
  var myClaim = claims.find(function(c) { return c.foundId === id && c.claimantEmail === currentUser.email; });

  var actionHTML = '';
  if (item.type === 'found' && item.ownerEmail !== currentUser.email) {
    if (myClaim) {
      actionHTML = '<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:.85rem;text-align:center;font-size:.86rem;color:#166534;font-weight:600">✅ You have already submitted a claim for this item. Awaiting admin review.</div>';
    } else {
      actionHTML = '<button class="btn-primary" style="width:100%;padding:.85rem;border-radius:12px" onclick="closeModal();openClaimModal(' + id + ')">🤝 Submit a Claim</button>';
    }
  }

  document.getElementById('modalContent').innerHTML = imgHTML +
    '<div class="modal-body">' +
      '<div class="detail-type ' + item.type + '">' + (item.type==='lost'?'🔴 Lost Item':'🟢 Found Item') + '</div>' +
      '<div class="detail-name">' + esc(item.name) + '</div>' +
      '<div class="detail-rows">' +
        dRow('📦','Category', item.category) +
        dRow('📅','Date', fmtDateLong(item.date)) +
        dRow('📍','Location', item.location) +
        (item.publicDesc ? dRow('📝','Description', item.publicDesc) : '') +
      '</div>' +
      actionHTML +
    '</div>';
  document.getElementById('detailModal').classList.add('show');
}

function closeModal() { document.getElementById('detailModal').classList.remove('show'); }
document.getElementById('detailModal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });

/* ── CLAIM MODAL ── */
function openClaimModal(foundId) {
  // Prevent claiming own item
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var item  = items.find(function(i) { return i.id === foundId; });
  if (!item) return;
  if (item.ownerEmail === currentUser.email) {
    showToast('❌ You cannot claim your own report.','err'); return;
  }
  document.getElementById('claimFoundId').value = foundId;
  document.getElementById('claimA1').value = '';
  document.getElementById('claimA2').value = '';
  document.getElementById('claimA3').value = '';
  document.getElementById('claimModal').classList.add('show');
}

function closeClaimModal() { document.getElementById('claimModal').classList.remove('show'); }
document.getElementById('claimModal').addEventListener('click', function(e) { if(e.target===this) closeClaimModal(); });

function submitClaim() {
  var foundId = parseInt(document.getElementById('claimFoundId').value);
  var a1      = document.getElementById('claimA1').value.trim();
  var a2      = document.getElementById('claimA2').value.trim();
  var a3      = document.getElementById('claimA3').value.trim();

  if (!a1 || !a2 || !a3) { showToast('⚠️ Please answer all 3 questions.','err'); return; }

  // Prevent duplicate claim
  var claims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
  var dup = claims.some(function(c) { return c.foundId === foundId && c.claimantEmail === currentUser.email; });
  if (dup) { showToast('⚠️ You already submitted a claim for this item.','warn'); closeClaimModal(); return; }

  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var foundItem = items.find(function(i) { return i.id === foundId; });

  var claim = {
    id:             Date.now(),
    foundId:        foundId,
    foundItemName:  foundItem ? foundItem.name : '–',
    reporterEmail:  foundItem ? foundItem.ownerEmail : '',
    claimantEmail:  currentUser.email,
    claimantName:   currentUser.name,
    claimantRoll:   currentUser.roll  || '',
    claimantDept:   currentUser.dept  || '',
    answer1:        a1,
    answer2:        a2,
    answer3:        a3,
    status:         'pending',   // pending | approved | rejected
    contactRevealed: false,      // true only after admin approves
    claimDate:      new Date().toISOString()
  };
  claims.push(claim);
  localStorage.setItem('cf_claims', JSON.stringify(claims));
  closeClaimModal();
  updateStats();
  showToast('✅ Claim submitted! Admin will review and verify.','success');
  renderAll();
}

/* ── CLAIMS SECTION ── */
function setClaimsTab(tab, btn) {
  claimsTab = tab;
  document.querySelectorAll('.claims-tab').forEach(function(t) { t.classList.remove('active'); });
  btn.classList.add('active');
  document.getElementById('claims-sent-panel').style.display     = tab === 'sent'     ? 'block' : 'none';
  document.getElementById('claims-received-panel').style.display = tab === 'received' ? 'block' : 'none';
  renderClaims();
}

function renderClaims() {
  var claims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
  var items  = JSON.parse(localStorage.getItem('cf_items')  || '[]');

  // SENT claims (by me)
  var sent = claims.filter(function(c) { return c.claimantEmail === currentUser.email; });
  renderClaimList(sent, 'sentClaimsList', 'sentEmpty', items, 'sent');

  // RECEIVED claims (on my found items)
  var received = claims.filter(function(c) { return c.reporterEmail === currentUser.email; });
  renderClaimList(received, 'receivedClaimsList', 'receivedEmpty', items, 'received');

  // Badges
  var pendingReceived = received.filter(function(c){ return c.status === 'pending'; }).length;
  var totalClaims     = sent.length + received.length;
  var badge = document.getElementById('claimsBadge');
  var rbadge= document.getElementById('receivedBadge');
  var mybadge=document.getElementById('myClaimsBadge');
  badge.textContent  = totalClaims;
  badge.style.display= totalClaims  > 0 ? 'inline' : 'none';
  rbadge.textContent = pendingReceived;
  rbadge.style.display = pendingReceived > 0 ? 'inline' : 'none';
  mybadge.textContent = totalClaims;

  // Incoming banner in My Items
  var incomingBanner = document.getElementById('claimsIncomingBanner');
  if (incomingBanner) incomingBanner.style.display = pendingReceived > 0 ? 'flex' : 'none';
}

function renderClaimList(list, listId, emptyId, items, mode) {
  var container = document.getElementById(listId);
  var emptyEl   = document.getElementById(emptyId);
  container.innerHTML = '';
  if (list.length === 0) { emptyEl.classList.remove('hidden'); return; }
  emptyEl.classList.add('hidden');

  list.sort(function(a,b){ return b.id - a.id; });

  list.forEach(function(claim) {
    var foundItem = items.find(function(i){ return i.id === claim.foundId; }) || {};
    var card = document.createElement('div');
    card.className = 'claim-card status-' + claim.status;

    // Status badge
    var statusLabel = claim.status === 'approved' ? '✅ Approved'
                    : claim.status === 'rejected' ? '❌ Rejected'
                    : '⏳ Pending Review';

    // Contact block — only shown if approved
    var contactBlock = '';
    if (claim.status === 'approved' && mode === 'sent') {
      contactBlock =
        '<div class="claim-contact-revealed">' +
          '<div class="contact-icon">🔓</div>' +
          '<div>' +
            '<div class="claim-contact-label">Contact Revealed</div>' +
            '<div class="claim-contact-value">' + esc(foundItem.ownerContact || foundItem.ownerEmail || '–') + '</div>' +
            '<div class="claim-contact-meta">' + esc(foundItem.ownerName||'') + ' · ' + esc(foundItem.ownerRoll||'') + ' · ' + esc(foundItem.ownerDept||'') + '</div>' +
          '</div>' +
        '</div>';
    } else if (claim.status === 'approved' && mode === 'received') {
      contactBlock =
        '<div class="claim-contact-revealed">' +
          '<div class="contact-icon">🔓</div>' +
          '<div>' +
            '<div class="claim-contact-label">Claimant Details</div>' +
            '<div class="claim-contact-value">' + esc(claim.claimantEmail) + '</div>' +
            '<div class="claim-contact-meta">' + esc(claim.claimantName) + ' · ' + esc(claim.claimantRoll) + ' · ' + esc(claim.claimantDept) + '</div>' +
          '</div>' +
        '</div>';
    } else if (claim.status === 'pending') {
      contactBlock =
        '<div class="claim-locked">' +
          '<span style="font-size:1.3rem">🔒</span>' +
          '<span>Contact details hidden — revealed only after admin approves this claim.</span>' +
        '</div>';
    } else if (claim.status === 'rejected') {
      contactBlock =
        '<div class="claim-locked" style="border-color:#FECACA;background:#FEF2F2;">' +
          '<span style="font-size:1.3rem">❌</span>' +
          '<span style="color:#991B1B">Claim was rejected. Contact details remain hidden.</span>' +
        '</div>';
    }

    var claimDateStr = fmtDate(claim.claimDate);
    card.innerHTML =
      '<div class="claim-card-top">' +
        '<div>' +
          '<div class="claim-card-title">' + esc(claim.foundItemName) + '</div>' +
          '<div class="claim-card-sub">' +
            (mode === 'sent' ? 'Your claim · ' : 'Claim by ' + esc(claim.claimantName) + ' · ') +
            claimDateStr +
          '</div>' +
        '</div>' +
        '<span class="claim-status-badge ' + claim.status + '">' + statusLabel + '</span>' +
      '</div>' +
      '<div class="claim-answers">' +
        '<div class="claim-answer-row"><span class="claim-q">Q1</span><span class="claim-a">' + esc(claim.answer1) + '</span></div>' +
        '<div class="claim-answer-row"><span class="claim-q">Q2</span><span class="claim-a">' + esc(claim.answer2) + '</span></div>' +
        '<div class="claim-answer-row"><span class="claim-q">Q3</span><span class="claim-a">' + esc(claim.answer3) + '</span></div>' +
      '</div>' +
      contactBlock;

    container.appendChild(card);
  });
}

/* ── MY ITEMS ── */
function setMyFilter(f, btn) {
  myFilter = f;
  document.querySelectorAll('#section-myitems .filter-tab').forEach(function(t) { t.classList.remove('active'); });
  btn.classList.add('active');
  renderMyItems();
}

function renderMyItems() {
  var items  = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var search = document.getElementById('mySearch').value.toLowerCase();
  var mine   = items.filter(function(i) { return i.ownerEmail === currentUser.email; });

  var total    = mine.length;
  var pending  = mine.filter(function(i){ return i.status==='pending'; }).length;
  var approved = mine.filter(function(i){ return i.status==='approved'; }).length;
  var lost     = mine.filter(function(i){ return i.type==='lost'; }).length;
  var found    = mine.filter(function(i){ return i.type==='found'; }).length;

  setNum('sum-total',   total);
  setNum('sum-pending', pending);
  setNum('sum-live',    approved);
  setNum('sum-lost',    lost);
  setNum('sum-found',   found);

  document.getElementById('myItemCountBadge').textContent = total;
  document.getElementById('pendingBanner').style.display = pending > 0 ? 'flex' : 'none';

  // Incoming claims banner
  var claims   = JSON.parse(localStorage.getItem('cf_claims') || '[]');
  var incoming = claims.filter(function(c){ return c.reporterEmail === currentUser.email && c.status === 'pending'; }).length;
  document.getElementById('claimsIncomingBanner').style.display = incoming > 0 ? 'flex' : 'none';

  var list = mine.filter(function(it) {
    var okF = myFilter==='all'
      || (myFilter==='pending'  && it.status==='pending')
      || (myFilter==='approved' && it.status==='approved')
      || (myFilter==='lost'     && it.type==='lost')
      || (myFilter==='found'    && it.type==='found');
    var okS = !search || it.name.toLowerCase().includes(search) || (it.location||'').toLowerCase().includes(search);
    return okF && okS;
  });

  var container = document.getElementById('myItemsList');
  var emptyEl   = document.getElementById('myEmpty');
  container.innerHTML = '';
  if (list.length === 0) { emptyEl.classList.remove('hidden'); return; }
  emptyEl.classList.add('hidden');
  list.sort(function(a,b){ return b.id - a.id; });

  list.forEach(function(item, i) {
    var dateStr  = fmtDate(item.date);
    var subDate  = item.submittedAt ? fmtDate(item.submittedAt) : '–';
    var thumbHTML = item.image
      ? '<img class="mir-thumb" src="' + item.image + '" alt=""/>'
      : '<div class="mir-thumb-ph">' + getCatEmoji(item.category) + '</div>';
    var statusHTML = item.status === 'approved'
      ? '<span class="mir-status-badge approved">✅ Live</span>'
      : '<span class="mir-status-badge pending">⏳ Pending</span>';

    // Claim requests count on found items
    var claimsBadgeHTML = '';
    if (item.type === 'found') {
      var myClaims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
      var incomingCount = myClaims.filter(function(c){ return c.foundId === item.id && c.status==='pending'; }).length;
      if (incomingCount > 0) {
        claimsBadgeHTML = '<span style="background:#EFF6FF;color:var(--accent);border:1px solid #BFDBFE;border-radius:999px;font-size:.68rem;font-weight:700;padding:.1rem .45rem;margin-left:.4rem">' + incomingCount + ' claim' + (incomingCount>1?'s':'') + '</span>';
      }
    }

    var row = document.createElement('div');
    row.className = 'my-item-row status-' + (item.status || 'pending');
    row.style.animationDelay = (i * 0.04) + 's';
    row.innerHTML =
      thumbHTML +
      '<div class="mir-info">' +
        '<div class="mir-top">' +
          '<span class="mir-name">' + esc(item.name) + '</span>' +
          '<span class="mir-type ' + item.type + '">' + (item.type==='lost'?'🔴 Lost':'🟢 Found') + '</span>' +
          claimsBadgeHTML +
        '</div>' +
        '<div class="mir-meta">' +
          '<span>📦 ' + esc(item.category) + '</span>' +
          '<span>📍 ' + esc(item.location) + '</span>' +
          '<span>📅 ' + dateStr + '</span>' +
          '<span style="opacity:.7">Submitted ' + subDate + '</span>' +
        '</div>' +
      '</div>' +
      '<div class="mir-status">' + statusHTML + '</div>' +
      '<div class="mir-actions">' +
        '<button class="mir-btn mir-btn-view" onclick="openMyItemDetail(' + item.id + ')">👁 View</button>' +
        '<button class="mir-btn mir-btn-edit" onclick="openEditModal(' + item.id + ')">✏️ Edit</button>' +
        '<button class="mir-btn mir-btn-del"  onclick="askDelete(' + item.id + ')">🗑</button>' +
      '</div>';
    container.appendChild(row);
  });
}

/* ── MY ITEM DETAIL ── */
function openMyItemDetail(id) {
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var item  = items.find(function(i){ return i.id === id; });
  if (!item) return;
  var imgHTML = item.image
    ? '<img class="modal-img" src="' + item.image + '" alt=""/>'
    : '<div class="modal-img-placeholder">' + getCatEmoji(item.category) + '</div>';
  var statusBlock = item.status === 'approved'
    ? '<div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#166534;display:flex;gap:.5rem;align-items:center;margin-bottom:1rem">✅ <strong>Live</strong> — visible to all students in Browse.</div>'
    : '<div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#92400E;display:flex;gap:.5rem;align-items:center;margin-bottom:1rem">⏳ <strong>Pending admin review</strong> — not visible in Browse yet.</div>';

  document.getElementById('modalContent').innerHTML = imgHTML +
    '<div class="modal-body">' +
      '<div class="detail-type ' + item.type + '">' + (item.type==='lost'?'🔴 Lost Item':'🟢 Found Item') + '</div>' +
      '<div class="detail-name">' + esc(item.name) + '</div>' +
      statusBlock +
      '<div class="detail-rows">' +
        dRow('📦','Category', item.category) +
        dRow('📅','Date', fmtDateLong(item.date)) +
        dRow('📍','Location', item.location) +
        (item.publicDesc ? dRow('📝','Public Description', item.publicDesc) : '') +
        dRow('🔒','Secret Detail', item.secretDetail || '–') +
      '</div>' +
      '<div style="display:flex;gap:.6rem;flex-wrap:wrap;">' +
        '<button class="mir-btn mir-btn-edit" style="flex:1;padding:.7rem;border-radius:10px" onclick="openEditModal(' + id + ');closeModal()">✏️ Edit</button>' +
        '<button class="mir-btn mir-btn-del"  style="flex:1;padding:.7rem;border-radius:10px" onclick="askDelete(' + id + ');closeModal()">🗑 Delete</button>' +
      '</div>' +
    '</div>';
  document.getElementById('detailModal').classList.add('show');
}

/* ── EDIT ── */
function openEditModal(id) {
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var item  = items.find(function(i){ return i.id === id; });
  if (!item) return;
  document.getElementById('editId').value       = id;
  document.getElementById('editName').value     = item.name;
  document.getElementById('editCategory').value = item.category;
  document.getElementById('editDate').value     = item.date;
  document.getElementById('editLocation').value = item.location;
  document.getElementById('editDesc').value     = item.publicDesc || item.desc || '';
  document.getElementById('editModal').classList.add('show');
}
function saveEdit() {
  var id       = parseInt(document.getElementById('editId').value);
  var name     = document.getElementById('editName').value.trim();
  var category = document.getElementById('editCategory').value;
  var date     = document.getElementById('editDate').value;
  var location = document.getElementById('editLocation').value;
  var desc     = document.getElementById('editDesc').value.trim();
  if (!name) { showToast('⚠️ Item name required.','err'); return; }
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var idx   = items.findIndex(function(i){ return i.id === id; });
  if (idx < 0) return;
  items[idx].name       = name;
  items[idx].category   = category;
  items[idx].date       = date;
  items[idx].location   = location;
  items[idx].publicDesc = desc;
  items[idx].status     = 'pending';
  items[idx].editedAt   = new Date().toISOString();
  localStorage.setItem('cf_items', JSON.stringify(items));
  closeEditModal();
  updateStats();
  renderMyItems();
  showToast('✏️ Report updated — awaiting re-approval.','warn');
}
function closeEditModal() { document.getElementById('editModal').classList.remove('show'); }
document.getElementById('editModal').addEventListener('click', function(e){ if(e.target===this) closeEditModal(); });

/* ── DELETE ── */
var pendingDeleteId = null;
function askDelete(id) {
  pendingDeleteId = id;
  document.getElementById('deleteModal').classList.add('show');
  document.getElementById('deleteYes').onclick = function(){ doDelete(); };
}
function doDelete() {
  var items = JSON.parse(localStorage.getItem('cf_items') || '[]');
  localStorage.setItem('cf_items', JSON.stringify(items.filter(function(i){ return i.id !== pendingDeleteId; })));
  pendingDeleteId = null;
  closeDeleteModal();
  updateStats();
  renderMyItems();
  showToast('🗑 Report deleted.','warn');
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); }
document.getElementById('deleteModal').addEventListener('click', function(e){ if(e.target===this) closeDeleteModal(); });

/* ── STATS ── */
function updateStats() {
  var items  = JSON.parse(localStorage.getItem('cf_items') || '[]');
  var claims = JSON.parse(localStorage.getItem('cf_claims') || '[]');
  var mine   = items.filter(function(i){ return i.ownerEmail === currentUser.email; });
  document.getElementById('myLostCount').textContent     = mine.filter(function(i){ return i.type==='lost'; }).length;
  document.getElementById('myFoundCount').textContent    = mine.filter(function(i){ return i.type==='found'; }).length;
  document.getElementById('myPendingCount').textContent  = mine.filter(function(i){ return i.status==='pending'; }).length;
  document.getElementById('myApprovedCount').textContent = mine.filter(function(i){ return i.status==='approved'; }).length;
  document.getElementById('allLostCount').textContent    = items.filter(function(i){ return i.type==='lost'; }).length;
  document.getElementById('allFoundCount').textContent   = items.filter(function(i){ return i.type==='found'; }).length;
  document.getElementById('myItemCountBadge').textContent= mine.length;

  var myClaims = claims.filter(function(c){ return c.claimantEmail === currentUser.email || c.reporterEmail === currentUser.email; });
  var claimBadge = document.getElementById('myClaimsBadge');
  if (claimBadge) claimBadge.textContent = myClaims.length;
}

/* ── HELPERS ── */
function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function dRow(icon, label, value) {
  return '<div class="detail-row"><div class="detail-icon">' + icon + '</div>' +
    '<div><div class="detail-lbl">' + label + '</div><div class="detail-val">' + esc(value) + '</div></div></div>';
}
function setNum(id, val) {
  var el = document.getElementById(id);
  if (!el) return;
  var child = el.querySelector('.sum-n');
  if (child) child.textContent = val;
}
function fmtDate(d) {
  if (!d) return '–';
  return new Date(d).toLocaleDateString('en-IN', { month:'short', day:'numeric', year:'numeric' });
}
function fmtDateLong(d) {
  if (!d) return '–';
  return new Date(d).toLocaleDateString('en-IN', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
function getCatEmoji(cat) {
  var m={'Electronics':'📱','Stationery':'✏️','Clothing':'👕','Accessories':'💍',
         'ID / Documents':'🪪','Books':'📚','Keys':'🔑','Bags':'👜','Sports Equipment':'⚽','Other':'📦'};
  return m[cat]||'📦';
}
function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast' + (type ? ' ' + type : '');
  t.classList.add('show');
  setTimeout(function(){ t.classList.remove('show'); }, 3500);
}
function setDefaultDate() {
  var d = new Date();
  var y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0');
  document.getElementById('itemDate').value = y + '-' + m + '-' + day;
}

/* ── BOOT ── */
loadUserProfile();
setDefaultDate();
renderAll();
updateStats();
renderClaims();
