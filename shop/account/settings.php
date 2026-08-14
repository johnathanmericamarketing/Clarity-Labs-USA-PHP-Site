<?php
/* ============================================================
   ClarityLabsUSA — Account Settings
   Edit name, email, phone, SMS preferences
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'Account Settings';

// Always pull fresh from API so SMS state reflects any STOP / opt-out activity
$api = new ClarityApiClient();
$me = $api->getMe(get_customer_token());
$customer = $me['data'] ?? get_customer();

$smsState = $customer['sms_opt_in_state'] ?? 'never_opted_in';
$smsCanReceive = !empty($customer['sms_can_receive']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    .settings-wrap { max-width: 720px; margin: 40px auto; padding: 20px; }
    .settings-card {
      background: var(--white);
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      margin-bottom: 24px;
    }
    .settings-card h2 {
      font-family: var(--font-display);
      font-size: 22px;
      color: var(--navy);
      margin-bottom: 6px;
    }
    .settings-card .desc {
      font-size: 13px;
      color: var(--gray-600);
      margin-bottom: 20px;
    }
    .form-row { margin-bottom: 16px; }
    .form-row label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-700);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }
    .form-row input[type="text"],
    .form-row input[type="email"],
    .form-row input[type="tel"] {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--gray-300);
      border-radius: 10px;
      font-size: 15px;
    }
    .sms-block {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px;
      margin-top: 8px;
    }
    .sms-state-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .sms-state-confirmed { background: #dcfce7; color: #166534; }
    .sms-state-pending   { background: #fef3c7; color: #92400e; }
    .sms-state-other     { background: #e5e7eb; color: #374151; }
    .checkbox-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-top: 12px;
    }
    .checkbox-row input[type="checkbox"] {
      margin-top: 3px;
      width: 18px;
      height: 18px;
    }
    .checkbox-row label {
      font-size: 13px;
      color: var(--gray-700);
      line-height: 1.4;
      cursor: pointer;
    }
    .btn-save {
      background: var(--navy);
      color: var(--white);
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-save:hover { background: #0f1f4a; }
    .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .links-row { font-size: 13px; margin-top: 16px; }
    .links-row a { color: var(--navy); margin-right: 16px; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <div class="settings-wrap">
    <h1 style="font-family: var(--font-display); color: var(--navy); margin-bottom: 8px;">Account Settings</h1>
    <p style="color: var(--gray-600); margin-bottom: 24px;">Update your contact info and notification preferences.</p>

    <div id="alertBox"></div>

    <form id="settingsForm" class="settings-card">
      <?= csrf_field() ?>
      <h2>Your Information</h2>
      <p class="desc">Make sure we have the right details so we can reach you about your orders.</p>

      <div class="form-row">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($customer['first_name'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($customer['last_name'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" placeholder="+1 555 123 4567">
      </div>

      <div class="sms-block">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <strong style="font-size: 14px; color: var(--navy);">SMS Notifications</strong>
          <span class="sms-state-badge sms-state-<?= in_array($smsState, ['confirmed','pending']) ? $smsState : 'other' ?>">
            <?php
              echo match($smsState) {
                'confirmed' => 'Opted In',
                'pending' => 'Pending Confirmation',
                'opted_out' => 'Opted Out',
                'expired' => 'Expired',
                default => 'Not Subscribed',
              };
            ?>
          </span>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="sms_opt_in" name="sms_opt_in" value="1" <?= ($smsState === 'confirmed' || $smsState === 'pending') ? 'checked' : '' ?>>
          <label for="sms_opt_in">
            Text me order updates (paid, shipped, out for delivery, delivered).
            Message frequency varies. Reply STOP at any time to opt out. Standard message and data rates may apply.
          </label>
        </div>
        <?php if ($smsState === 'pending'): ?>
          <div id="confirmBlock" style="margin-top: 14px; padding: 14px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px;">
            <div style="font-size: 13px; color: #92400e; margin-bottom: 10px;">
              <strong>One more step.</strong> To finalize SMS notifications, type <strong>CONFIRM</strong> below and click the button. We'll send a welcome text right after.
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
              <input type="text" id="confirmationText" placeholder="Type CONFIRM" autocomplete="off"
                     style="flex: 1; padding: 10px 12px; border: 1px solid #fdba74; border-radius: 8px; font-size: 14px; text-transform: uppercase;">
              <button type="button" id="confirmBtn" style="background: #ea580c; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Confirm</button>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div style="margin-top: 24px;">
        <button type="submit" class="btn-save">Save Changes</button>
      </div>

      <div class="links-row">
        <a href="<?= SHOP_URL ?>/account/change-password">Change password</a>
        <a href="<?= SHOP_URL ?>/account">Back to dashboard</a>
      </div>
    </form>
  </div>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
    document.getElementById('settingsForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const fd = new FormData(this);
      // Ensure unchecked checkbox sends "0" so server can see toggle off
      if (!fd.has('sms_opt_in')) fd.set('sms_opt_in', '0');

      const alertBox = document.getElementById('alertBox');
      alertBox.innerHTML = '';

      try {
        const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=update-profile', {
          method: 'POST',
          body: fd,
        });
        const data = await res.json();
        if (data.success) {
          alertBox.innerHTML = '<div class="alert alert-success">' + (data.message || 'Saved.') + '</div>';
          // Email changes need the customer to read the "check your new inbox"
          // message — don't wipe it with a quick reload
          setTimeout(() => location.reload(), data.email_change_pending ? 8000 : 1200);
        } else {
          let msg = data.error || 'Update failed.';
          if (data.errors) {
            msg += '<ul style="margin: 8px 0 0 20px;">';
            for (const k in data.errors) msg += '<li>' + data.errors[k].join(', ') + '</li>';
            msg += '</ul>';
          }
          alertBox.innerHTML = '<div class="alert alert-error">' + msg + '</div>';
        }
      } catch (err) {
        alertBox.innerHTML = '<div class="alert alert-error">Network error. Please try again.</div>';
      }
    });

    // SMS web double opt-in confirm handler
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) {
      confirmBtn.addEventListener('click', async function() {
        const text = (document.getElementById('confirmationText').value || '').trim();
        if (text.toUpperCase() !== 'CONFIRM') {
          document.getElementById('alertBox').innerHTML =
            '<div class="alert alert-error">Please type CONFIRM (in capital letters).</div>';
          return;
        }

        const fd = new FormData();
        fd.append('_csrf_token', '<?= htmlspecialchars(csrf_token()) ?>');
        fd.append('confirmation_text', text);

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Confirming...';

        try {
          const res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=sms-confirm', {
            method: 'POST',
            body: fd,
          });
          const data = await res.json();
          const alertBox = document.getElementById('alertBox');
          if (data.success) {
            alertBox.innerHTML = '<div class="alert alert-success">' + (data.message || 'Confirmed!') + '</div>';
            setTimeout(() => location.reload(), 1500);
          } else {
            alertBox.innerHTML = '<div class="alert alert-error">' + (data.error || 'Confirmation failed.') + '</div>';
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm';
          }
        } catch (err) {
          document.getElementById('alertBox').innerHTML =
            '<div class="alert alert-error">Network error. Please try again.</div>';
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Confirm';
        }
      });
    }
  </script>
</body>
</html>
