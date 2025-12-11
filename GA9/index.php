<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
$firstName = $_SESSION['first_name'] ?? '';
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);
$role = $_SESSION['role'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Baytisan — Local crafts from Albay</title>
  <link rel="stylesheet" href="style.css">
  <script defer src="script.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Leave your existing styles unchanged */
    .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.25); align-items: center; justify-content: center; }
    .modal.active { display: flex; }
    .modal-content { background: #fff; border-radius: 16px; max-width: 490px; width: 96vw; margin: 0 auto; padding: 30px 22px 20px 22px; position: relative; box-shadow: 0 16px 48px rgba(30,30,30,0.13);}
    .close { position: absolute; top: 9px; right: 13px; background: none; border: none; font-size: 1.5em; color: #8EB486; cursor: pointer; z-index:2; }
    .auth-tabs { display: flex; gap: 8px; margin-bottom:14px }
    .tab-btn { flex:1; padding:10px; border-radius:10px; border:1px solid #eee; background:#fafafa; cursor:pointer }
    .tab-btn.active { background:var(--primary);color:#fff;border:none }
    .auth-form { display:none }
    .auth-form.active { display:block }
    .auth-msg { margin-top:8px; min-height:22px; color:#e05d5d; }
    /* Green button style to match screenshot */
    .btn-admin-green {
      background: #8EB486 !important;
      color: #fff !important;
      border: none !important;
      border-radius: 13px !important;
      font-weight: 600;
      font-size: 1em;
      padding: 10px 28px;
      margin: 0 6px 0 0;
      box-shadow: 0 2px 13px rgba(120,160,120,0.07);
      transition: background .18s, color .18s, transform .18s;
      display: inline-block;
    }
    .btn-admin-green:hover, .btn-admin-green:focus { background: #6a9c5b; color: #fff; transform: translateY(-2px);}
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container nav">
      <a href="index.php" class="brand">
        <img src="images/logo.png" alt="Baytisan logo" class="logo">
        <span class="brand-text">Baytisan</span>
      </a>
      <nav class="main-nav">
        <a href="index.php" class="active">Home</a>
        <a href="products.php">Shop</a>
        <a href="order_history.php">Orders</a>
        <a href="admin_dashboard.php">Admin</a>
      </nav>
      <div class="nav-actions" id="navActions">
        <?php if ($loggedIn): ?>
          <span class="welcome"><i class="fa fa-user"></i> Welcome, <?= htmlspecialchars($firstName) ?></span>
          <?php if ($role === 'customer'): ?>
            <a href="profile.php" class="btn btn-primary" style="margin-left:8px;">
              <i class="fa fa-user"></i> Profile
            </a>
          <?php elseif ($role === 'admin'): ?>
            <a href="admin_profile.php" class="btn btn-primary" style="margin-left:8px;">
              <i class="fa fa-user-shield"></i> Admin Profile
            </a>
          <?php elseif ($role === 'seller'): ?>
            <a href="seller_dashboard.php" class="btn btn-primary" style="margin-left:8px;">
              <i class="fa fa-store"></i> Seller Dashboard
            </a>
          <?php endif; ?>
          <a href="cart.php" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> Cart (<?= $cartCount ?>)</a>
          <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
          <!-- ONE BUTTON FOR ALL -->
          <button id="authModalBtn" class="btn-admin-green">
            <i class="fa fa-sign-in-alt"></i> Login / Signup / Admin
          </button>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <!-- Unchanged main content -->
    <section class="hero container">
      <div class="hero-inner">
        <div class="hero-text">
          <?php if ($loggedIn): ?>
            <h1><i class="fa fa-hand-sparkles"></i> Welcome back, <?= htmlspecialchars($firstName) ?>!</h1>
            <p>Discover new handmade crafts, check your orders, and support local artisans.</p>
            <a href="products.php" class="btn btn-primary"><i class="fa fa-store"></i> Shop Now</a>
          <?php else: ?>
            <h1><i class="fa fa-spa"></i> Traditional crafts from Albay</h1>
            <p>Handmade abaca products, pots and local pili sweets — directly from the LGUs of Albay.</p>
            <a href="products.php" class="btn btn-primary"><i class="fa fa-store"></i> Shop Now</a>
          <?php endif; ?>
        </div>
        <div class="hero-image">
          <img src="images/bg-main.png" alt="Baytisan hero image">
        </div>
      </div>
    </section>

    <section class="features container">
      <article><h3><i class="fa fa-truck-fast"></i> Free local delivery</h3><p>Selected municipalities</p></article>
      <article><h3><i class="fa fa-lock"></i> Secure payments</h3><p>Encrypted checkout</p></article>
      <article><h3><i class="fa fa-heart"></i> Support local artisans</h3><p>Made in Albay</p></article>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>© <span id="year"></span> Baytisan — Local crafts from Albay</p>
    </div>
  </footer>

  <?php if (!$loggedIn): ?>
  <!-- ONE MODAL, THREE TABS -->
  <div id="modal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
      <button id="closeModal" class="close" aria-label="Close">✕</button>
      <div class="auth-tabs" role="tablist">
        <button class="tab-btn active" data-target="loginForm" role="tab"><i class="fa fa-sign-in-alt"></i> Login / Signup</button>
        <button class="tab-btn" data-target="adminLoginForm" role="tab"><i class="fa fa-user-shield"></i> Admin Login</button>
        <button class="tab-btn" data-target="sellerRegisterForm" role="tab"><i class="fa fa-user-plus"></i> Seller Register</button>
      </div>
      <div class="auth-forms">
        <!-- Customer Login -->
        <form id="loginForm" class="auth-form active" method="post" autocomplete="off">
          <label for="login-email"><i class="fa fa-envelope"></i> Email</label>
          <input id="login-email" name="email" type="email" required>
          <label for="login-password"><i class="fa fa-key"></i> Password</label>
          <input id="login-password" name="password" type="password" required>
          <button id="loginSubmit" type="button" class="btn btn-primary"><i class="fa fa-sign-in-alt"></i> Login</button>
          <div id="loginMsg" class="auth-msg"></div>
          <hr>
          <label for="signup-firstname"><i class="fa fa-user"></i> First name</label>
          <input id="signup-firstname" name="first_name" type="text" required>
          <label for="signup-lastname"><i class="fa fa-user"></i> Last name</label>
          <input id="signup-lastname" name="last_name" type="text">
          <label for="signup-email"><i class="fa fa-envelope"></i> Email</label>
          <input id="signup-email" name="email" type="email" required>
          <label for="signup-password"><i class="fa fa-key"></i> Password</label>
          <input id="signup-password" name="password" type="password" required>
          <button id="signupSubmit" type="button" class="btn btn-primary"><i class="fa fa-user-plus"></i> Create Account</button>
          <div id="signupMsg" class="auth-msg"></div>
        </form>
        <!-- Admin Login -->
        <form id="adminLoginForm" class="auth-form" method="post" autocomplete="off">
          <label for="admin-email"><i class="fa fa-user-shield"></i> Admin Email</label>
          <input type="email" name="email" id="admin-email" required>
          <label for="admin-password"><i class="fa fa-key"></i> Password</label>
          <input type="password" name="password" id="admin-password" required>
          <button id="adminSubmit" type="button" class="btn btn-primary"><i class="fa fa-user-shield"></i> Admin Login</button>
          <div id="adminMsg" class="auth-msg"></div>
        </form>
        <!-- Seller Register -->
        <form id="sellerRegisterForm" class="auth-form" method="post" autocomplete="off">
          <label for="sellerreg-firstname"><i class="fa fa-user"></i> First Name</label>
          <input type="text" name="first_name" id="sellerreg-firstname" required>
          <label for="sellerreg-lastname"><i class="fa fa-user"></i> Last Name</label>
          <input type="text" name="last_name" id="sellerreg-lastname">
          <label for="sellerreg-email"><i class="fa fa-envelope"></i> Seller Email</label>
          <input type="email" name="email" id="sellerreg-email" required>
          <label for="sellerreg-password"><i class="fa fa-key"></i> Password</label>
          <input type="password" name="password" id="sellerreg-password" required>
          <label for="sellerreg-confirm"><i class="fa fa-key"></i> Confirm Password</label>
          <input type="password" name="confirm_password" id="sellerreg-confirm" required>
          <button id="sellerRegisterSubmit" type="button" class="btn btn-primary"><i class="fa fa-user-plus"></i> Register Seller</button>
          <div id="sellerRegisterMsg" class="auth-msg"></div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <noscript><div class="noscript-warning container">JavaScript disabled — some features need JavaScript.</div></noscript>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal');
    const authModalBtn = document.getElementById('authModalBtn');
    const closeModal = document.getElementById('closeModal');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const authForms = document.querySelectorAll('.auth-form');

    function openAuthModal(target='loginForm') {
      if (!modal) return;
      modal.classList.add('active');
      modal.setAttribute('aria-hidden','false');
      tabBtns.forEach(b => b.classList.remove('active'));
      authForms.forEach(f => f.classList.remove('active'));
      const tb = Array.from(tabBtns).find(b => b.dataset.target === target);
      if (tb) tb.classList.add('active');
      const tf = document.getElementById(target);
      if (tf) tf.classList.add('active');
    }
    function closeAuthModal() {
      if (!modal) return;
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden','true');
    }
    if (authModalBtn) authModalBtn.addEventListener('click', () => openAuthModal('loginForm'));
    if (closeModal) closeModal.addEventListener('click', closeAuthModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeAuthModal(); });

    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.dataset.target;
        authForms.forEach(f => f.classList.remove('active'));
        const el = document.getElementById(target);
        if (el) el.classList.add('active');
      });
    });

    // --- Customer login/signup ---
    async function postJSON(url, data) {
      const params = new URLSearchParams();
      for (const k in data) params.append(k, data[k]);
      const res = await fetch(url, { method: 'POST', body: params });
      return res.json();
    }
    // Customer signup
    const signupSubmit = document.getElementById('signupSubmit');
    if (signupSubmit) {
      signupSubmit.addEventListener('click', async () => {
        const first = document.getElementById('signup-firstname').value.trim();
        const last = document.getElementById('signup-lastname').value.trim();
        const email = document.getElementById('signup-email').value.trim();
        const pass = document.getElementById('signup-password').value;
        const msg = document.getElementById('signupMsg');
        msg.textContent = 'Creating account...';
        try {
          const res = await postJSON('signup.php', { first_name: first, last_name: last, email: email, password: pass });
          if (res.ok) {
            msg.textContent = 'Account created. Redirecting...';
            location.reload();
          } else msg.textContent = res.msg || 'Error';
        } catch (e) { msg.textContent = 'Network error'; }
      });
    }
    // Customer login
    const loginSubmit = document.getElementById('loginSubmit');
    if (loginSubmit) {
      loginSubmit.addEventListener('click', async () => {
        const email = document.getElementById('login-email').value.trim();
        const pass = document.getElementById('login-password').value;
        const msg = document.getElementById('loginMsg');
        msg.textContent = 'Signing in...';
        try {
          const res = await postJSON('login.php', { email: email, password: pass });
          if (res.ok) {
            msg.textContent = 'Logged in. Reloading...';
            location.reload();
          } else msg.textContent = res.msg || 'Invalid';
        } catch (e) { msg.textContent = 'Network error'; }
      });
    }
    // Admin login
    const adminSubmit = document.getElementById('adminSubmit');
    if (adminSubmit) {
      adminSubmit.addEventListener('click', async () => {
        const email = document.getElementById('admin-email').value.trim();
        const pass = document.getElementById('admin-password').value;
        const msg = document.getElementById('adminMsg');
        msg.textContent = 'Signing in...';
        try {
          const res = await postJSON('login.php', { email: email, password: pass });
          if (res.ok && res.user && res.user.role === 'admin') {
            msg.textContent = 'Welcome admin. Redirecting...';
            window.location.href = 'admin_dashboard.php';
          } else if (res.ok) {
            msg.textContent = 'Account is not admin';
          } else msg.textContent = res.msg || 'Invalid';
        } catch (e) { msg.textContent = 'Network error'; }
      });
    }
    // Seller register (replaces previous admin-register behavior)
    const sellerRegisterSubmit = document.getElementById('sellerRegisterSubmit');
    if (sellerRegisterSubmit) {
      sellerRegisterSubmit.addEventListener('click', async () => {
        const fname = document.getElementById('sellerreg-firstname').value.trim();
        const lname = document.getElementById('sellerreg-lastname').value.trim();
        const email = document.getElementById('sellerreg-email').value.trim();
        const pass = document.getElementById('sellerreg-password').value;
        const confirm = document.getElementById('sellerreg-confirm').value;
        const msg = document.getElementById('sellerRegisterMsg');
        msg.textContent = 'Registering seller...';
        if (!fname || !email || !pass || !confirm) {
          msg.textContent = 'Please fill in all fields.';
          return;
        }
        if (pass !== confirm) {
          msg.textContent = 'Passwords do not match.';
          return;
        }
        try {
          const params = { first_name: fname, last_name: lname, email: email, password: pass, confirm_password: confirm };
          const res = await postJSON('seller_register.php', params);
          if (res.ok) {
            msg.textContent = 'Seller registered. Redirecting...';
            // If auto-logged in, redirect to seller dashboard
            if (res.user && res.user.role === 'seller') {
              window.location.href = 'seller_dashboard.php';
            } else {
              location.reload();
            }
          } else msg.textContent = res.msg || 'Error';
        } catch (e) { msg.textContent = 'Network error'; }
      });
    }
  });
  </script>
</body>
</html>
