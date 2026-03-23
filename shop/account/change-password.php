<?php
/* ============================================================
   ClarityLabsUSA — Change Password (First Login)
   Required after signing in with temporary password
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

// Must be logged in to change password
if (!is_logged_in()) {
    header('Location: ' . SHOP_URL . '/gate/sign-in');
    exit;
}

$page_title = 'Set Your Password';
$customer = get_customer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    body { opacity: 1; animation: none; }
    .change-pw-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      padding: 20px;
    }
    .change-pw-card {
      background: var(--white);
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 460px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }
    .change-pw-card img {
      width: 60px;
      margin: 0 auto 12px;
    }
    .change-pw-card h1 {
      font-family: var(--font-display);
      font-size: 28px;
      color: var(--navy);
      margin-bottom: 8px;
    }
    .change-pw-card .subtitle {
      font-size: 14px;
      color: var(--gray-600);
      margin-bottom: 28px;
      line-height: 1.6;
    }
    .change-pw-card .form-group {
      text-align: left;
      margin-bottom: 16px;
    }
    .change-pw-card label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--navy);
      margin-bottom: 4px;
    }
    .change-pw-card input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      color: var(--navy);
    }
    .change-pw-card input:focus {
      outline: none;
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(26,122,110,0.1);
    }
    .change-pw-card button {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      cursor: pointer;
      margin-top: 8px;
      transition: transform 0.2s;
    }
    .change-pw-card button:hover { transform: translateY(-2px); }
    .change-pw-card button:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .pw-requirements {
      text-align: left;
      font-size: 12px;
      color: var(--gray-400);
      margin-top: 8px;
      line-height: 1.6;
    }
    .change-pw-message {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }
    .change-pw-message--error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
    .change-pw-message--success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }

    @media (max-width: 600px) {
      .change-pw-card { padding: 32px 20px; }
    }
  </style>
</head>
<body>
  <div class="change-pw-page">
    <div class="change-pw-card">
      <img src="<?= R2_PUBLIC_URL ?>/clarity-logo/icon_no_background.webp" alt="Clarity Labs USA">
      <h1>Set Your New Password</h1>
      <p class="subtitle">
        Welcome, <?= htmlspecialchars($customer['first_name'] ?? '') ?>! For your security, please set a new password to replace the temporary one.
      </p>

      <div id="change-pw-message"></div>

      <form id="change-pw-form">
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="new-password">New Password</label>
          <input type="password" id="new-password" name="new_password" placeholder="At least 8 characters" required minlength="8">
        </div>

        <div class="form-group">
          <label for="confirm-password">Confirm New Password</label>
          <input type="password" id="confirm-password" name="new_password_confirmation" placeholder="Re-enter your password" required minlength="8">
        </div>

        <div class="pw-requirements">
          Password must be at least 8 characters long.
        </div>

        <button type="submit" id="change-pw-btn">Set Password & Continue</button>
      </form>
    </div>
  </div>

  <script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  document.getElementById('change-pw-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('change-pw-btn');
    const msgEl = document.getElementById('change-pw-message');
    const newPw = document.getElementById('new-password').value;
    const confirmPw = document.getElementById('confirm-password').value;

    if (newPw !== confirmPw) {
      msgEl.innerHTML = '<div class="change-pw-message change-pw-message--error">Passwords do not match.</div>';
      return;
    }

    if (newPw.length < 8) {
      msgEl.innerHTML = '<div class="change-pw-message change-pw-message--error">Password must be at least 8 characters.</div>';
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Updating...';
    msgEl.innerHTML = '';

    try {
      const formData = new FormData(e.target);
      const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=change-password', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        msgEl.innerHTML = '<div class="change-pw-message change-pw-message--success">Password updated! Redirecting to the shop...</div>';
        setTimeout(() => {
          window.location.href = '<?= SHOP_URL ?>/';
        }, 2000);
      } else {
        const errDiv = document.createElement('div');
        errDiv.className = 'change-pw-message change-pw-message--error';
        errDiv.textContent = data.error || 'Failed to update password.';
        msgEl.innerHTML = '';
        msgEl.appendChild(errDiv);
        btn.disabled = false;
        btn.textContent = 'Set Password & Continue';
      }
    } catch (err) {
      const errDiv = document.createElement('div');
      errDiv.className = 'change-pw-message change-pw-message--error';
      errDiv.textContent = 'Something went wrong. Please try again.';
      msgEl.innerHTML = '';
      msgEl.appendChild(errDiv);
      btn.disabled = false;
      btn.textContent = 'Set Password & Continue';
    }
  });
  </script>
</body>
</html>
