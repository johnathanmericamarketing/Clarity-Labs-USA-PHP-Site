<?php
/* ============================================================
   ClarityLabsUSA — Order Detail
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
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

if (!$response['success'] || empty($response['data'])) {
    header('Location: ' . SHOP_URL . '/account/orders');
    exit;
}

$order = $response['data'];
$items = json_decode($order['items_json'] ?? '[]', true) ?: [];
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
            <?= ucfirst($order['status'] ?? 'pending') ?>
          </span>
        </div>

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
