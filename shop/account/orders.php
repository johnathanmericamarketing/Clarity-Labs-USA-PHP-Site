<?php
/* ============================================================
   ClarityLabsUSA — Order History
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'Order History';

$api = new ClarityApiClient();
$page = max(1, (int) ($_GET['page'] ?? 1));
$ordersResponse = $api->getOrders(get_customer_token(), ['per_page' => 20, 'page' => $page]);
$orders = $ordersResponse['data'] ?? [];
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
      <div class="container">
        <h1 style="font-size: 32px; margin-bottom: 32px;">Order History</h1>

        <?php if (empty($orders)): ?>
          <div style="text-align: center; padding: 60px 0;">
            <h3>No orders yet</h3>
            <p style="color: var(--gray-600); margin-bottom: 20px;">Start shopping to see your orders here.</p>
            <a href="<?= SHOP_URL ?>/" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, var(--green), var(--navy)); color: var(--white); border-radius: 50px; font-weight: 600;">Browse Products</a>
          </div>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <div style="background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; padding: 20px 24px; margin-bottom: 12px;">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                  <strong style="font-size: 16px; color: var(--navy);"><?= htmlspecialchars($order['order_number'] ?? '') ?></strong>
                  <span style="font-size: 13px; color: var(--gray-400); margin-left: 12px;">
                    <?= isset($order['ordered_at']) ? date('M j, Y', strtotime($order['ordered_at'])) : '' ?>
                  </span>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                  <span class="order-status order-status--<?= strtolower($order['status'] ?? 'pending') ?>" style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                    <?= ucfirst($order['status'] ?? 'pending') ?>
                  </span>
                  <strong style="color: var(--navy);">$<?= number_format($order['total_amount'] ?? 0, 2) ?></strong>
                  <a href="<?= SHOP_URL ?>/account/order-detail?id=<?= (int) ($order['id'] ?? 0) ?>" style="color: var(--green); font-weight: 500; font-size: 14px;">View Details →</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 24px;">
          <a href="<?= SHOP_URL ?>/account/" style="color: var(--green); font-size: 14px;">← Back to Dashboard</a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
