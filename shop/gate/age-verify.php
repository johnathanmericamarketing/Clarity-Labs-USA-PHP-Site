<?php
/* ============================================================
   Gate 1: Age Verification
   Full-screen modal — must confirm 21+ before entering shop
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';

clarity_session_start();

// If already verified, redirect to shop (or to original destination)
if (is_age_verified()) {
    $redirect = $_GET['redirect'] ?? '/';
    header('Location: ' . SHOP_URL . $redirect);
    exit;
}

// Handle POST (age confirmation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_age_verified();
    $redirect = $_POST['redirect'] ?? '/';
    header('Location: ' . SHOP_URL . '/gate/sign-in.php?redirect=' . urlencode($redirect));
    exit;
}

$redirect = $_GET['redirect'] ?? '/';
$page_title = 'Age Verification';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    body { opacity: 1; animation: none; }

    .age-gate {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--navy);
      padding: 20px;
    }

    .age-gate__card {
      background: var(--white);
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 560px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    }

    .age-gate__logo {
      width: 72px;
      height: auto;
      margin: 0 auto 8px;
    }

    .age-gate__brand {
      font-family: var(--font-display);
      font-size: 24px;
      color: var(--navy);
      margin-bottom: 4px;
      line-height: 1.2;
    }

    .age-gate__brand span {
      color: var(--green);
    }

    .age-gate__tagline {
      font-family: var(--font-mono);
      font-size: 9px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--gray-400);
      margin-bottom: 32px;
    }

    .age-gate__title {
      font-family: var(--font-display);
      font-size: 42px;
      color: var(--navy);
      margin-bottom: 8px;
      background: linear-gradient(135deg, var(--navy), var(--green));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .age-gate__subtitle {
      font-size: 15px;
      color: var(--gray-600);
      margin-bottom: 28px;
    }

    .age-gate__disclaimer {
      font-size: 13px;
      line-height: 1.7;
      color: var(--gray-600);
      margin-bottom: 36px;
      text-align: center;
      padding: 0 12px;
    }

    .age-gate__btn {
      display: inline-block;
      width: 100%;
      max-width: 340px;
      padding: 16px 32px;
      border: none;
      border-radius: 50px;
      font-family: var(--font-body);
      font-weight: 600;
      font-size: 16px;
      letter-spacing: 0.5px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green) 0%, var(--navy) 100%);
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .age-gate__btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26, 122, 110, 0.35);
    }

    @media (max-width: 600px) {
      .age-gate__card { padding: 36px 24px; }
      .age-gate__title { font-size: 32px; }
      .age-gate__disclaimer { font-size: 12px; }
    }
  </style>
</head>
<body>
  <div class="age-gate">
    <div class="age-gate__card">
      <img src="<?= $base_path ?>Logo/ClarityLabsUSA-icon-256px.webp" alt="Clarity Labs USA" class="age-gate__logo">
      <div class="age-gate__brand">Clarity<br>Labs <span>USA</span></div>
      <div class="age-gate__tagline">Clarity &bull; Confidence &bull; Simplicity</div>

      <h1 class="age-gate__title">Confirm Your Age</h1>
      <p class="age-gate__subtitle">Confirm that you are 21 years old or over.</p>

      <p class="age-gate__disclaimer"><?= htmlspecialchars(AGE_GATE_DISCLAIMER) ?></p>

      <form method="POST" action="">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <button type="submit" class="age-gate__btn">Enter Website</button>
      </form>
    </div>
  </div>
</body>
</html>
