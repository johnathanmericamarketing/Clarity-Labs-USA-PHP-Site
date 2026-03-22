<?php
/* ============================================================
   ClarityLabsUSA — Saved Addresses
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'Addresses';
$customer = get_customer();
$customerName = get_customer_name();
$current_page = 'account';

// Fetch current profile with address
$api = new ClarityApiClient();
$profileResponse = $api->getMe(get_customer_token());
$profile = $profileResponse['data'] ?? [];
$address = $profile['billing_address'] ?? [];

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $updateData = [
        'billing_address_line1' => trim($_POST['line1'] ?? ''),
        'billing_address_line2' => trim($_POST['line2'] ?? ''),
        'billing_city'          => trim($_POST['city'] ?? ''),
        'billing_state'         => trim($_POST['state'] ?? ''),
        'billing_zip'           => trim($_POST['zip'] ?? ''),
        'billing_country'       => trim($_POST['country'] ?? 'US'),
    ];

    if (empty($updateData['billing_address_line1']) || empty($updateData['billing_city']) || empty($updateData['billing_state']) || empty($updateData['billing_zip'])) {
        $error = 'Please fill in all required address fields.';
    } else {
        $result = $api->updateProfile($updateData, get_customer_token());
        if ($result['success'] ?? false) {
            $success = 'Address updated successfully.';
            // Refresh address data
            $address = [
                'line1' => $updateData['billing_address_line1'],
                'line2' => $updateData['billing_address_line2'],
                'city' => $updateData['billing_city'],
                'state' => $updateData['billing_state'],
                'zip' => $updateData['billing_zip'],
                'country' => $updateData['billing_country'],
            ];
        } else {
            $error = $result['message'] ?? 'Failed to update address. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    .account { padding: 60px 0 100px; min-height: 60vh; }
    .account__header { margin-bottom: 40px; }
    .account__header h1 { font-size: 32px; margin-bottom: 4px; }
    .account__header p { color: var(--gray-600); }
    .account__grid { display: grid; grid-template-columns: 240px 1fr; gap: 40px; }
    .account-nav a { display: block; padding: 10px 16px; font-size: 14px; font-weight: 500; color: var(--gray-600); border-radius: 8px; margin-bottom: 4px; transition: all 0.15s; }
    .account-nav a:hover, .account-nav a.active { background: var(--green-bg); color: var(--green); }
    .account-nav__logout { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--rule); }
    .account-nav__logout a { color: #DC2626; }
    .account-nav__logout a:hover { background: #FEE2E2; }
    .account-card { background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
    .account-card h3 { font-size: 18px; margin-bottom: 16px; }
    .addr-form label { display: block; font-size: 12px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    .addr-form input { width: 100%; height: 42px; border: 1px solid var(--rule); border-radius: 8px; padding: 0 12px; font-size: 14px; color: var(--navy); background: var(--white); outline: none; transition: border-color .2s; }
    .addr-form input:focus { border-color: var(--green); }
    .addr-form .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .addr-form .form-group { margin-bottom: 16px; }
    .addr-alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 8px; padding: 12px 16px; color: #065F46; font-size: 14px; font-weight: 500; margin-bottom: 16px; }
    .addr-alert-error { background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; color: #991B1B; font-size: 14px; font-weight: 500; margin-bottom: 16px; }
    @media (max-width: 768px) {
      .account__grid { grid-template-columns: 1fr; gap: 24px; }
      .addr-form .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <div class="breadcrumb">
    <div class="breadcrumb__inner">
      <a href="<?= SITE_URL ?>">ClarityLabsUSA</a>
      <span class="breadcrumb__sep">/</span>
      <a href="<?= SHOP_URL ?>/account/">Account</a>
      <span class="breadcrumb__sep">/</span>
      <span class="breadcrumb__current">Addresses</span>
    </div>
  </div>

  <main>
    <section class="account">
      <div class="container">
        <div class="account__header">
          <h1>Addresses</h1>
          <p>Manage your billing and shipping address.</p>
        </div>

        <div class="account__grid">
          <!-- Sidebar -->
          <nav class="account-nav">
            <a href="<?= SHOP_URL ?>/account/">Dashboard</a>
            <a href="<?= SHOP_URL ?>/account/orders">Order History</a>
            <a href="<?= SHOP_URL ?>/account/addresses" class="active">Addresses</a>
            <a href="<?= SHOP_URL ?>/account/wishlist">Saved Products</a>
            <a href="<?= SHOP_URL ?>/support/">Support</a>
            <div class="account-nav__logout">
              <a href="#" onclick="logout(); return false;">Sign Out</a>
            </div>
          </nav>

          <!-- Content -->
          <div>
            <?php if ($success): ?>
              <div class="addr-alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
              <div class="addr-alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="account-card">
              <h3>Billing / Shipping Address</h3>
              <form method="POST" class="addr-form">
                <?= csrf_field() ?>

                <div class="form-group">
                  <label for="line1">Street Address *</label>
                  <input type="text" name="line1" id="line1" value="<?= htmlspecialchars($address['line1'] ?? '') ?>" placeholder="123 Main St" required>
                </div>

                <div class="form-group">
                  <label for="line2">Apt / Suite / Unit</label>
                  <input type="text" name="line2" id="line2" value="<?= htmlspecialchars($address['line2'] ?? '') ?>" placeholder="Apt 4B">
                </div>

                <div class="form-row">
                  <div>
                    <label for="city">City *</label>
                    <input type="text" name="city" id="city" value="<?= htmlspecialchars($address['city'] ?? '') ?>" placeholder="City" required>
                  </div>
                  <div>
                    <label for="state">State *</label>
                    <input type="text" name="state" id="state" value="<?= htmlspecialchars($address['state'] ?? '') ?>" placeholder="State" required>
                  </div>
                  <div>
                    <label for="zip">ZIP Code *</label>
                    <input type="text" name="zip" id="zip" value="<?= htmlspecialchars($address['zip'] ?? '') ?>" placeholder="ZIP" required>
                  </div>
                </div>

                <input type="hidden" name="country" value="US">

                <button type="submit" class="btn btn--navy" style="margin-top: 8px;">Save Address</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
  function logout() {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var fd = new FormData();
    fd.append('csrf_token', token);
    fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=logout', { method: 'POST', body: fd, credentials: 'include' })
      .then(function() { window.location.href = '<?= SHOP_URL ?>/gate/sign-in'; });
  }
  </script>
</body>
</html>
