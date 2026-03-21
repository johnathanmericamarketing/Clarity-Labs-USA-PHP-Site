<?php
/* ============================================================
   Email Verification Landing Page
   Clicked from welcome email — verifies email then redirects to sign-in
   ============================================================ */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

// Set age verified (they came from an email, they already confirmed age during registration)
if (!is_age_verified()) {
    set_age_verified();
}

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (!empty($token)) {
    $api = new ClarityApiClient();
    $result = $api->verifyEmail(['token' => $token]);

    if (!empty($result['status']) && $result['status'] === 'ok') {
        $success = true;
        $message = $result['message'] ?? 'Email verified successfully!';
    } else {
        $message = $result['message'] ?? 'Verification failed or link expired.';
    }
} else {
    $message = 'No verification token provided.';
}

$base_path = '../../';
$page_title = 'Email Verification';
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
  <?php if ($success): ?>
    <meta http-equiv="refresh" content="5;url=<?= SHOP_URL ?>/gate/sign-in">
  <?php endif; ?>
</head>
<body>
  <div class="verify-page">
    <div class="verify-card">
      <?php if ($success): ?>
        <div class="verify-icon">&#10003;</div>
        <h1 class="verify-success">Email Verified!</h1>
        <p>Your account is now active. You can sign in with your email and the temporary password from your welcome email.</p>
        <p style="font-size: 13px; color: var(--gray-400);">Redirecting to sign-in in 5 seconds...</p>
        <a href="<?= SHOP_URL ?>/gate/sign-in" class="btn">Sign In Now</a>
      <?php else: ?>
        <div class="verify-icon">&#9888;</div>
        <h1 class="verify-error">Verification Issue</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <p style="font-size: 13px; color: var(--gray-400);">If your link expired, try logging in — you may be able to request a new verification email.</p>
        <a href="<?= SHOP_URL ?>/gate/sign-in" class="btn">Go to Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
