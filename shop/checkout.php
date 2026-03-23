<?php
/* ============================================================
   ClarityLabsUSA — Checkout
   Multi-step: Shipping → Review → Confirmation
   Invoice-based payment (Zelle/Venmo/ACH/Check)
   ============================================================ */

$base_path = '../';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';

access_guard();

// Redirect if cart is empty
if (cart_is_empty()) {
    header('Location: ' . SHOP_URL . '/cart');
    exit;
}

$page_title = 'Checkout';
$customer = get_customer();
$items = cart_items();
$subtotal = cart_subtotal();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    .checkout { padding: 40px 0 100px; min-height: 70vh; }

    .checkout__layout {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 48px;
      align-items: start;
    }

    /* Steps */
    .checkout-steps {
      display: flex;
      gap: 0;
      margin-bottom: 32px;
    }

    .checkout-step {
      flex: 1;
      text-align: center;
      padding: 12px;
      font-size: 13px;
      font-weight: 600;
      color: var(--gray-400);
      border-bottom: 3px solid var(--gray-200);
      transition: all 0.2s;
    }

    .checkout-step.active {
      color: var(--green);
      border-color: var(--green);
    }

    .checkout-step.completed {
      color: var(--navy);
      border-color: var(--navy);
    }

    /* Panels */
    .checkout-panel { display: none; }
    .checkout-panel.active { display: block; }

    .checkout-panel h2 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    /* Form */
    .checkout-form__group { margin-bottom: 16px; }

    .checkout-form__label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--navy);
      margin-bottom: 4px;
    }

    .checkout-form__input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      color: var(--navy);
      transition: border-color 0.2s;
    }

    .checkout-form__input:focus {
      outline: none;
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(26, 122, 110, 0.1);
    }

    .checkout-form__row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .checkout-form__row--3 {
      grid-template-columns: 2fr 1fr 1fr;
    }

    /* Shipping Rates */
    .shipping-rates { margin: 20px 0; }

    .shipping-rate {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 16px;
      border: 2px solid var(--gray-200);
      border-radius: 8px;
      margin-bottom: 8px;
      cursor: pointer;
      transition: border-color 0.15s;
    }

    .shipping-rate:hover,
    .shipping-rate.selected { border-color: var(--green); }

    .shipping-rate__info {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .shipping-rate__info input { accent-color: var(--green); }
    .shipping-rate__price { font-weight: 600; color: var(--navy); }
    .shipping-rate__price.free { color: var(--green); }
    .shipping-rate__est { font-size: 12px; color: var(--gray-400); }

    /* Payment Info Box */
    .payment-info-box {
      background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
      border: 1px solid #99f6e4;
      border-radius: 12px;
      padding: 20px;
      margin: 20px 0;
    }

    .payment-info-box h4 {
      color: var(--green);
      font-size: 15px;
      margin-bottom: 12px;
    }

    .payment-info-box p {
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.6;
      margin-bottom: 8px;
    }

    /* Buttons */
    .checkout-btn {
      display: inline-block;
      padding: 14px 32px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      cursor: pointer;
      transition: transform 0.2s;
      margin-top: 16px;
    }

    .checkout-btn:hover { transform: translateY(-2px); }
    .checkout-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .checkout-back {
      background: none;
      border: none;
      color: var(--gray-400);
      font-size: 14px;
      cursor: pointer;
      margin-right: 16px;
    }

    /* Summary sidebar */
    .checkout-summary {
      background: var(--gray-50);
      border: 1px solid var(--rule);
      border-radius: 16px;
      padding: 24px;
      position: sticky;
      top: 100px;
    }

    .checkout-summary h3 {
      font-size: 18px;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--rule);
    }

    .checkout-summary__item {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .checkout-summary__item .name {
      color: var(--navy);
      max-width: 200px;
    }

    .checkout-summary__item .qty {
      color: var(--gray-400);
      font-size: 12px;
    }

    .checkout-summary__row {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      padding: 4px 0;
    }

    .checkout-summary__total {
      display: flex;
      justify-content: space-between;
      font-size: 18px;
      font-weight: 600;
      color: var(--navy);
      padding-top: 12px;
      margin-top: 12px;
      border-top: 2px solid var(--rule);
    }

    .checkout-message {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .checkout-message--error {
      background: #FEE2E2;
      color: #991B1B;
      border: 1px solid #FECACA;
    }

    .free-shipping-banner {
      background: linear-gradient(135deg, #0d9488, #0f766e);
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 16px;
    }

    @media (max-width: 768px) {
      .checkout__layout { grid-template-columns: 1fr; }
      .checkout-form__row, .checkout-form__row--3 { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <section class="checkout">
      <div class="container">
        <h1 style="font-size: 32px; margin-bottom: 8px;">Checkout</h1>

        <!-- Steps -->
        <div class="checkout-steps">
          <div class="checkout-step active" data-step="1">1. Shipping</div>
          <div class="checkout-step" data-step="2">2. Review & Confirm</div>
          <div class="checkout-step" data-step="3">3. Order Placed</div>
        </div>

        <div id="checkout-error"></div>

        <div class="checkout__layout">
          <!-- Form Panels -->
          <div>
            <!-- Step 1: Shipping -->
            <div class="checkout-panel active" id="panel-1">
              <h2>Shipping Address</h2>
              <form id="shipping-form">
                <div class="checkout-form__row">
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">First Name</label>
                    <input type="text" name="shipping_first" class="checkout-form__input"
                           value="<?= htmlspecialchars($customer['first_name'] ?? '') ?>" required>
                  </div>
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">Last Name</label>
                    <input type="text" name="shipping_last" class="checkout-form__input"
                           value="<?= htmlspecialchars($customer['last_name'] ?? '') ?>" required>
                  </div>
                </div>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Street Address</label>
                  <input type="text" name="shipping_address" class="checkout-form__input" placeholder="123 Main St" required>
                </div>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Apartment, Suite, etc. (optional)</label>
                  <input type="text" name="shipping_address2" class="checkout-form__input" placeholder="Apt 4B">
                </div>

                <div class="checkout-form__row--3" style="display: grid; gap: 12px;">
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">City</label>
                    <input type="text" name="shipping_city" class="checkout-form__input" required>
                  </div>
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">State</label>
                    <input type="text" name="shipping_state" class="checkout-form__input" placeholder="OR" maxlength="2" required>
                  </div>
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">ZIP Code</label>
                    <input type="text" name="shipping_zip" class="checkout-form__input" placeholder="97239" required>
                  </div>
                </div>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Phone (for shipping updates)</label>
                  <input type="tel" name="shipping_phone" class="checkout-form__input"
                         value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" placeholder="(555) 123-4567">
                </div>

                <!-- Shipping Rates -->
                <div id="shipping-rates-container" style="display: none;">
                  <h3 style="font-size: 18px; margin: 24px 0 12px;">Shipping Method</h3>
                  <div id="shipping-rates"></div>
                </div>

                <div style="margin-top: 24px;">
                  <button type="button" class="checkout-btn" id="btn-to-review" onclick="getShippingRates()">
                    Continue to Review
                  </button>
                </div>
              </form>
            </div>

            <!-- Step 2: Review & Confirm -->
            <div class="checkout-panel" id="panel-2">
              <h2>Review Your Order</h2>

              <!-- Shipping Summary -->
              <div style="background: var(--gray-50); border-radius: 10px; padding: 16px; margin-bottom: 20px; border: 1px solid var(--rule);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <strong style="font-size: 14px;">Ship To</strong>
                  <button type="button" class="checkout-back" onclick="goToStep(1)" style="margin: 0;">Edit</button>
                </div>
                <div id="review-shipping-address" style="font-size: 13px; color: var(--gray-600); line-height: 1.6;"></div>
              </div>

              <!-- Payment Info -->
              <div class="payment-info-box">
                <h4>💳 How Payment Works</h4>
                <p>After placing your order, you'll receive an <strong>invoice by email</strong> with payment instructions. We accept:</p>
                <p>
                  <strong>Zelle</strong> · <strong>Venmo</strong> · <strong>ACH Bank Transfer</strong> · <strong>Check</strong>
                </p>
                <p style="font-size: 12px; color: var(--gray-500); margin-bottom: 0;">
                  Your order will be processed and shipped once payment is confirmed. Payment details are included in the invoice.
                </p>
              </div>

              <div class="checkout-form__checkbox" style="display: flex; gap: 10px; margin: 20px 0;">
                <input type="checkbox" id="agree-terms" required>
                <label for="agree-terms" style="font-size: 13px; color: var(--gray-600); line-height: 1.5;">
                  I agree to the <a href="<?= SITE_URL ?>/terms" style="color: var(--green);">Terms of Service</a>,
                  <a href="<?= SITE_URL ?>/refund" style="color: var(--green);">Refund Policy</a>,
                  and confirm that all products are for <strong>research use only</strong>.
                </label>
              </div>

              <div style="margin-top: 24px;">
                <button type="button" class="checkout-back" onclick="goToStep(1)">← Back to Shipping</button>
                <button type="button" class="checkout-btn" id="btn-place-order" onclick="placeOrder()">
                  Place Order — $<span id="order-total-btn"><?= number_format($subtotal, 2) ?></span>
                </button>
              </div>
            </div>

            <!-- Step 3: Order Placed -->
            <div class="checkout-panel" id="panel-3">
              <div style="text-align: center; padding: 40px 0;">
                <div style="font-size: 64px; margin-bottom: 16px;">✓</div>
                <h2 style="color: var(--green); margin-bottom: 8px;">Order Placed!</h2>
                <p style="color: var(--gray-600); margin-bottom: 4px;">
                  Your order <strong id="order-number"></strong> has been received.
                </p>
                <p style="color: var(--gray-600); margin-bottom: 24px;">
                  An invoice with payment instructions has been sent to <strong id="order-email"></strong>.
                </p>

                <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 20px; text-align: left; max-width: 440px; margin: 0 auto 32px;">
                  <h4 style="color: var(--green); font-size: 14px; margin-bottom: 10px;">What's Next?</h4>
                  <ol style="font-size: 13px; color: var(--gray-600); line-height: 2; padding-left: 20px; margin: 0;">
                    <li>Check your email for the invoice with payment details</li>
                    <li>Submit payment via Zelle, Venmo, ACH, or Check</li>
                    <li>We'll confirm payment and ship your order</li>
                    <li>You'll receive tracking info by email</li>
                  </ol>
                </div>

                <a href="<?= SHOP_URL ?>/account/orders" class="checkout-btn" style="text-decoration: none; margin-right: 8px;">View My Orders</a>
                <a href="<?= SHOP_URL ?>/" class="checkout-btn" style="text-decoration: none; background: var(--navy);">Continue Shopping</a>
              </div>
            </div>
          </div>

          <!-- Order Summary Sidebar -->
          <div class="checkout-summary" id="order-summary">
            <h3>Order Summary</h3>

            <div class="free-shipping-banner">✓ Free Standard Shipping</div>

            <?php foreach ($items as $item): ?>
              <div class="checkout-summary__item">
                <div>
                  <div class="name"><?= htmlspecialchars($item['name']) ?> <span class="qty">&times;<?= $item['qty'] ?></span></div>
                </div>
                <div>$<?= number_format($item['price'] * $item['qty'], 2) ?></div>
              </div>
            <?php endforeach; ?>

            <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--rule);">
              <div class="checkout-summary__row">
                <span>Subtotal</span>
                <span>$<?= number_format($subtotal, 2) ?></span>
              </div>
              <div class="checkout-summary__row">
                <span>Shipping</span>
                <span id="summary-shipping" style="color: var(--green); font-weight: 600;">FREE</span>
              </div>
              <div class="checkout-summary__row" id="summary-shipping-upgrade-row" style="display: none;">
                <span>Upgrade</span>
                <span id="summary-shipping-upgrade">—</span>
              </div>
            </div>
            <div class="checkout-summary__total">
              <span>Total</span>
              <span id="summary-total">$<?= number_format($subtotal, 2) ?></span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  let selectedShippingRate = null;
  let shippingAmount = 0;
  const subtotal = <?= $subtotal ?>;

  function goToStep(step) {
    document.querySelectorAll('.checkout-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.checkout-step').forEach(s => {
      const sStep = parseInt(s.dataset.step);
      s.classList.remove('active', 'completed');
      if (sStep < step) s.classList.add('completed');
      if (sStep === step) s.classList.add('active');
    });
    document.getElementById('panel-' + step).classList.add('active');

    // Populate review address on step 2 (use textContent to prevent XSS)
    if (step === 2) {
      const form = document.getElementById('shipping-form');
      const fd = new FormData(form);
      const addrEl = document.getElementById('review-shipping-address');
      addrEl.textContent = '';
      const lines = [
        fd.get('shipping_first') + ' ' + fd.get('shipping_last'),
        fd.get('shipping_address'),
      ];
      if (fd.get('shipping_address2')) lines.push(fd.get('shipping_address2'));
      lines.push(fd.get('shipping_city') + ', ' + fd.get('shipping_state') + ' ' + fd.get('shipping_zip'));
      lines.forEach(function(line, i) {
        addrEl.appendChild(document.createTextNode(line));
        if (i < lines.length - 1) addrEl.appendChild(document.createElement('br'));
      });
    }
  }

  function showError(msg) {
    const el = document.getElementById('checkout-error');
    el.innerHTML = '';
    const errDiv = document.createElement('div');
    errDiv.className = 'checkout-message checkout-message--error';
    errDiv.textContent = msg;
    el.appendChild(errDiv);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateTotal() {
    const total = subtotal + shippingAmount;
    document.getElementById('summary-total').textContent = '$' + total.toFixed(2);
    document.getElementById('order-total-btn').textContent = total.toFixed(2);

    if (shippingAmount > 0) {
      document.getElementById('summary-shipping-upgrade-row').style.display = 'flex';
      document.getElementById('summary-shipping-upgrade').textContent = '+$' + shippingAmount.toFixed(2);
    } else {
      document.getElementById('summary-shipping-upgrade-row').style.display = 'none';
    }
  }

  function selectRate(el, price, rateId) {
    document.querySelectorAll('.shipping-rate').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
    selectedShippingRate = rateId;
    shippingAmount = parseFloat(price);
    updateTotal();
  }

  async function getShippingRates() {
    const form = document.getElementById('shipping-form');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const btn = document.getElementById('btn-to-review');
    btn.disabled = true;
    btn.textContent = 'Getting shipping rates...';

    const fd = new FormData(form);
    fd.append('shipping_name', fd.get('shipping_first') + ' ' + fd.get('shipping_last'));
    fd.append('_csrf_token', csrfToken);

    try {
      const res = await fetch('<?= SHOP_URL ?>/php/checkout-actions.php?action=shipping-rates', {
        method: 'POST',
        body: fd,
      });
      const data = await res.json();

      if (data.status === 'ok' && data.tiers && data.tiers.length > 0) {
        const container = document.getElementById('shipping-rates');
        container.innerHTML = '';

        data.tiers.forEach((tier, idx) => {
          const isDefault = idx === 0;
          const div = document.createElement('div');
          div.className = 'shipping-rate' + (isDefault ? ' selected' : '');
          div.onclick = function() { selectRate(this, tier.customer_price, tier.rate_id); };
          div.innerHTML = `
            <div class="shipping-rate__info">
              <input type="radio" name="shipping_tier" value="${tier.rate_id}" ${isDefault ? 'checked' : ''}>
              <div>
                <div style="font-weight: 500;">${tier.label}</div>
                <div class="shipping-rate__est">${tier.carrier}${tier.est_delivery_days ? ' · Est. ' + tier.est_delivery_days + ' days' : ''}</div>
              </div>
            </div>
            <div class="shipping-rate__price ${tier.customer_price === 0 ? 'free' : ''}">${tier.display_price}</div>
          `;
          container.appendChild(div);

          if (isDefault) {
            selectedShippingRate = tier.rate_id;
            shippingAmount = tier.customer_price;
          }
        });

        document.getElementById('shipping-rates-container').style.display = 'block';
        updateTotal();

        // Change button to go to step 2
        btn.textContent = 'Continue to Review';
        btn.disabled = false;
        btn.onclick = function() { goToStep(2); };
      } else {
        // No rates returned — default to free shipping and continue
        shippingAmount = 0;
        selectedShippingRate = 'free_standard';
        updateTotal();
        goToStep(2);
      }
    } catch (err) {
      // If rate fetch fails, continue with free shipping
      shippingAmount = 0;
      selectedShippingRate = 'free_standard';
      updateTotal();
      goToStep(2);
    }

    btn.disabled = false;
    btn.textContent = 'Continue to Review';
  }

  async function placeOrder() {
    const agreeBox = document.getElementById('agree-terms');
    if (!agreeBox.checked) {
      showError('Please agree to the Terms of Service before placing your order.');
      return;
    }

    const btn = document.getElementById('btn-place-order');
    btn.disabled = true;
    btn.textContent = 'Placing order...';

    try {
      const form = document.getElementById('shipping-form');
      const formData = new FormData(form);

      const orderData = {
        _csrf_token: csrfToken,
        shipping_name: formData.get('shipping_first') + ' ' + formData.get('shipping_last'),
        shipping_address_line1: formData.get('shipping_address'),
        shipping_address_line2: formData.get('shipping_address2'),
        shipping_city: formData.get('shipping_city'),
        shipping_state: formData.get('shipping_state'),
        shipping_zip: formData.get('shipping_zip'),
        shipping_country: 'US',
        shipping_phone: formData.get('shipping_phone'),
        shipping_amount: shippingAmount,
        payment_method: 'pending',
        payment_reference: 'awaiting_invoice',
        shipping_rate_id: selectedShippingRate,
      };

      const res = await fetch('<?= SHOP_URL ?>/php/checkout-actions.php?action=place-order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify(orderData),
      });

      const data = await res.json();

      if (data.success) {
        document.getElementById('order-number').textContent = data.order_number || '';
        document.getElementById('order-email').textContent = <?= json_encode($customer['email'] ?? '') ?>;
        goToStep(3);
        // Hide sidebar on confirmation
        document.getElementById('order-summary').style.display = 'none';
      } else {
        showError(data.error || 'Failed to place order. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Place Order — $' + document.getElementById('order-total-btn').textContent;
      }
    } catch (err) {
      showError('Something went wrong. Please try again.');
      btn.disabled = false;
      btn.textContent = 'Place Order — $' + document.getElementById('order-total-btn').textContent;
    }
  }
  </script>
</body>
</html>
