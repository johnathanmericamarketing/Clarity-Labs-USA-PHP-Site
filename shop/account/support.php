<?php
/* ============================================================
   ClarityLabsUSA — My Support Tickets
   Lists the logged-in customer's tickets (from ops API).
   "View & Reply" links to the ticket's public tracking page
   (same page as the email button) where they can add replies.
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'Support Tickets';
$customerName = get_customer_name();

$api = new ClarityApiClient();
$ticketsResponse = $api->getMyTickets(get_customer_token());
$tickets = $ticketsResponse['data'] ?? [];
$loadFailed = (($ticketsResponse['status'] ?? '') !== 'ok');
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

    .account-card h3 { font-size: 18px; margin-bottom: 16px; }

    /* Tickets table */
    .tickets-table { width: 100%; border-collapse: collapse; }

    .tickets-table th {
      text-align: left;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--gray-400);
      padding: 8px 12px;
      border-bottom: 1px solid var(--rule);
    }

    .tickets-table td {
      padding: 12px;
      font-size: 14px;
      border-bottom: 1px solid var(--rule);
      vertical-align: middle;
    }

    .tickets-table tr:last-child td { border-bottom: none; }

    .ticket-subject {
      max-width: 340px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .ticket-status {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      text-transform: capitalize;
    }

    .ticket-status--open { background: #D1FAE5; color: #065F46; }
    .ticket-status--in_progress { background: #DBEAFE; color: #1E40AF; }
    .ticket-status--waiting { background: #FEF3C7; color: #92400E; }
    .ticket-status--resolved { background: #E0E7FF; color: #3730A3; }
    .ticket-status--closed { background: #E5E7EB; color: #374151; }

    .btn-new-ticket {
      display: inline-block;
      padding: 10px 18px;
      background: var(--green);
      color: #fff;
      border-radius: 8px;
      font-weight: 600;
      font-size: 13px;
      transition: all 0.2s;
    }

    .btn-new-ticket:hover { opacity: 0.9; transform: translateY(-1px); }

    .ticket-view-link {
      color: var(--green);
      font-weight: 600;
      font-size: 13px;
      white-space: nowrap;
    }

    @media (max-width: 768px) {
      .account__grid { grid-template-columns: 1fr; }
      .tickets-table .hide-mobile { display: none; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <main>
    <section class="account">
      <div class="container">
        <div class="account__header">
          <h1>Support Tickets</h1>
          <p>Track your support requests and reply with more information.</p>
        </div>

        <div class="account__grid">
          <!-- Sidebar -->
          <nav class="account-nav">
            <a href="<?= SHOP_URL ?>/account/">Dashboard</a>
            <a href="<?= SHOP_URL ?>/account/orders">Order History</a>
            <a href="<?= SHOP_URL ?>/account/addresses">Addresses</a>
            <a href="<?= SHOP_URL ?>/account/settings">Settings</a>
            <a href="<?= SHOP_URL ?>/account/support" class="active">Support</a>
            <div class="account-nav__logout">
              <a href="#" onclick="logout(); return false;">Sign Out</a>
            </div>
          </nav>

          <!-- Content -->
          <div>
            <div class="account-card">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <h3 style="margin-bottom: 0;">My Tickets</h3>
                <a href="<?= SHOP_URL ?>/support/" class="btn-new-ticket">+ Open New Ticket</a>
              </div>

              <?php if ($loadFailed): ?>
                <p style="color: var(--gray-400); font-size: 14px;">
                  We couldn't load your tickets right now. Please refresh the page, or
                  <a href="<?= SHOP_URL ?>/support/" style="color: var(--green);">open a new ticket</a> if you need help.
                </p>
              <?php elseif (empty($tickets)): ?>
                <p style="color: var(--gray-400); font-size: 14px;">
                  You haven't opened any support tickets yet.
                  <a href="<?= SHOP_URL ?>/support/" style="color: var(--green);">Need help with something?</a>
                </p>
              <?php else: ?>
                <table class="tickets-table">
                  <thead>
                    <tr>
                      <th>Ticket</th>
                      <th>Subject</th>
                      <th class="hide-mobile">Opened</th>
                      <th>Status</th>
                      <th class="hide-mobile">Replies</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($ticket['ticket_number'] ?? '') ?></strong></td>
                        <td class="ticket-subject" title="<?= htmlspecialchars($ticket['subject'] ?? '') ?>">
                          <?= htmlspecialchars($ticket['subject'] ?? '') ?>
                        </td>
                        <td class="hide-mobile">
                          <?= isset($ticket['created_at']) ? date('M j, Y', strtotime($ticket['created_at'])) : '—' ?>
                        </td>
                        <td>
                          <span class="ticket-status ticket-status--<?= htmlspecialchars(strtolower($ticket['status'] ?? 'open')) ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $ticket['status'] ?? 'open'))) ?>
                          </span>
                        </td>
                        <td class="hide-mobile"><?= (int) ($ticket['replies_count'] ?? 0) ?></td>
                        <td>
                          <?php if (!empty($ticket['public_url']) && str_starts_with($ticket['public_url'], 'https://')): ?>
                            <a href="<?= htmlspecialchars($ticket['public_url']) ?>" target="_blank" rel="noopener" class="ticket-view-link">View &amp; Reply →</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>

            <div class="account-card">
              <h3>How it works</h3>
              <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                Click <strong>View &amp; Reply</strong> on any ticket to see the full conversation and add more information —
                the same page linked in your ticket emails. You'll receive an email whenever our team responds.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
    function logout() {
      fetch('<?= SHOP_URL ?>/php/auth-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=logout&csrf_token=<?= csrf_token() ?>'
      }).then(() => { window.location.href = '<?= SHOP_URL ?>/gate/sign-in'; });
    }
  </script>
</body>
</html>
