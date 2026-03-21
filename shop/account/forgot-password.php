<?php
/* ============================================================
   ClarityLabsUSA — Forgot Password
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

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
    .reset-card button:disabled { opacity: 0.5; }
    .reset-message {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .reset-message--success { background: #D1FAE5; color: #065F46; }
    .reset-message--error { background: #FEE2E2; color: #991B1B; }
    .reset-card a { color: var(--green); font-size: 14px; }
  </style>
</head>
<body>
  <div class="reset-page">
    <div class="reset-card">
      <h1>Reset Password</h1>
      <p>Enter your email address and we'll send you a link to reset your password.</p>
      <div id="message"></div>
      <form id="reset-form">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="you@example.com" required>
        <button type="submit" id="reset-btn">Send Reset Link</button>
      </form>
      <div style="margin-top: 20px;">
        <a href="<?= SHOP_URL ?>/gate/sign-in.php">← Back to Sign In</a>
      </div>
    </div>
  </div>
  <script>
  document.getElementById('reset-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('reset-btn');
    btn.disabled = true;
    btn.textContent = 'Sending...';
    const msgEl = document.getElementById('message');

    try {
      const formData = new FormData(e.target);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=forgot-password', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();
      msgEl.innerHTML = '<div class="reset-message reset-message--' + (data.success ? 'success' : 'error') + '">' +
        (data.message || data.error || 'Something went wrong.') + '</div>';
    } catch (err) {
      msgEl.innerHTML = '<div class="reset-message reset-message--error">Something went wrong. Please try again.</div>';
    }
    btn.disabled = false;
    btn.textContent = 'Send Reset Link';
  });
  </script>
</body>
</html>
