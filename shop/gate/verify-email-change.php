<?php
/* ============================================================
   Email Change Confirmation Landing Page
   Clicked from the "Confirm your new email" message sent to the
   NEW address. GET renders a Confirm button; only the explicit
   POST applies the change — mail scanners (Outlook SafeLinks
   etc.) prefetch GET links, and a prefetch must never confirm
   an email change on its own.
   ============================================================ */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

// They came from an email link — same treatment as verify-and-sign-in
if (!is_age_verified()) {
    set_age_verified();
}

$state = 'confirm'; // confirm | success | error
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';

    if (empty($token)) {
        $state = 'error';
        $message = 'No confirmation token provided.';
    } else {
        $api = new ClarityApiClient();
        $result = $api->verifyEmailChange($token);

        if (!empty($result['status']) && $result['status'] === 'ok') {
            $state = 'success';
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
            $state = 'error';
            $message = $result['message'] ?? 'Confirmation failed or the link expired.';
        }
    }
} else {
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
        $state = 'error';
        $message = 'No confirmation token provided.';
    }
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
      border: none;
      cursor: pointer;
      font-family: inherit;
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
      <?php if ($state === 'confirm'): ?>
        <div class="verify-icon">&#9993;</div>
        <h1>Confirm Email Change</h1>
        <p>Click the button below to confirm this as the new email address for your ClarityLabs account. Your sign-in and all account emails will switch to it.</p>
        <form method="post">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <button type="submit" class="btn">Confirm New Email</button>
        </form>
      <?php elseif ($state === 'success'): ?>
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
