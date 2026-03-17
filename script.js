/* CampusFind — script.js (Home Page) */

/* ── NAV SCROLL ── */
window.addEventListener('scroll', function() {
  var nav = document.getElementById('mainNav');
  if (window.scrollY > 40) nav.classList.add('scrolled');
  else nav.classList.remove('scrolled');
});

/* ── MOBILE MENU ── */
function toggleMenu() {
  document.getElementById('navLinks').classList.toggle('open');
}
document.querySelectorAll('.nav-links a').forEach(function(a) {
  a.addEventListener('click', function() {
    document.getElementById('navLinks').classList.remove('open');
  });
});

/* ── FAQ ── */
function toggleFAQ(el) {
  var item = el.closest('.faq-item');
  var isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(function(i) { i.classList.remove('open'); });
  if (!isOpen) item.classList.add('open');
}

/* ── TOAST ── */
function showToast(msg) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
}

/* ── SCROLL REVEAL ── */
var observer = new IntersectionObserver(function(entries) {
  entries.forEach(function(e) {
    if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); }
  });
}, { threshold: 0.1 });
document.querySelectorAll('[data-reveal]').forEach(function(el) { observer.observe(el); });

/* ── ACTIVE NAV LINK ON SCROLL ── */
var sections = document.querySelectorAll('section[id]');
window.addEventListener('scroll', function() {
  var pos = window.scrollY + 100;
  sections.forEach(function(s) {
    var top = s.offsetTop, h = s.offsetHeight;
    var link = document.querySelector('.nav-links a[href="#' + s.id + '"]');
    if (link) { link.style.color = pos >= top && pos < top + h ? 'var(--text)' : ''; }
  });
}, { passive: true });
