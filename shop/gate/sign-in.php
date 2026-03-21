<?php
/* ============================================================
   Gate 2: Sign-In Required
   Combined login + registration page
   After age verification, customers must sign in to browse
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';

// Only enforce age gate here (not login — this IS the login page)
age_gate_only();

// If already logged in, redirect to shop
if (is_logged_in()) {
    $redirect = $_GET['redirect'] ?? '/';
    header('Location: ' . SHOP_URL . $redirect);
    exit;
}

$redirect = $_GET['redirect'] ?? '/';
$mode = $_GET['mode'] ?? 'login'; // 'login' or 'register'
$error = '';
$success = '';

$page_title = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    body { opacity: 1; animation: none; }

    .signin-gate {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      padding: 20px;
    }

    .signin-gate__card {
      background: var(--white);
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 520px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    }

    .signin-gate__logo {
      width: 72px;
      height: auto;
      margin: 0 auto 8px;
    }

    .signin-gate__brand {
      font-family: var(--font-display);
      font-size: 24px;
      color: var(--navy);
      margin-bottom: 4px;
      line-height: 1.2;
    }

    .signin-gate__brand span { color: var(--green); }

    .signin-gate__tagline {
      font-family: var(--font-mono);
      font-size: 9px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--gray-400);
      margin-bottom: 28px;
    }

    .signin-gate__title {
      font-family: var(--font-display);
      font-size: 32px;
      color: var(--navy);
      margin-bottom: 8px;
    }

    .signin-gate__title span { color: var(--green); }

    .signin-gate__desc {
      font-size: 14px;
      color: var(--gray-600);
      margin-bottom: 24px;
      line-height: 1.7;
    }

    .signin-gate__why {
      background: linear-gradient(135deg, rgba(26, 122, 110, 0.08), rgba(42, 157, 143, 0.12));
      border: 1px solid var(--green-bdr);
      border-radius: 12px;
      padding: 20px 24px;
      margin-bottom: 28px;
    }

    .signin-gate__why h3 {
      font-family: var(--font-display);
      font-size: 22px;
      color: var(--navy);
      margin-bottom: 4px;
    }

    .signin-gate__why h3 span { color: var(--green); }

    .signin-gate__why p {
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.7;
    }

    /* ── Form Styles ── */
    .gate-form { text-align: left; }

    .gate-form__group {
      margin-bottom: 16px;
    }

    .gate-form__label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--navy);
      margin-bottom: 4px;
    }

    .gate-form__input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      color: var(--navy);
      transition: border-color 0.2s;
      background: var(--white);
    }

    .gate-form__input:focus {
      outline: none;
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(26, 122, 110, 0.1);
    }

    .gate-form__input.error {
      border-color: #E53E3E;
    }

    .gate-form__row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .gate-form__checkbox {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 16px;
    }

    .gate-form__checkbox input[type="checkbox"] {
      margin-top: 3px;
      width: 18px;
      height: 18px;
      accent-color: var(--green);
    }

    .gate-form__checkbox label {
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.5;
    }

    .gate-form__dob {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .gate-form__dob select {
      padding: 12px 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      color: var(--navy);
      background: var(--white);
      cursor: pointer;
    }

    .gate-form__submit {
      display: block;
      width: 100%;
      padding: 14px 32px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green) 0%, var(--navy) 100%);
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      margin-top: 8px;
    }

    .gate-form__submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26, 122, 110, 0.35);
    }

    .gate-form__submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .gate-form__switch {
      font-size: 14px;
      color: var(--gray-600);
      margin-top: 20px;
      text-align: center;
    }

    .gate-form__switch a {
      color: var(--green);
      font-weight: 600;
      text-decoration: underline;
    }

    .gate-form__forgot {
      font-size: 13px;
      text-align: right;
      margin-top: -8px;
      margin-bottom: 16px;
    }

    .gate-form__forgot a {
      color: var(--green);
    }

    /* ── Messages ── */
    .gate-message {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .gate-message--error {
      background: #FEE2E2;
      color: #991B1B;
      border: 1px solid #FECACA;
    }

    .gate-message--success {
      background: #D1FAE5;
      color: #065F46;
      border: 1px solid #A7F3D0;
    }

    /* ── Tab Toggle ── */
    .gate-tabs {
      display: flex;
      gap: 0;
      margin-bottom: 24px;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid var(--gray-200);
    }

    .gate-tabs__btn {
      flex: 1;
      padding: 10px;
      font-size: 14px;
      font-weight: 500;
      color: var(--gray-600);
      background: var(--gray-50);
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .gate-tabs__btn.active {
      color: var(--white);
      background: var(--navy);
    }

    @media (max-width: 600px) {
      .signin-gate__card { padding: 32px 20px; }
      .signin-gate__title { font-size: 26px; }
      .gate-form__row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="signin-gate">
    <div class="signin-gate__card">
      <img src="<?= R2_PUBLIC_URL ?>/clarity-logo/icon_no_background.webp" alt="Clarity Labs USA" class="signin-gate__logo">
      <div class="signin-gate__brand">Clarity<br>Labs <span>USA</span></div>
      <div class="signin-gate__tagline">Clarity &bull; Confidence &bull; Simplicity</div>

      <h1 class="signin-gate__title">Sign-In Required to Access<br>Clarity Labs <span>USA</span></h1>
      <p class="signin-gate__desc">To ensure compliance with evolving regulations and continue providing high-quality research products, Clarity Labs USA now requires all professional researchers to log in to browse or purchase.</p>

      <div class="signin-gate__why">
        <h3>Why The <span>Change?</span></h3>
        <p>This helps us maintain strict standards for regulatory compliance, researcher verification, and responsible product access.</p>
      </div>

      <!-- Tab Toggle -->
      <div class="gate-tabs">
        <button class="gate-tabs__btn" data-tab="login" id="tab-login">Sign In</button>
        <button class="gate-tabs__btn" data-tab="register" id="tab-register">Sign Up</button>
      </div>

      <!-- Error/Success Messages -->
      <div id="gate-message"></div>

      <!-- LOGIN FORM -->
      <form class="gate-form" id="form-login" style="display: none;">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="gate-form__group">
          <label class="gate-form__label" for="login-email">Email Address</label>
          <input type="email" id="login-email" name="email" class="gate-form__input" placeholder="you@example.com" required>
        </div>

        <div class="gate-form__group">
          <label class="gate-form__label" for="login-password">Password</label>
          <input type="password" id="login-password" name="password" class="gate-form__input" placeholder="Enter your password" required>
        </div>

        <div class="gate-form__forgot">
          <a href="<?= SHOP_URL ?>/account/forgot-password.php">Forgot password?</a>
        </div>

        <button type="submit" class="gate-form__submit" id="login-submit">Sign In</button>

        <p class="gate-form__switch">
          Don't have an account? <a href="#" data-switch="register">Sign up here</a>
        </p>
      </form>

      <!-- REGISTER FORM -->
      <form class="gate-form" id="form-register" style="display: none;">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="gate-form__row">
          <div class="gate-form__group">
            <label class="gate-form__label" for="reg-first">First Name</label>
            <input type="text" id="reg-first" name="first_name" class="gate-form__input" placeholder="John" required>
          </div>
          <div class="gate-form__group">
            <label class="gate-form__label" for="reg-last">Last Name</label>
            <input type="text" id="reg-last" name="last_name" class="gate-form__input" placeholder="Doe" required>
          </div>
        </div>

        <div class="gate-form__group">
          <label class="gate-form__label" for="reg-email">Email Address</label>
          <input type="email" id="reg-email" name="email" class="gate-form__input" placeholder="you@example.com" required>
        </div>

        <div class="gate-form__group">
          <label class="gate-form__label">Date of Birth</label>
          <div class="gate-form__dob">
            <select name="birth_month" id="reg-month" required>
              <option value="">Month</option>
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
              <?php endfor; ?>
            </select>
            <select name="birth_year" id="reg-year" required>
              <option value="">Year</option>
              <?php
              $currentYear = (int) date('Y');
              for ($y = $currentYear - 18; $y >= $currentYear - 100; $y--): ?>
                <option value="<?= $y ?>"><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="gate-form__checkbox">
          <input type="checkbox" id="reg-research" name="research_confirmed" required>
          <label for="reg-research">I confirm that I am a qualified research professional and understand that all products are intended exclusively for in vitro research and laboratory use.</label>
        </div>

        <button type="submit" class="gate-form__submit" id="register-submit">Create Account</button>

        <p class="gate-form__switch">
          Already have an account? <a href="#" data-switch="login">Sign in here</a>
        </p>
      </form>
    </div>
  </div>

  <script>
  (function() {
    const tabLogin    = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const formLogin   = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const messageBox  = document.getElementById('gate-message');

    // Tab switching
    function showTab(tab) {
      if (tab === 'login') {
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
        formLogin.style.display = 'block';
        formRegister.style.display = 'none';
      } else {
        tabRegister.classList.add('active');
        tabLogin.classList.remove('active');
        formRegister.style.display = 'block';
        formLogin.style.display = 'none';
      }
      messageBox.innerHTML = '';
    }

    // Initialize based on mode param
    const urlParams = new URLSearchParams(window.location.search);
    showTab(urlParams.get('mode') === 'register' ? 'register' : 'login');

    tabLogin.addEventListener('click', () => showTab('login'));
    tabRegister.addEventListener('click', () => showTab('register'));

    // Switch links inside forms
    document.querySelectorAll('[data-switch]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        showTab(link.dataset.switch);
      });
    });

    function showMessage(type, text) {
      messageBox.innerHTML = '<div class="gate-message gate-message--' + type + '">' + text + '</div>';
    }

    // Login form handler
    formLogin.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('login-submit');
      const origText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Signing in...';
      messageBox.innerHTML = '';

      try {
        const formData = new FormData(formLogin);
        const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=login', {
          method: 'POST',
          body: formData,
        });
        const data = await res.json();

        if (data.success) {
          const redirect = formData.get('redirect') || '/';
          window.location.href = '<?= SHOP_URL ?>' + redirect;
        } else {
          showMessage('error', data.error || 'Login failed. Please check your credentials.');
          btn.disabled = false;
          btn.textContent = origText;
        }
      } catch (err) {
        showMessage('error', 'Something went wrong. Please try again.');
        btn.disabled = false;
        btn.textContent = origText;
      }
    });

    // Register form handler
    formRegister.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('register-submit');
      const origText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Creating account...';
      messageBox.innerHTML = '';

      try {
        const formData = new FormData(formRegister);
        const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=register', {
          method: 'POST',
          body: formData,
        });
        const data = await res.json();

        if (data.success) {
          showMessage('success', data.message || 'Account created! Check your email for your temporary password, then sign in.');
          showTab('login');
        } else {
          showMessage('error', data.error || 'Registration failed. Please try again.');
          btn.disabled = false;
          btn.textContent = origText;
        }
      } catch (err) {
        showMessage('error', 'Something went wrong. Please try again.');
        btn.disabled = false;
        btn.textContent = origText;
      }
    });
  })();
  </script>
</body>
</html>
