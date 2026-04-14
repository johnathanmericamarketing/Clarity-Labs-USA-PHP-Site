<?php
/* ============================================================
   ClarityLabsUSA — Reset Password
   Shared by both email link (?token=...) and SMS code flows.
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';

age_gate_only();

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: ' . SHOP_URL . '/account/forgot-password');
    exit;
}

$page_title = 'Set New Password';
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
    .password-hint {
      font-size: 12px;
      color: var(--gray-500);
      margin-top: -10px;
      margin-bottom: 16px;
      text-align: left;
    }
  </style>
</head>
<body>
  <div class="reset-page">
    <div class="reset-card">
      <h1>Set New Password</h1>
      <p>Choose a strong password for your account.</p>

      <div id="message"></div>

      <form id="reset-form">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="password" name="new_password" placeholder="New password" minlength="8" required>
        <div class="password-hint">Minimum 8 characters</div>
        <input type="password" name="new_password_confirmation" placeholder="Confirm new password" minlength="8" required>
        <button type="submit" id="reset-btn">Reset Password</button>
      </form>

      <div style="margin-top: 20px;">
        <a href="<?= SHOP_URL ?>/gate/sign-in">&larr; Back to Sign In</a>
      </div>
    </div>
  </div>

  <script>
  document.getElementById('reset-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('reset-btn');
    const msgEl = document.getElementById('message');
    btn.disabled = true;
    btn.textContent = 'Resetting...';

    try {
      const fd = new FormData(e.target);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=reset-password', {
        method: 'POST',
        body: fd,
      });
      const data = await res.json();

      if (data.success) {
        msgEl.innerHTML = `<div class="reset-message reset-message--success">
          ${data.message || 'Password reset successfully!'}
          <br><br>
          <a href="<?= SHOP_URL ?>/gate/sign-in" style="color: #065F46; font-weight: 600;">Sign in with your new password &rarr;</a>
        </div>`;
        // Hide the form after success
        document.getElementById('reset-form').style.display = 'none';
      } else {
        msgEl.innerHTML = `<div class="reset-message reset-message--error">${data.error || 'Failed to reset password.'}</div>`;
        btn.disabled = false;
        btn.textContent = 'Reset Password';
      }
    } catch (err) {
      msgEl.innerHTML = '<div class="reset-message reset-message--error">Something went wrong. Please try again.</div>';
      btn.disabled = false;
      btn.textContent = 'Reset Password';
    }
  });
  </script>
</body>
</html>
