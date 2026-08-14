<?php
/* ============================================================
   Email Change Confirmation Landing Page
   Clicked from the "Confirm your new email" message sent to the
   NEW address. Applies the pending email change via the ops API.
   ============================================================ */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

// They came from an email link — same treatment as verify-and-sign-in
if (!is_age_verified()) {
    set_age_verified();
}

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (!empty($token)) {
    $api = new ClarityApiClient();
    $result = $api->verifyEmailChange($token);

    if (!empty($result['status']) && $result['status'] === 'ok') {
        $success = true;
        $message = $result['message'] ?? 'Email address confirmed!';

        // If they're signed in on this browser, refresh the cached customer
        // so the account pages show the new email immediately
        if (is_logged_in()) {
            $me = $api->getMe(get_customer_token());
            if (!empty($me['data'])) {
                set_customer($me['data'], get_customer_token());
            }
        }
    } else {
        $message = $result['message'] ?? 'Confirmation failed or the link expired.';
    }
} else {
    $message = 'No confirmation token provided.';
}

$base_path = '../../';
$page_title = 'Confirm Email Change';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    body { opacity: 1; animation: none; }
    .verify-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      padding: 20px;
    }
    .verify-card {
      background: var(--white);
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 480px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }
    .verify-icon {
      font-size: 64px;
      margin-bottom: 16px;
    }
    .verify-card h1 {
      font-family: var(--font-display);
      font-size: 28px;
      color: var(--navy);
      margin-bottom: 12px;
    }
    .verify-card p {
      font-size: 15px;
      color: var(--gray-600);
      line-height: 1.7;
      margin-bottom: 24px;
    }
    .verify-card .btn {
      display: inline-block;
      padding: 14px 32px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      text-decoration: none;
      transition: transform 0.2s;
    }
    .verify-card .btn:hover { transform: translateY(-2px); }
    .verify-success { color: #059669; }
    .verify-error { color: #DC2626; }
  </style>
</head>
<body>
  <div class="verify-page">
    <div class="verify-card">
      <?php if ($success): ?>
        <div class="verify-icon">&#10003;</div>
        <h1 class="verify-success">Email Updated!</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <p style="font-size: 13px; color: var(--gray-400);">Use your new email address the next time you sign in.</p>
        <a href="<?= SHOP_URL ?>/account/" class="btn">Go to My Account</a>
      <?php else: ?>
        <div class="verify-icon">&#9888;</div>
        <h1 class="verify-error">Confirmation Issue</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <p style="font-size: 13px; color: var(--gray-400);">If your link expired, request the email change again from your account settings.</p>
        <a href="<?= SHOP_URL ?>/account/settings" class="btn">Account Settings</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
