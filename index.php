<?php
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CampusFind — Lost & Found Community Board</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/campusfind/assets/css/style.css"/>
</head>
<body>
  <header class="launch-nav">
    <a href="/campusfind/index.php" class="nav-logo">Campus<span>Find</span></a>
    <div class="launch-nav-links">
      <a href="#home">Home</a>
      <a href="#about">About</a>
      <a href="#how">How It Works</a>
      <a href="#features">Features</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Contact</a>
      <a href="/campusfind/auth/login.php" class="btn btn-ghost btn-sm">Log In</a>
      <a href="/campusfind/auth/signup.php" class="btn btn-primary btn-sm">Sign Up</a>
    </div>
  </header>

  <main class="launch-main">
    <section id="home" class="launch-hero">
      <div>
        <div class="page-tag">Official Campus Lost &amp; Found Portal</div>
        <h1>Lost or Found Something on Campus?<span></span></h1>
        <p>CampusFind helps students, faculty, and staff report lost items, publish found items, and connect quickly so belongings get back to the right owner.</p>
        <div class="launch-hero-cta">
          <a href="/campusfind/items/report-lost.php" class="btn btn-primary btn-lg">Report Lost or Found Item</a>
          <a href="/campusfind/pages/notice-board.php" class="btn btn-ghost btn-lg">Browse Lost Items Board</a>
        </div>
      </div>
      <div class="launch-hero-card">
        <div class="launch-hero-icon">🔎</div>
        <h3>Quick Return Workflow</h3>
        <p>One secure campus portal for reporting, matching, and returning items faster.</p>
        <ul>
          <li>Post lost and found notices in seconds</li>
          <li>Connect owners and finders directly</li>
          <li>Confirm handover and close status</li>
        </ul>
      </div>
    </section>

    <section id="about" class="launch-section">
      <h2>About Us</h2>
      <p>CampusFind is a student-first platform built to support both sides of recovery: people who lost items and people who found them. Our public notice board and simple handover flow make returns faster, safer, and more transparent.</p>
      <div class="launch-grid">
        <article class="launch-mini-card">
          <h4>🎯 Mission</h4>
          <p>Help every student return found items and recover lost belongings with community support.</p>
        </article>
        <article class="launch-mini-card">
          <h4>🔒 Safety</h4>
          <p>Share only needed contact details and avoid posting sensitive private info.</p>
        </article>
        <article class="launch-mini-card">
          <h4>⚡ Speed</h4>
          <p>Direct contact actions reduce delay between finding and returning items.</p>
        </article>
      </div>
    </section>

    <section id="how" class="launch-section">
      <h2>How It Works</h2>
      <div class="launch-steps">
        <div><span>1</span><p>Sign up or log in to post and manage lost or found notices.</p></div>
        <div><span>2</span><p>Create a <strong>Lost</strong> or <strong>Found</strong> notice with clear item details and location.</p></div>
        <div><span>3</span><p>Owners and finders connect directly through details that provided</p></div>
        <div><span>4</span><p>After successful handover, update status to close the case.</p></div>
      </div>
    </section>

    <section id="features" class="launch-section">
      <h2>Features</h2>
      <div class="launch-grid">
        <article class="launch-mini-card">
          <h4>Two-Way Notice Board</h4>
          <p>Browse both lost and found notices in one place for faster matching.</p>
        </article>
        <article class="launch-mini-card">
          <h4>Instant Contact Actions</h4>
          <p>Call, WhatsApp, or email directly to coordinate safe return quickly.</p>
        </article>
        <article class="launch-mini-card">
          <h4>Return Status Tracking</h4>
          <p>Track active, matched, and returned items so every case stays organized.</p>
        </article>
      </div>
    </section>

    <section id="faq" class="launch-section">
      <h2>FAQ</h2>
      <div class="launch-grid">
        <article class="launch-mini-card">
          <h4>I found an item. What should I do first?</h4>
          <p>Post a found notice with key details, then check matching lost notices and contact the likely owner.</p>
        </article>
        <article class="launch-mini-card">
          <h4>Can I return an item without creating an account?</h4>
          <p>Yes. You can still browse the board and contact notice owners directly from public listings.</p>
        </article>
        <article class="launch-mini-card">
          <h4>How is a case closed after return?</h4>
          <p>Once owner and finder complete handover, update the notice status to mark the item as returned.</p>
        </article>
      </div>
    </section>

    <section id="contact" class="launch-section launch-contact">
      <h2>Contact</h2>
      <p>Need support or want to suggest improvements for CampusFind?</p>
      <div class="launch-contact-links">
        <a href="mailto:support@campusfind.local" class="btn btn-ghost">✉️ support@campusfind.local</a>
        <a href="tel:+919000000000" class="btn btn-ghost">📞 +91 90000 00000</a>
        <a href="/campusfind/pages/notice-board.php" class="btn btn-primary">Open Notice Board</a>
      </div>
    </section>
  </main>

  <footer class="launch-footer">
    <strong>CampusFind</strong> — Lost &amp; Found Community Board for Students
  </footer>
</body>
</html>
