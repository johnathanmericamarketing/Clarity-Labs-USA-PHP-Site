<?php
/* ============================================================
   ClarityLabsUSA — Customer Account Dashboard
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'My Account';
$customer = get_customer();
$customerName = get_customer_name();

// Fetch recent orders
$api = new ClarityApiClient();
$ordersResponse = $api->getOrders(get_customer_token(), ['per_page' => 5]);
$orders = $ordersResponse['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    .account { padding: 60px 0 100px; min-height: 60vh; }

    .account__header {
      margin-bottom: 40px;
    }

    .account__header h1 { font-size: 32px; margin-bottom: 4px; }
    .account__header p { color: var(--gray-600); }

    .account__grid {
      display: grid;
      grid-template-columns: 240px 1fr;
      gap: 40px;
    }

    /* Sidebar Nav */
    .account-nav a {
      display: block;
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 500;
      color: var(--gray-600);
      border-radius: 8px;
      margin-bottom: 4px;
      transition: all 0.15s;
    }

    .account-nav a:hover,
    .account-nav a.active {
      background: var(--green-bg);
      color: var(--green);
    }

    .account-nav__logout {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--rule);
    }

    .account-nav__logout a { color: #DC2626; }
    .account-nav__logout a:hover { background: #FEE2E2; }

    /* Content */
    .account-card {
      background: var(--gray-50);
      border: 1px solid var(--rule);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 20px;
    }

    .account-card h3 {
      font-size: 18px;
      margin-bottom: 16px;
    }

    /* Orders table */
    .orders-table {
      width: 100%;
      border-collapse: collapse;
    }

    .orders-table th {
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-400);
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 8px 12px;
      border-bottom: 1px solid var(--rule);
    }

    .orders-table td {
      padding: 12px;
      font-size: 14px;
      border-bottom: 1px solid var(--rule);
    }

    .orders-table a { color: var(--green); font-weight: 500; }

    .order-status {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }

    .order-status--pending { background: #FEF3C7; color: #92400E; }
    .order-status--processing { background: #DBEAFE; color: #1E40AF; }
    .order-status--paid { background: #D1FAE5; color: #065F46; }
    .order-status--shipped { background: #E0E7FF; color: #3730A3; }
    .order-status--delivered { background: #D1FAE5; color: #065F46; }
    .order-status--cancelled { background: #FEE2E2; color: #991B1B; }

    @media (max-width: 768px) {
      .account__grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <main>
    <section class="account">
      <div class="container">
        <div class="account__header">
          <h1>Welcome, <?= htmlspecialchars($customerName) ?></h1>
          <p>Manage your orders, addresses, and account settings.</p>
        </div>

        <div class="account__grid">
          <!-- Sidebar -->
          <nav class="account-nav">
            <a href="<?= SHOP_URL ?>/account/" class="active">Dashboard</a>
            <a href="<?= SHOP_URL ?>/account/orders">Order History</a>
            <a href="<?= SHOP_URL ?>/account/addresses">Addresses</a>
            <a href="<?= SHOP_URL ?>/support/">Support</a>
            <div class="account-nav__logout">
              <a href="#" onclick="logout(); return false;">Sign Out</a>
            </div>
          </nav>

          <!-- Content -->
          <div>
            <!-- Quick Stats -->
            <div class="account-card">
              <h3>Account Overview</h3>
              <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div>
                  <div style="font-size: 12px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Email</div>
                  <div style="font-size: 14px; color: var(--navy); margin-top: 4px;"><?= htmlspecialchars($customer['email'] ?? '') ?></div>
                </div>
                <div>
                  <div style="font-size: 12px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Total Orders</div>
                  <div style="font-size: 14px; color: var(--navy); margin-top: 4px;"><?= $customer['total_orders'] ?? 0 ?></div>
                </div>
                <div>
                  <div style="font-size: 12px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Member Since</div>
                  <div style="font-size: 14px; color: var(--navy); margin-top: 4px;"><?= isset($customer['created_at']) ? date('M Y', strtotime($customer['created_at'])) : '—' ?></div>
                </div>
              </div>
            </div>

            <!-- Recent Orders -->
            <div class="account-card">
              <h3>Recent Orders</h3>
              <?php if (empty($orders)): ?>
                <p style="color: var(--gray-400); font-size: 14px;">No orders yet. <a href="<?= SHOP_URL ?>/" style="color: var(--green);">Browse products</a></p>
              <?php else: ?>
                <table class="orders-table">
                  <thead>
                    <tr>
                      <th>Order</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $order): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($order['order_number'] ?? '') ?></strong></td>
                        <td><?= isset($order['ordered_at']) ? date('M j, Y', strtotime($order['ordered_at'])) : '—' ?></td>
                        <td>
                          <span class="order-status order-status--<?= strtolower($order['status'] ?? 'pending') ?>">
                            <?= ucfirst($order['status'] ?? 'pending') ?>
                          </span>
                        </td>
                        <td>$<?= number_format($order['total_amount'] ?? 0, 2) ?></td>
                        <td><a href="<?= SHOP_URL ?>/account/order-detail?id=<?= $order['id'] ?? '' ?>">View</a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <div style="margin-top: 16px;">
                  <a href="<?= SHOP_URL ?>/account/orders" style="color: var(--green); font-size: 14px; font-weight: 500;">View all orders →</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
  async function logout() {
    await fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=logout');
    window.location.href = '<?= SHOP_URL ?>/gate/sign-in';
  }
  </script>
</body>
</html>
