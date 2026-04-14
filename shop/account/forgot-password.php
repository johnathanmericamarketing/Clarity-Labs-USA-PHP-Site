<?php
/* ============================================================
   ClarityLabsUSA — Forgot Password (Email + SMS)
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

// Audit C2: forgot-password.php enforces age gate (no login
// required since the user is trying to recover their password).
age_gate_only();

$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    body { opacity: 1; animation: none; }
    .reset-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      padding: 20px;
    }
    .reset-card {
      background: var(--white);
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 440px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }
    .reset-card h1 {
      font-family: var(--font-display);
      font-size: 28px;
      color: var(--navy);
      margin-bottom: 8px;
    }
    .reset-card p {
      font-size: 14px;
      color: var(--gray-600);
      margin-bottom: 24px;
    }
    .reset-card input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      margin-bottom: 16px;
      box-sizing: border-box;
    }
    .reset-card input:focus {
      outline: none;
      border-color: var(--green);
    }
    .reset-card button {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      cursor: pointer;
    }
    .reset-card button:disabled { opacity: 0.5; cursor: not-allowed; }
    .reset-message {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .reset-message--success { background: #D1FAE5; color: #065F46; }
    .reset-message--error { background: #FEE2E2; color: #991B1B; }
    .reset-card a { color: var(--green); font-size: 14px; }

    /* Toggle between Email / SMS */
    .method-toggle {
      display: flex;
      gap: 0;
      margin-bottom: 24px;
      border-radius: 50px;
      overflow: hidden;
      border: 2px solid var(--green);
    }
    .method-toggle button {
      flex: 1;
      padding: 10px 16px;
      border: none;
      border-radius: 0;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      background: transparent;
      color: var(--green);
    }
    .method-toggle button.active {
      background: linear-gradient(135deg, var(--green), var(--navy));
      color: var(--white);
    }

    /* Code input styling */
    .code-input {
      letter-spacing: 8px;
      text-align: center;
      font-size: 24px;
      font-weight: 700;
      font-family: monospace;
    }

    .step { display: none; }
    .step.active { display: block; }

    .resend-link {
      display: inline-block;
      margin-top: 12px;
      font-size: 13px;
      color: var(--gray-600);
      cursor: pointer;
    }
    .resend-link:hover { color: var(--green); }
  </style>
</head>
<body>
  <div class="reset-page">
    <div class="reset-card">
      <h1>Reset Password</h1>

      <!-- Method Toggle -->
      <div class="method-toggle">
        <button id="toggle-email" class="active" onclick="switchMethod('email')">Email</button>
        <button id="toggle-sms" onclick="switchMethod('sms')">Text Message</button>
      </div>

      <div id="message"></div>

      <!-- ─── EMAIL METHOD ─── -->
      <div id="email-step" class="step active">
        <p>Enter your email address and we'll send you a link to reset your password.</p>
        <form id="email-form">
          <?= csrf_field() ?>
          <input type="email" name="email" placeholder="you@example.com" required>
          <button type="submit" id="email-btn">Send Reset Link</button>
        </form>
      </div>

      <!-- ─── SMS METHOD: Step 1 — Enter Phone ─── -->
      <div id="sms-step-1" class="step">
        <p>Enter the phone number associated with your account. We'll text you a verification code.</p>
        <form id="sms-phone-form">
          <?= csrf_field() ?>
          <input type="tel" name="phone" id="sms-phone" placeholder="(555) 123-4567" required>
          <button type="submit" id="sms-phone-btn">Send Code</button>
        </form>
      </div>

      <!-- ─── SMS METHOD: Step 2 — Enter Code ─── -->
      <div id="sms-step-2" class="step">
        <p>Enter the 6-digit code we sent to <strong id="display-phone"></strong></p>
        <form id="sms-code-form">
          <?= csrf_field() ?>
          <input type="text" name="code" class="code-input" maxlength="6" placeholder="000000" pattern="[0-9]{4,6}" inputmode="numeric" required autocomplete="one-time-code">
          <button type="submit" id="sms-code-btn">Verify Code</button>
        </form>
        <span class="resend-link" onclick="resendCode()">Didn't get it? Resend code</span>
      </div>

      <div style="margin-top: 20px;">
        <a href="<?= SHOP_URL ?>/gate/sign-in">&larr; Back to Sign In</a>
      </div>
    </div>
  </div>

  <script>
  const CSRF = '<?= csrf_token() ?>';
  let currentMethod = 'email';
  let smsPhone = '';

  function switchMethod(method) {
    currentMethod = method;
    document.getElementById('toggle-email').classList.toggle('active', method === 'email');
    document.getElementById('toggle-sms').classList.toggle('active', method === 'sms');
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('message').innerHTML = '';

    if (method === 'email') {
      document.getElementById('email-step').classList.add('active');
    } else {
      document.getElementById('sms-step-1').classList.add('active');
    }
  }

  function showMsg(type, text) {
    const el = document.getElementById('message');
    el.innerHTML = `<div class="reset-message reset-message--${type}">${text}</div>`;
  }

  // ─── Email Form ───
  document.getElementById('email-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('email-btn');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
      const fd = new FormData(e.target);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=forgot-password', { method: 'POST', body: fd });
      const data = await res.json();
      showMsg(data.success ? 'success' : 'error', data.message || data.error || 'Something went wrong.');
    } catch (err) {
      showMsg('error', 'Something went wrong. Please try again.');
    }
    btn.disabled = false;
    btn.textContent = 'Send Reset Link';
  });

  // ─── SMS Step 1: Send Code ───
  document.getElementById('sms-phone-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('sms-phone-btn');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    smsPhone = document.getElementById('sms-phone').value.trim();

    try {
      const fd = new FormData(e.target);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=forgot-password-sms', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        // Move to code entry step
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById('sms-step-2').classList.add('active');
        document.getElementById('display-phone').textContent = smsPhone;
        showMsg('success', 'Verification code sent! Check your text messages.');
      } else {
        showMsg('error', data.error || 'Failed to send code.');
      }
    } catch (err) {
      showMsg('error', 'Something went wrong. Please try again.');
    }
    btn.disabled = false;
    btn.textContent = 'Send Code';
  });

  // ─── SMS Step 2: Verify Code ───
  document.getElementById('sms-code-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('sms-code-btn');
    btn.disabled = true;
    btn.textContent = 'Verifying...';

    try {
      const fd = new FormData(e.target);
      fd.append('phone', smsPhone);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=verify-reset-code', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success && data.token) {
        // Redirect to reset-password page with the token
        window.location.href = '<?= SHOP_URL ?>/account/reset-password?token=' + encodeURIComponent(data.token);
      } else {
        showMsg('error', data.error || 'Invalid code. Please try again.');
      }
    } catch (err) {
      showMsg('error', 'Something went wrong. Please try again.');
    }
    btn.disabled = false;
    btn.textContent = 'Verify Code';
  });

  // ─── Resend Code ───
  async function resendCode() {
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone', smsPhone);

    try {
      showMsg('success', 'Resending code...');
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=forgot-password-sms', { method: 'POST', body: fd });
      const data = await res.json();
      showMsg('success', 'New code sent! Check your text messages.');
    } catch (err) {
      showMsg('error', 'Failed to resend. Please try again.');
    }
  }
  </script>
</body>
</html>
