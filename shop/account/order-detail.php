<?php
/* ============================================================
   ClarityLabsUSA — Order Detail
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$orderId = (int) ($_GET['id'] ?? 0);
if (!$orderId) {
    header('Location: ' . SHOP_URL . '/account/orders');
    exit;
}

$api = new ClarityApiClient();
$response = $api->getOrder($orderId, get_customer_token());

$ok = ($response['status'] ?? null) === 'ok' || !empty($response['success']);
if (!$ok || empty($response['data'])) {
    header('Location: ' . SHOP_URL . '/account/orders');
    exit;
}

$order = $response['data'];
// Ops returns items as an already-decoded array under the 'items' key
$items = $order['items'] ?? (is_array($order['items_json'] ?? null) ? $order['items_json'] : []);
$shipments = $order['shipments'] ?? [];

$page_title = 'Order ' . ($order['order_number'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <main>
    <section style="padding: 60px 0 100px; min-height: 60vh;">
      <div class="container" style="max-width: 800px;">
        <a href="<?= SHOP_URL ?>/account/orders" style="color: var(--green); font-size: 14px; display: block; margin-bottom: 20px;">← Back to Orders</a>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
          <div>
            <h1 style="font-size: 28px;">Order <?= htmlspecialchars($order['order_number'] ?? '') ?></h1>
            <p style="color: var(--gray-400); font-size: 14px;">
              Placed on <?= isset($order['ordered_at']) ? date('F j, Y \a\t g:i A', strtotime($order['ordered_at'])) : '—' ?>
            </p>
          </div>
          <span class="order-status order-status--<?= strtolower($order['status'] ?? 'pending') ?>"
                style="display: inline-block; padding: 6px 16px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            <?= ucwords(str_replace('_', ' ', $order['status'] ?? 'pending')) ?>
          </span>
        </div>

        <!-- Payment Status Banner -->
        <?php if (in_array(($order['payment_status'] ?? ''), ['awaiting', 'pending'], true) && ($order['status'] ?? '') !== 'cancelled'): ?>
          <?php
            // 48h countdown math
            $orderedAt = isset($order['ordered_at']) ? strtotime($order['ordered_at']) : (isset($order['created_at']) ? strtotime($order['created_at']) : time());
            $deadlineTs = $orderedAt + (48 * 3600);
            $remainingSecs = max(0, $deadlineTs - time());
            $remainingHours = (int) floor($remainingSecs / 3600);
            $remainingMins = (int) floor(($remainingSecs % 3600) / 60);
            $expired = $remainingSecs <= 0;

            $zelleEmail = 'orders@claritylabsusa.com';      // TODO: pull from config
            $venmoHandle = 'claritylabsusa';                // bare handle
            $venmoDeepLink = 'https://venmo.com/' . $venmoHandle;
          ?>
          <div class="payment-box payment-box--awaiting">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
              <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #92400e; font-weight: 600;">
                  <?= $expired ? 'Payment Overdue' : 'Payment Required' ?>
                </div>
                <h3 style="font-size: 20px; color: #0B1E3F; margin: 4px 0 0;">
                  <?php if ($expired): ?>
                    This order is being cancelled
                  <?php else: ?>
                    Pay within <?= $remainingHours ?>h <?= $remainingMins ?>m
                  <?php endif; ?>
                </h3>
              </div>
              <?php if (!empty($order['invoice_url'])): ?>
                <a href="<?= htmlspecialchars($order['invoice_url']) ?>" target="_blank"
                   style="display: inline-block; padding: 10px 18px; background: #0B1E3F; color: #fff; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none;">
                  View Full Invoice (PDF)
                </a>
              <?php endif; ?>
            </div>

            <!-- Memo hero -->
            <div style="background: #fff; border: 3px solid #0B1E3F; border-radius: 12px; padding: 18px 20px; margin-bottom: 18px; text-align: center;">
              <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; margin-bottom: 6px;">
                Required Payment Memo
              </div>
              <div style="font-family: 'Courier New', monospace; font-size: 32px; font-weight: 700; color: #0B1E3F; letter-spacing: 3px;">
                <?= htmlspecialchars($order['order_number'] ?? '') ?>
              </div>
              <button type="button" onclick="copyMemo()" style="margin-top: 10px; padding: 6px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; cursor: pointer; color: #334155;">
                Copy memo
              </button>
              <div id="copy-feedback" style="display: none; font-size: 11px; color: #16a34a; margin-top: 6px;">Copied!</div>
              <p style="font-size: 11px; color: #991b1b; margin: 10px 0 0;">
                Without this exact memo, payments can't be matched automatically.
              </p>
            </div>

            <!-- Payment options grid -->
            <div class="pay-grid">
              <!-- ZELLE -->
              <div class="pay-card pay-card--zelle">
                <div class="pay-card__eyebrow">Option 1</div>
                <div class="pay-card__title">Zelle</div>
                <div class="pay-card__row">
                  <div class="pay-card__label">Send to</div>
                  <div class="pay-card__value pay-card__value--mono">
                    <span id="zelle-email"><?= htmlspecialchars($zelleEmail) ?></span>
                    <button type="button" onclick="copyText('<?= htmlspecialchars($zelleEmail) ?>', 'zelle-feedback')" class="pay-card__copy">Copy</button>
                  </div>
                </div>
                <div class="pay-card__row">
                  <div class="pay-card__label">Name</div>
                  <div class="pay-card__value">ClarityLabs USA</div>
                </div>
                <div class="pay-card__row">
                  <div class="pay-card__label">Amount</div>
                  <div class="pay-card__value"><strong>$<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></strong></div>
                </div>
                <div class="pay-card__row">
                  <div class="pay-card__label">Memo</div>
                  <div class="pay-card__value pay-card__value--mono"><?= htmlspecialchars($order['order_number'] ?? '') ?></div>
                </div>
                <div id="zelle-feedback" class="pay-card__feedback">Copied!</div>
                <div class="pay-card__steps">
                  <strong>Steps:</strong> Open your Chase app → tap <em>Pay & Transfer</em> → <em>Zelle</em> → send to the email above → enter the amount → type the memo.
                </div>
              </div>

              <!-- VENMO -->
              <div class="pay-card pay-card--venmo">
                <div class="pay-card__eyebrow">Option 2</div>
                <div class="pay-card__title">Venmo</div>
                <div style="display: flex; gap: 14px;">
                  <div style="flex: 1;">
                    <div class="pay-card__row">
                      <div class="pay-card__label">Send to</div>
                      <div class="pay-card__value pay-card__value--mono">@<?= htmlspecialchars($venmoHandle) ?></div>
                    </div>
                    <div class="pay-card__row">
                      <div class="pay-card__label">Amount</div>
                      <div class="pay-card__value"><strong>$<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></strong></div>
                    </div>
                    <div class="pay-card__row">
                      <div class="pay-card__label">Note</div>
                      <div class="pay-card__value pay-card__value--mono"><?= htmlspecialchars($order['order_number'] ?? '') ?></div>
                    </div>
                  </div>
                  <div style="width: 100px; text-align: center;">
                    <img src="/images/venmo-qr.png" alt="Venmo QR" style="width: 100px; height: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;">
                    <div style="font-size: 9px; color: #64748b; margin-top: 4px;">Scan to pay</div>
                  </div>
                </div>
                <a href="<?= htmlspecialchars($venmoDeepLink) ?>" target="_blank" class="pay-card__deeplink">
                  Open in Venmo →
                </a>
                <div class="pay-card__steps">
                  <strong>Steps:</strong> Tap "Open in Venmo" (mobile) or scan the QR → enter the amount → type the memo in the note field.
                </div>
              </div>
            </div>

            <!-- I've Paid button -->
            <div style="margin-top: 18px; padding-top: 18px; border-top: 1px solid #fbbf24; text-align: center;">
              <p style="font-size: 13px; color: #78350f; margin: 0 0 10px;">
                Already sent payment? Let us know so we can match it faster.
              </p>
              <button type="button" onclick="openPaidModal()" class="btn-ive-paid">
                I've Paid — Report It
              </button>
            </div>
          </div>

          <!-- I've Paid Modal -->
          <div id="paid-modal" class="paid-modal" style="display: none;">
            <div class="paid-modal__backdrop" onclick="closePaidModal()"></div>
            <div class="paid-modal__card">
              <h3 style="margin-bottom: 6px;">Report Your Payment</h3>
              <p style="font-size: 13px; color: #64748b; margin-bottom: 18px;">
                This puts your payment in our review queue so it's not missed. We'll confirm it as soon as we see it land.
              </p>

              <form id="paid-form">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int) ($order['id'] ?? 0) ?>">

                <div class="paid-form__row">
                  <label>Payment Method</label>
                  <select name="provider" required>
                    <option value="">— Select —</option>
                    <option value="zelle">Zelle</option>
                    <option value="venmo">Venmo</option>
                  </select>
                </div>

                <div class="paid-form__row">
                  <label>Confirmation / Reference Number <span style="color: #64748b; font-weight: 400;">(optional)</span></label>
                  <input type="text" name="reference" placeholder="e.g. Venmo transaction ID or Zelle confirmation code">
                </div>

                <div class="paid-form__row">
                  <label>Notes <span style="color: #64748b; font-weight: 400;">(optional)</span></label>
                  <textarea name="notes" rows="2" placeholder="Anything we should know..."></textarea>
                </div>

                <div id="paid-form-alert"></div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px;">
                  <button type="button" onclick="closePaidModal()" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Submit</button>
                </div>
              </form>
            </div>
          </div>

          <style>
            .payment-box--awaiting {
              background: linear-gradient(135deg, #fffbeb, #fef3c7);
              border: 2px solid #f59e0b;
              border-radius: 16px;
              padding: 24px;
              margin-bottom: 24px;
            }
            .pay-grid {
              display: grid;
              grid-template-columns: 1fr 1fr;
              gap: 14px;
            }
            @media (max-width: 720px) {
              .pay-grid { grid-template-columns: 1fr; }
            }
            .pay-card {
              background: #fff;
              border-radius: 12px;
              padding: 18px;
              border-left: 4px solid #6366f1;
              position: relative;
            }
            .pay-card--zelle { border-left-color: #6D28D9; }
            .pay-card--venmo { border-left-color: #3D95CE; }
            .pay-card__eyebrow {
              font-size: 10px;
              text-transform: uppercase;
              letter-spacing: 1px;
              color: #64748b;
            }
            .pay-card__title {
              font-size: 20px;
              font-weight: 700;
              color: #0B1E3F;
              margin-bottom: 10px;
            }
            .pay-card__row { margin-bottom: 8px; font-size: 12px; }
            .pay-card__label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
            .pay-card__value { color: #0B1E3F; font-size: 13px; }
            .pay-card__value--mono { font-family: 'Courier New', monospace; font-weight: 600; }
            .pay-card__copy {
              display: inline-block; margin-left: 6px; padding: 2px 8px;
              background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca;
              border-radius: 4px; font-size: 10px; cursor: pointer;
              font-family: inherit;
            }
            .pay-card__copy:hover { background: #e0e7ff; }
            .pay-card__feedback {
              display: none; font-size: 11px; color: #16a34a;
              margin-top: 4px; text-align: right;
            }
            .pay-card__deeplink {
              display: inline-block; margin-top: 12px; padding: 10px 16px;
              background: #3D95CE; color: #fff; border-radius: 8px;
              font-size: 13px; font-weight: 600; text-decoration: none;
              width: 100%; text-align: center;
            }
            .pay-card__steps {
              margin-top: 12px; padding-top: 10px; border-top: 1px solid #e2e8f0;
              font-size: 11px; color: #64748b; line-height: 1.4;
            }
            .btn-ive-paid {
              padding: 12px 28px; background: #0B1E3F; color: #fff;
              border: none; border-radius: 10px; font-size: 14px;
              font-weight: 600; cursor: pointer;
            }
            .btn-ive-paid:hover { background: #0f1f4a; }
            .paid-modal {
              position: fixed; top: 0; left: 0; right: 0; bottom: 0;
              z-index: 9999; display: flex; align-items: center; justify-content: center;
            }
            .paid-modal__backdrop {
              position: absolute; top: 0; left: 0; right: 0; bottom: 0;
              background: rgba(11, 30, 63, 0.6);
            }
            .paid-modal__card {
              position: relative; background: #fff; border-radius: 16px;
              padding: 28px; max-width: 480px; width: calc(100% - 40px);
              box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            }
            .paid-form__row { margin-bottom: 14px; }
            .paid-form__row label {
              display: block; font-size: 12px; font-weight: 600; color: #334155;
              margin-bottom: 4px;
            }
            .paid-form__row input, .paid-form__row select, .paid-form__row textarea {
              width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1;
              border-radius: 8px; font-size: 14px; font-family: inherit;
            }
            .btn-primary {
              padding: 10px 20px; background: #0B1E3F; color: #fff;
              border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
            }
            .btn-secondary {
              padding: 10px 20px; background: #f1f5f9; color: #334155;
              border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; cursor: pointer;
            }
            .alert-success {
              background: #dcfce7; color: #166534; padding: 10px 14px;
              border-radius: 8px; font-size: 13px; margin: 10px 0;
            }
            .alert-error {
              background: #fee2e2; color: #991b1b; padding: 10px 14px;
              border-radius: 8px; font-size: 13px; margin: 10px 0;
            }
          </style>

          <script>
            function copyText(text, feedbackId) {
              navigator.clipboard.writeText(text).then(function() {
                var el = document.getElementById(feedbackId);
                if (el) { el.style.display = 'block'; setTimeout(function() { el.style.display = 'none'; }, 1800); }
              });
            }
            function copyMemo() {
              navigator.clipboard.writeText('<?= htmlspecialchars($order['order_number'] ?? '') ?>').then(function() {
                var el = document.getElementById('copy-feedback');
                if (el) { el.style.display = 'block'; setTimeout(function() { el.style.display = 'none'; }, 1800); }
              });
            }
            function openPaidModal() {
              document.getElementById('paid-modal').style.display = 'flex';
            }
            function closePaidModal() {
              document.getElementById('paid-modal').style.display = 'none';
              document.getElementById('paid-form-alert').innerHTML = '';
            }
            document.getElementById('paid-form').addEventListener('submit', async function(e) {
              e.preventDefault();
              var alertBox = document.getElementById('paid-form-alert');
              alertBox.innerHTML = '';
              var fd = new FormData(this);
              try {
                var res = await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=mark-order-paid', {
                  method: 'POST',
                  body: fd,
                });
                var data = await res.json();
                if (data.success) {
                  alertBox.innerHTML = '<div class="alert-success">' + (data.message || 'Payment reported.') + '</div>';
                  setTimeout(function() { location.reload(); }, 1800);
                } else {
                  alertBox.innerHTML = '<div class="alert-error">' + (data.error || 'Failed to record payment.') + '</div>';
                }
              } catch (err) {
                alertBox.innerHTML = '<div class="alert-error">Network error. Please try again.</div>';
              }
            });
          </script>

        <?php elseif (($order['payment_status'] ?? '') === 'paid'): ?>
          <div style="background: linear-gradient(135deg, #d1fae5, #ecfdf5); border: 1px solid #10b981; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <h3 style="font-size: 16px; color: #065f46; margin-bottom: 2px;">✓ Payment Received</h3>
                <p style="font-size: 13px; color: #047857; margin: 0;">
                  Payment confirmed via <?= ucfirst(htmlspecialchars($order['payment_method'] ?? 'payment')) ?>.
                  <?php if (($order['status'] ?? '') === 'paid'): ?>Your order is being prepared.<?php endif; ?>
                </p>
              </div>
              <?php if (!empty($order['invoice_url'])): ?>
                <a href="<?= htmlspecialchars($order['invoice_url']) ?>" target="_blank"
                   style="color: #065f46; font-size: 13px; font-weight: 500; text-decoration: none;">
                  📄 Invoice
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Items -->
        <div style="background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; padding: 24px; margin-bottom: 20px;">
          <h3 style="font-size: 16px; margin-bottom: 16px;">Items</h3>
          <?php foreach ($items as $item): ?>
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--rule);">
              <div>
                <strong style="color: var(--navy);"><?= htmlspecialchars($item['name'] ?? '') ?></strong>
                <span style="color: var(--gray-400); font-size: 13px;"> × <?= $item['qty'] ?? 1 ?></span>
              </div>
              <div style="color: var(--navy); font-weight: 500;">
                $<?= number_format(($item['unit_price'] ?? 0) * ($item['qty'] ?? 1), 2) ?>
              </div>
            </div>
          <?php endforeach; ?>

          <div style="margin-top: 16px; padding-top: 12px;">
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px;">
              <span>Subtotal</span>
              <span>$<?= number_format($order['subtotal'] ?? 0, 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px;">
              <span>Shipping</span>
              <span>$<?= number_format($order['shipping_amount'] ?? 0, 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px;">
              <span>Tax</span>
              <span>$<?= number_format($order['tax_amount'] ?? 0, 2) ?></span>
            </div>
            <?php if (($order['discount_amount'] ?? 0) > 0): ?>
              <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px; color: #059669;">
                <span>Discount</span>
                <span>-$<?= number_format($order['discount_amount'], 2) ?></span>
              </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 600; color: var(--navy); padding-top: 8px; margin-top: 8px; border-top: 2px solid var(--rule);">
              <span>Total</span>
              <span>$<?= number_format($order['total_amount'] ?? 0, 2) ?></span>
            </div>
          </div>
        </div>

        <!-- Shipping -->
        <div style="background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; padding: 24px; margin-bottom: 20px;">
          <h3 style="font-size: 16px; margin-bottom: 12px;">Shipping Address</h3>
          <p style="font-size: 14px; color: var(--gray-600); line-height: 1.6;">
            <?= htmlspecialchars($order['shipping_name'] ?? '') ?><br>
            <?= htmlspecialchars($order['shipping_address_line1'] ?? '') ?><br>
            <?php if (!empty($order['shipping_address_line2'])): ?>
              <?= htmlspecialchars($order['shipping_address_line2']) ?><br>
            <?php endif; ?>
            <?= htmlspecialchars($order['shipping_city'] ?? '') ?>,
            <?= htmlspecialchars($order['shipping_state'] ?? '') ?>
            <?= htmlspecialchars($order['shipping_zip'] ?? '') ?>
          </p>
        </div>

        <!-- Tracking -->
        <?php if (!empty($shipments)): ?>
          <div style="background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; padding: 24px; margin-bottom: 20px;">
            <h3 style="font-size: 16px; margin-bottom: 12px;">Tracking</h3>
            <?php foreach ($shipments as $shipment): ?>
              <div style="margin-bottom: 12px;">
                <strong style="color: var(--navy);"><?= htmlspecialchars($shipment['carrier'] ?? '') ?> <?= htmlspecialchars($shipment['service'] ?? '') ?></strong>
                <?php if (!empty($shipment['tracking_number'])): ?>
                  <br>
                  <a href="<?= htmlspecialchars($shipment['public_tracking_url'] ?? $shipment['tracking_url'] ?? '#') ?>"
                     target="_blank" style="color: var(--green); font-weight: 500;">
                    <?= htmlspecialchars($shipment['tracking_number']) ?> →
                  </a>
                <?php endif; ?>
                <?php if (!empty($shipment['est_delivery_date'])): ?>
                  <br>
                  <span style="font-size: 13px; color: var(--gray-400);">
                    Est. delivery: <?= date('M j, Y', strtotime($shipment['est_delivery_date'])) ?>
                  </span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Need Help? -->
        <div style="text-align: center; padding: 20px 0;">
          <p style="color: var(--gray-400); font-size: 14px;">
            Questions about this order?
            <a href="<?= SHOP_URL ?>/support/?subject=Order+<?= urlencode($order['order_number'] ?? '') ?>" style="color: var(--green); font-weight: 500;">Contact Support</a>
          </p>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
