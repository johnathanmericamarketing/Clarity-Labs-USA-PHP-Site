<?php
/* ============================================================
   ClarityLabsUSA — Saved Addresses (Shipping + Billing)
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

// Fetch fresh profile with both addresses
$api = new ClarityApiClient();
$profileResponse = $api->getMe(get_customer_token());
$profile = $profileResponse['data'] ?? [];
$shipping = $profile['shipping_address'] ?? [];
$billing  = $profile['billing_address'] ?? [];

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $sameAsShipping = !empty($_POST['same_as_shipping']);

    $updateData = [
        'shipping_address_line1' => trim($_POST['shipping_line1'] ?? ''),
        'shipping_address_line2' => trim($_POST['shipping_line2'] ?? ''),
        'shipping_city'          => trim($_POST['shipping_city'] ?? ''),
        'shipping_state'         => trim($_POST['shipping_state'] ?? ''),
        'shipping_zip'           => trim($_POST['shipping_zip'] ?? ''),
        'shipping_country'       => trim($_POST['shipping_country'] ?? 'US'),
    ];

    if ($sameAsShipping) {
        $updateData['billing_address_line1'] = $updateData['shipping_address_line1'];
        $updateData['billing_address_line2'] = $updateData['shipping_address_line2'];
        $updateData['billing_city']          = $updateData['shipping_city'];
        $updateData['billing_state']         = $updateData['shipping_state'];
        $updateData['billing_zip']           = $updateData['shipping_zip'];
        $updateData['billing_country']       = $updateData['shipping_country'];
    } else {
        $updateData['billing_address_line1'] = trim($_POST['billing_line1'] ?? '');
        $updateData['billing_address_line2'] = trim($_POST['billing_line2'] ?? '');
        $updateData['billing_city']          = trim($_POST['billing_city'] ?? '');
        $updateData['billing_state']         = trim($_POST['billing_state'] ?? '');
        $updateData['billing_zip']           = trim($_POST['billing_zip'] ?? '');
        $updateData['billing_country']       = trim($_POST['billing_country'] ?? 'US');
    }

    if (
        empty($updateData['shipping_address_line1']) || empty($updateData['shipping_city']) ||
        empty($updateData['shipping_state']) || empty($updateData['shipping_zip'])
    ) {
        $error = 'Please fill in all required shipping address fields.';
    } elseif (
        !$sameAsShipping && (
            empty($updateData['billing_address_line1']) || empty($updateData['billing_city']) ||
            empty($updateData['billing_state']) || empty($updateData['billing_zip'])
        )
    ) {
        $error = 'Please fill in all required billing address fields (or check "same as shipping").';
    } else {
        $result = $api->updateProfile($updateData, get_customer_token());
        $ok = ($result['status'] ?? null) === 'ok' || !empty($result['success']);
        if ($ok) {
            $success = 'Addresses updated successfully.';
            // Re-pull for display
            $profileResponse = $api->getMe(get_customer_token());
            $profile = $profileResponse['data'] ?? [];
            $shipping = $profile['shipping_address'] ?? [];
            $billing  = $profile['billing_address'] ?? [];
        } else {
            $error = $result['message'] ?? 'Failed to update address. Please try again.';
        }
    }
}

// Auto-detect if shipping and billing currently match (for initial checkbox state)
$addressesMatch = !empty($shipping['line1']) && (
    ($shipping['line1'] ?? '') === ($billing['line1'] ?? '') &&
    ($shipping['city'] ?? '') === ($billing['city'] ?? '') &&
    ($shipping['state'] ?? '') === ($billing['state'] ?? '') &&
    ($shipping['zip'] ?? '') === ($billing['zip'] ?? '')
);
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
    .account-card h3 { font-size: 18px; margin-bottom: 4px; }
    .account-card .card-sub { color: var(--gray-600); font-size: 13px; margin-bottom: 16px; }
    .addr-form label { display: block; font-size: 12px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    .addr-form input { width: 100%; height: 42px; border: 1px solid var(--rule); border-radius: 8px; padding: 0 12px; font-size: 14px; color: var(--navy); background: var(--white); outline: none; transition: border-color .2s; }
    .addr-form input:focus { border-color: var(--green); }
    .addr-form .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .addr-form .form-group { margin-bottom: 16px; }
    .same-as-row { display: flex; align-items: center; gap: 10px; padding: 14px 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; margin: 10px 0 20px; }
    .same-as-row input[type="checkbox"] { width: 18px; height: 18px; }
    .same-as-row label { margin: 0 !important; font-size: 14px !important; color: var(--navy) !important; text-transform: none !important; letter-spacing: normal !important; font-weight: 500 !important; cursor: pointer; }
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
          <p>Manage your shipping and billing addresses.</p>
        </div>

        <div class="account__grid">
          <!-- Sidebar -->
          <nav class="account-nav">
            <a href="<?= SHOP_URL ?>/account/">Dashboard</a>
            <a href="<?= SHOP_URL ?>/account/orders">Order History</a>
            <a href="<?= SHOP_URL ?>/account/addresses" class="active">Addresses</a>
            <a href="<?= SHOP_URL ?>/account/wishlist">Saved Products</a>
            <a href="<?= SHOP_URL ?>/account/settings">Settings</a>
            <a href="<?= SHOP_URL ?>/account/support">Support</a>
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

            <form method="POST" class="addr-form">
              <?= csrf_field() ?>

              <!-- SHIPPING -->
              <div class="account-card">
                <h3>Shipping Address</h3>
                <div class="card-sub">Where we send your orders.</div>

                <div class="form-group">
                  <label for="shipping_line1">Street Address *</label>
                  <input type="text" name="shipping_line1" id="shipping_line1" value="<?= htmlspecialchars($shipping['line1'] ?? '') ?>" placeholder="123 Main St" required>
                </div>

                <div class="form-group">
                  <label for="shipping_line2">Apt / Suite / Unit</label>
                  <input type="text" name="shipping_line2" id="shipping_line2" value="<?= htmlspecialchars($shipping['line2'] ?? '') ?>" placeholder="Apt 4B">
                </div>

                <div class="form-row">
                  <div>
                    <label for="shipping_city">City *</label>
                    <input type="text" name="shipping_city" id="shipping_city" value="<?= htmlspecialchars($shipping['city'] ?? '') ?>" placeholder="City" required>
                  </div>
                  <div>
                    <label for="shipping_state">State *</label>
                    <input type="text" name="shipping_state" id="shipping_state" value="<?= htmlspecialchars($shipping['state'] ?? '') ?>" placeholder="State" required>
                  </div>
                  <div>
                    <label for="shipping_zip">ZIP Code *</label>
                    <input type="text" name="shipping_zip" id="shipping_zip" value="<?= htmlspecialchars($shipping['zip'] ?? '') ?>" placeholder="ZIP" required>
                  </div>
                </div>

                <input type="hidden" name="shipping_country" value="US">
              </div>

              <!-- Same as shipping toggle -->
              <div class="same-as-row">
                <input type="checkbox" name="same_as_shipping" id="same_as_shipping" value="1" <?= ($addressesMatch || empty($billing['line1'])) ? 'checked' : '' ?> onchange="toggleBilling()">
                <label for="same_as_shipping">Billing address is the same as shipping</label>
              </div>

              <!-- BILLING (hidden when same-as is checked) -->
              <div class="account-card" id="billing-section" style="<?= ($addressesMatch || empty($billing['line1'])) ? 'display: none;' : '' ?>">
                <h3>Billing Address</h3>
                <div class="card-sub">Used on invoices and for payment verification.</div>

                <div class="form-group">
                  <label for="billing_line1">Street Address *</label>
                  <input type="text" name="billing_line1" id="billing_line1" value="<?= htmlspecialchars($billing['line1'] ?? '') ?>" placeholder="123 Main St">
                </div>

                <div class="form-group">
                  <label for="billing_line2">Apt / Suite / Unit</label>
                  <input type="text" name="billing_line2" id="billing_line2" value="<?= htmlspecialchars($billing['line2'] ?? '') ?>" placeholder="Apt 4B">
                </div>

                <div class="form-row">
                  <div>
                    <label for="billing_city">City *</label>
                    <input type="text" name="billing_city" id="billing_city" value="<?= htmlspecialchars($billing['city'] ?? '') ?>" placeholder="City">
                  </div>
                  <div>
                    <label for="billing_state">State *</label>
                    <input type="text" name="billing_state" id="billing_state" value="<?= htmlspecialchars($billing['state'] ?? '') ?>" placeholder="State">
                  </div>
                  <div>
                    <label for="billing_zip">ZIP Code *</label>
                    <input type="text" name="billing_zip" id="billing_zip" value="<?= htmlspecialchars($billing['zip'] ?? '') ?>" placeholder="ZIP">
                  </div>
                </div>

                <input type="hidden" name="billing_country" value="US">
              </div>

              <button type="submit" class="btn btn--navy" style="margin-top: 8px;">Save Addresses</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
    function toggleBilling() {
      var box = document.getElementById('same_as_shipping');
      var section = document.getElementById('billing-section');
      section.style.display = box.checked ? 'none' : 'block';
    }

    function logout() {
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
      var fd = new FormData();
      fd.append('_csrf_token', token);
      fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=logout', { method: 'POST', body: fd, credentials: 'include' })
        .then(function() { window.location.href = '<?= SHOP_URL ?>/gate/sign-in'; });
    }
  </script>
</body>
</html>
