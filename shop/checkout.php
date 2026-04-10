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
require_once __DIR__ . '/../includes/api-client.php';

access_guard();

// Redirect if cart is empty
if (cart_is_empty()) {
    header('Location: ' . SHOP_URL . '/cart');
    exit;
}

$page_title = 'Checkout';
$customer = get_customer();

// Pull fresh profile so checkout can prefill from saved shipping address
// and know whether the customer already has one saved
$savedShipping = [];
$hasSavedShipping = false;
if (!empty($customer['id']) || is_logged_in()) {
    try {
        $checkoutApi = new ClarityApiClient();
        $me = $checkoutApi->getMe(get_customer_token());
        if (!empty($me['data'])) {
            $customer = array_merge($customer, $me['data']);
            $savedShipping = $me['data']['shipping_address'] ?? [];
            $hasSavedShipping = !empty($savedShipping['line1']);
        }
    } catch (\Throwable $e) {
        // non-fatal — fall back to empty fields
    }
}
$items = cart_items();
$subtotal = cart_subtotal();

// Phase 14: Check if Bacteriostatic Water (WA10) is in the cart.
// If not, fetch its product data for the checkout add-on upsell.
$hasWater = false;
$waterProduct = null;
foreach ($items as $item) {
    if (($item['sku'] ?? '') === 'WA10') {
        $hasWater = true;
        break;
    }
}
if (!$hasWater) {
    try {
        $upsellApi = new ClarityApiClient();
        $waterResponse = $upsellApi->getProduct('WA10');
        if (!empty($waterResponse['data']) && ($waterResponse['data']['stock_status'] ?? '') !== 'Out of Stock') {
            $waterProduct = $waterResponse['data'];
        }
    } catch (\Throwable $e) {
        // Silent fail — upsell is optional, don't break checkout
    }
}
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

                <?php if ($hasSavedShipping): ?>
                <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #4338ca;">
                  Pre-filled from your saved shipping address. Edit any field below to ship somewhere different.
                </div>
                <?php endif; ?>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Street Address</label>
                  <input type="text" name="shipping_address" class="checkout-form__input" placeholder="123 Main St"
                         value="<?= htmlspecialchars($savedShipping['line1'] ?? '') ?>" required>
                </div>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Apartment, Suite, etc. (optional)</label>
                  <input type="text" name="shipping_address2" class="checkout-form__input" placeholder="Apt 4B"
                         value="<?= htmlspecialchars($savedShipping['line2'] ?? '') ?>">
                </div>

                <div class="checkout-form__row--3" style="display: grid; gap: 12px;">
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">City</label>
                    <input type="text" name="shipping_city" class="checkout-form__input"
                           value="<?= htmlspecialchars($savedShipping['city'] ?? '') ?>" required>
                  </div>
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">State</label>
                    <input type="text" name="shipping_state" class="checkout-form__input" placeholder="OR" maxlength="2"
                           value="<?= htmlspecialchars($savedShipping['state'] ?? '') ?>" required>
                  </div>
                  <div class="checkout-form__group">
                    <label class="checkout-form__label">ZIP Code</label>
                    <input type="text" name="shipping_zip" class="checkout-form__input" placeholder="97239"
                           value="<?= htmlspecialchars($savedShipping['zip'] ?? '') ?>" required>
                  </div>
                </div>

                <div class="checkout-form__group">
                  <label class="checkout-form__label">Phone (for shipping updates)</label>
                  <input type="tel" name="shipping_phone" class="checkout-form__input"
                         value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" placeholder="(555) 123-4567">
                </div>

                <div class="checkout-form__group" style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                  <input type="checkbox" name="save_shipping_as_default" id="save_shipping_as_default" value="1"
                         <?= $hasSavedShipping ? '' : 'checked' ?>
                         style="width: 18px; height: 18px; margin: 0;">
                  <label for="save_shipping_as_default" style="margin: 0; font-size: 13px; color: #334155; cursor: pointer;">
                    Save this as my default shipping address
                  </label>
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

              <?php if (!$hasWater && $waterProduct): ?>
              <!-- Phase 14: Bacteriostatic Water Add-On Upsell -->
              <div id="upsell-water" style="margin: 24px 0; padding: 20px; border-radius: 12px; border: 2px solid #2A9D8F; background: linear-gradient(135deg, rgba(42,157,143,0.06), rgba(11,30,63,0.03));">
                <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                  <?php
                    $waterImg = $waterProduct['primary_image'] ?? '';
                    $waterPrice = (float) ($waterProduct['price_per_vial'] ?? $waterProduct['sale_price'] ?? 0);
                    $waterName = htmlspecialchars($waterProduct['name'] ?? 'Bacteriostatic Water');
                    $waterMg = htmlspecialchars($waterProduct['mg_specification'] ?? $waterProduct['size'] ?? '');
                  ?>
                  <?php if ($waterImg): ?>
                    <img src="<?= htmlspecialchars($waterImg) ?>" alt="Bacteriostatic Water" style="width: 72px; height: 72px; border-radius: 10px; object-fit: cover; border: 1px solid #E4E6EB; flex-shrink: 0;">
                  <?php else: ?>
                    <div style="width: 72px; height: 72px; border-radius: 10px; background: #F8F9FA; border: 1px solid #E4E6EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                      <span style="font-family: 'DM Mono', monospace; font-size: 10px; color: #9BA3B5; text-transform: uppercase;">WA10</span>
                    </div>
                  <?php endif; ?>
                  <div style="flex: 1; min-width: 200px;">
                    <div style="font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #2A9D8F; font-weight: 600; margin-bottom: 4px;">Complete Your Order</div>
                    <div style="font-size: 16px; font-weight: 700; color: #0B1E3F; margin-bottom: 4px;"><?= $waterName ?></div>
                    <div style="font-size: 13px; color: #6B7185; line-height: 1.4;">Essential for reconstitution. Sterile bacteriostatic water for research use.</div>
                  </div>
                  <div style="text-align: center; flex-shrink: 0;">
                    <div style="font-family: 'DM Serif Display', serif; font-size: 24px; color: #0B1E3F; margin-bottom: 4px;">$<?= number_format($waterPrice, 2) ?><span style="font-size: 12px; color: #6B7185; font-family: Inter, sans-serif;"> each</span></div>
                    <div style="display: flex; align-items: center; gap: 8px; justify-content: center; margin-bottom: 10px;">
                      <label style="font-size: 11px; color: #6B7185; font-family: 'DM Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px;">Qty:</label>
                      <input type="number" id="upsell-water-qty" value="1" min="1" max="10" style="width: 52px; padding: 6px 8px; border: 1px solid #E4E6EB; border-radius: 6px; text-align: center; font-size: 14px; font-weight: 700; color: #0B1E3F;">
                    </div>
                    <button type="button" id="upsell-water-btn" onclick="addUpsellToCart()" style="padding: 10px 24px; background: #0B1E3F; color: #fff; border: none; border-radius: 8px; font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; cursor: pointer; font-weight: 600; transition: background 0.15s; width: 100%;">
                      Add to Order
                    </button>
                  </div>
                </div>
              </div>

              <script>
              // Phase 14: Upsell AJAX add-to-cart (no page reload)
              var upsellData = {
                sku: 'WA10',
                name: <?= json_encode($waterProduct['name'] ?? 'Bacteriostatic Water') ?>,
                size: <?= json_encode($waterProduct['mg_specification'] ?? '') ?>,
                price: <?= json_encode($waterPrice) ?>,
              };

              async function addUpsellToCart() {
                var btn = document.getElementById('upsell-water-btn');
                var qtyInput = document.getElementById('upsell-water-qty');
                var qty = Math.max(1, Math.min(10, parseInt(qtyInput.value) || 1));
                qtyInput.value = qty; // normalize

                btn.textContent = 'Adding...';
                btn.disabled = true;

                try {
                  var fd = new FormData();
                  fd.append('_csrf_token', '<?= csrf_token() ?>');
                  fd.append('sku', upsellData.sku);
                  fd.append('name', upsellData.name);
                  fd.append('size', upsellData.size);
                  fd.append('price', upsellData.price);
                  fd.append('qty', qty);
                  fd.append('image_url', <?= json_encode($waterImg) ?>);

                  var res = await fetch('<?= SHOP_URL ?>/php/cart-actions.php?action=add', {
                    method: 'POST',
                    body: fd,
                  });
                  var data = await res.json();

                  if (data.success) {
                    // Success — update the UI
                    var card = document.getElementById('upsell-water');
                    card.innerHTML = '<div style="padding: 16px; text-align: center; color: #2A9D8F; font-weight: 700; font-size: 14px;">&#10003; ' + qty + 'x Bacteriostatic Water added to your order</div>';
                    card.style.borderColor = '#22c55e';
                    card.style.background = 'rgba(34,197,94,0.06)';

                    // Update the order total in the sidebar + button
                    if (typeof updateTotal === 'function') updateTotal();
                    var totalBtn = document.getElementById('order-total-btn');
                    if (totalBtn && data.subtotal) {
                      totalBtn.textContent = parseFloat(data.subtotal).toFixed(2);
                    }

                    // Fade out after 3 seconds
                    setTimeout(function() {
                      card.style.transition = 'opacity 0.5s, max-height 0.5s';
                      card.style.opacity = '0';
                      card.style.maxHeight = '0';
                      card.style.overflow = 'hidden';
                      card.style.padding = '0';
                      card.style.margin = '0';
                      card.style.border = 'none';
                    }, 3000);
                  } else {
                    btn.textContent = 'Add to Order';
                    btn.disabled = false;
                    alert(data.error || 'Could not add to cart. Please try again.');
                  }
                } catch (err) {
                  btn.textContent = 'Add to Order';
                  btn.disabled = false;
                  alert('Network error. Please try again.');
                }
              }
              </script>
              <?php endif; ?>

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

  // GA4 begin_checkout event
  if (typeof ClarityAnalytics !== 'undefined') {
    ClarityAnalytics.beginCheckout(<?= json_encode(array_map(function($item) {
      return ['sku' => $item['sku'], 'name' => $item['name'], 'price' => $item['price'], 'quantity' => $item['qty']];
    }, cart_items())) ?>, subtotal);
  }

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
    // GA4 add_shipping_info event
    if (typeof ClarityAnalytics !== 'undefined') {
      ClarityAnalytics.addShippingInfo(shippingAmount, el.querySelector('.shipping-rate__carrier')?.textContent || '');
    }
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
        save_shipping_as_default: form.querySelector('[name="save_shipping_as_default"]')?.checked ? 1 : 0,
      };

      const res = await fetch('<?= SHOP_URL ?>/php/checkout-actions.php?action=place-order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify(orderData),
      });

      const data = await res.json();

      if (data.success) {
        // GA4 purchase event
        if (typeof ClarityAnalytics !== 'undefined') {
          ClarityAnalytics.purchase({
            id: data.order_number || '',
            total: parseFloat(document.getElementById('order-total-btn').textContent) || 0,
            shipping: shippingAmount,
            tax: 0,
            items: <?= json_encode(array_map(function($item) {
              return ['sku' => $item['sku'], 'name' => $item['name'], 'price' => $item['price'], 'quantity' => $item['qty']];
            }, cart_items())) ?>
          });
        }
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
