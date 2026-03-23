<?php
/* ============================================================
   ClarityLabsUSA — Shopping Cart
   Gated: requires age verification + login
   ============================================================ */

$base_path = '../';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';

access_guard();

$page_title = 'Shopping Cart';
$items = cart_items();
$subtotal = cart_subtotal();
$itemCount = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    .cart { padding: 60px 0 100px; min-height: 60vh; }
    .cart__header { margin-bottom: 32px; }
    .cart__header h1 { font-size: 36px; }
    .cart__header span { color: var(--gray-400); font-size: 15px; margin-left: 8px; }

    .cart__empty {
      text-align: center;
      padding: 80px 0;
    }
    .cart__empty h3 { margin-bottom: 12px; }
    .cart__empty p { color: var(--gray-600); margin-bottom: 24px; }
    .cart__empty a {
      display: inline-block;
      padding: 14px 32px;
      background: linear-gradient(135deg, var(--green), var(--navy));
      color: var(--white);
      border-radius: 50px;
      font-weight: 600;
    }

    .cart__layout {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 40px;
      align-items: start;
    }

    /* Cart Items */
    .cart-item {
      display: grid;
      grid-template-columns: 80px 1fr auto auto;
      gap: 16px;
      align-items: center;
      padding: 20px 0;
      border-bottom: 1px solid var(--rule);
    }

    .cart-item__img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      background: var(--gray-50);
    }

    .cart-item__info h4 {
      font-size: 16px;
      margin-bottom: 2px;
    }

    .cart-item__info .cart-item__size {
      font-size: 13px;
      color: var(--gray-400);
    }

    .cart-item__info .cart-item__price {
      font-size: 14px;
      color: var(--green);
      font-weight: 600;
      margin-top: 4px;
    }

    .cart-item__qty {
      display: flex;
      align-items: center;
      gap: 0;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      overflow: hidden;
    }

    .cart-item__qty button {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: var(--navy);
      background: var(--gray-50);
      cursor: pointer;
      border: none;
      transition: background 0.15s;
    }

    .cart-item__qty button:hover { background: var(--gray-200); }

    .cart-item__qty span {
      width: 40px;
      text-align: center;
      font-size: 15px;
      font-weight: 500;
      color: var(--navy);
    }

    .cart-item__remove {
      font-size: 13px;
      color: var(--gray-400);
      cursor: pointer;
      transition: color 0.15s;
      background: none;
      border: none;
      padding: 4px 8px;
    }

    .cart-item__remove:hover { color: #E53E3E; }

    /* Summary */
    .cart-summary {
      background: var(--gray-50);
      border: 1px solid var(--rule);
      border-radius: 16px;
      padding: 28px;
      position: sticky;
      top: 100px;
    }

    .cart-summary h3 {
      font-size: 20px;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--rule);
    }

    .cart-summary__row {
      display: flex;
      justify-content: space-between;
      font-size: 15px;
      margin-bottom: 12px;
    }

    .cart-summary__row.total {
      font-size: 18px;
      font-weight: 600;
      color: var(--navy);
      margin-top: 16px;
      padding-top: 16px;
      border-top: 2px solid var(--rule);
    }

    .cart-summary__note {
      font-size: 12px;
      color: var(--gray-400);
      margin-top: 12px;
    }

    .cart-summary__checkout {
      display: block;
      width: 100%;
      padding: 16px;
      margin-top: 20px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      text-align: center;
      text-decoration: none;
    }

    .cart-summary__checkout:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26, 122, 110, 0.35);
    }

    .cart-summary__continue {
      display: block;
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
      color: var(--green);
    }

    @media (max-width: 768px) {
      .cart__layout { grid-template-columns: 1fr; }
      .cart-item { grid-template-columns: 60px 1fr; gap: 12px; }
      .cart-item__qty, .cart-item__remove { grid-column: 2; }
      .cart-item__img { width: 60px; height: 60px; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <section class="cart">
      <div class="container">
        <div class="cart__header">
          <h1>Shopping Cart <span id="cart-count-label">(<?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>)</span></h1>
        </div>

        <?php if (cart_is_empty()): ?>
          <div class="cart__empty">
            <h3>Your cart is empty</h3>
            <p>Browse our catalog to find research-grade peptides for your studies.</p>
            <a href="<?= SHOP_URL ?>/">Browse Products</a>
          </div>
        <?php else: ?>
          <div class="cart__layout">
            <!-- Cart Items -->
            <div id="cart-items">
              <?php foreach ($items as $i => $item): ?>
                <div class="cart-item" data-sku="<?= htmlspecialchars($item['sku']) ?>">
                  <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item__img">
                  <?php else: ?>
                    <div class="cart-item__img" style="display: flex; align-items: center; justify-content: center; color: var(--gray-400); font-size: 11px;">No img</div>
                  <?php endif; ?>
                  <div class="cart-item__info">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <div class="cart-item__size"><?= htmlspecialchars($item['size']) ?></div>
                    <div class="cart-item__price">$<?= number_format($item['price'], 2) ?></div>
                  </div>
                  <div class="cart-item__qty">
                    <button onclick="updateQty('<?= htmlspecialchars($item['sku']) ?>', <?= $item['qty'] - 1 ?>)">−</button>
                    <span id="qty-<?= htmlspecialchars($item['sku']) ?>"><?= $item['qty'] ?></span>
                    <button onclick="updateQty('<?= htmlspecialchars($item['sku']) ?>', <?= $item['qty'] + 1 ?>)">+</button>
                  </div>
                  <button class="cart-item__remove" onclick="removeItem('<?= htmlspecialchars($item['sku']) ?>')">Remove</button>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="cart-summary">
              <h3>Order Summary</h3>
              <div class="cart-summary__row">
                <span>Subtotal</span>
                <span id="cart-subtotal">$<?= number_format($subtotal, 2) ?></span>
              </div>
              <div class="cart-summary__row">
                <span>Shipping</span>
                <span style="color: var(--gray-400);">Calculated at checkout</span>
              </div>
              <div class="cart-summary__row">
                <span>Tax</span>
                <span style="color: var(--gray-400);">Calculated at checkout</span>
              </div>
              <div class="cart-summary__row total">
                <span>Estimated Total</span>
                <span id="cart-total">$<?= number_format($subtotal, 2) ?></span>
              </div>
              <p class="cart-summary__note">Shipping and taxes calculated during checkout.</p>
              <a href="<?= SHOP_URL ?>/checkout" class="cart-summary__checkout">Proceed to Checkout</a>
              <a href="<?= SHOP_URL ?>/" class="cart-summary__continue">← Continue Shopping</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  async function cartAction(action, sku, qty) {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken);
    formData.append('sku', sku);
    if (qty !== undefined) formData.append('qty', qty);

    const res = await fetch('<?= SHOP_URL ?>/php/cart-actions.php?action=' + action, {
      method: 'POST',
      body: formData,
    });
    return res.json();
  }

  async function updateQty(sku, newQty) {
    if (newQty < 1) {
      removeItem(sku);
      return;
    }
    const data = await cartAction('update', sku, newQty);
    if (data.success) {
      location.reload();
    }
  }

  async function removeItem(sku) {
    const data = await cartAction('remove', sku);
    if (data.success) {
      location.reload();
    }
  }
  </script>
</body>
</html>
