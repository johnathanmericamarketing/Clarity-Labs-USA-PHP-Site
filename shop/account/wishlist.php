<?php
/* ============================================================
   ClarityLabsUSA — Saved Products (Wishlist)
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

access_guard();

$page_title = 'Saved Products';
$customer = get_customer();
$customerName = get_customer_name();
$current_page = 'account';

// Fetch wishlist from API
$api = new ClarityApiClient();
$wishlistResponse = $api->getWishlist(get_customer_token());
$wishlistItems = $wishlistResponse['data'] ?? [];
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
    .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
    .wishlist-card { background: var(--gray-50); border: 1px solid var(--rule); border-radius: 12px; overflow: hidden; transition: all .2s; position: relative; }
    .wishlist-card:hover { border-color: var(--green-bdr); }
    .wishlist-card__img { height: 180px; background: var(--white); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .wishlist-card__img img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .wishlist-card__img-placeholder { color: var(--gray-400); font-size: 14px; }
    .wishlist-card__body { padding: 16px; }
    .wishlist-card__cat { font-family: var(--font-mono); font-size: 10px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--green); }
    .wishlist-card__name { font-size: 16px; font-weight: 600; color: var(--navy); margin: 4px 0 8px; }
    .wishlist-card__price { font-size: 15px; font-weight: 700; color: var(--navy); }
    .wishlist-card__actions { display: flex; gap: 8px; margin-top: 12px; }
    .wishlist-card__actions .btn { font-size: 12px; padding: 8px 14px; }
    .wishlist-card__remove { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--rule); background: var(--white); color: var(--gray-400); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; font-size: 16px; line-height: 1; }
    .wishlist-card__remove:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }
    .wishlist-empty { text-align: center; padding: 60px 20px; color: var(--gray-400); }
    .wishlist-empty h3 { color: var(--navy); margin-bottom: 8px; }
    @media (max-width: 768px) {
      .account__grid { grid-template-columns: 1fr; gap: 24px; }
      .wishlist-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
      .wishlist-grid { grid-template-columns: 1fr; }
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
      <span class="breadcrumb__current">Saved Products</span>
    </div>
  </div>

  <main>
    <section class="account">
      <div class="container">
        <div class="account__header">
          <h1>Saved Products</h1>
          <p>Products you've saved for later.</p>
        </div>

        <div class="account__grid">
          <!-- Sidebar -->
          <nav class="account-nav">
            <a href="<?= SHOP_URL ?>/account/">Dashboard</a>
            <a href="<?= SHOP_URL ?>/account/orders">Order History</a>
            <a href="<?= SHOP_URL ?>/account/addresses">Addresses</a>
            <a href="<?= SHOP_URL ?>/account/wishlist" class="active">Saved Products</a>
            <a href="<?= SHOP_URL ?>/support/">Support</a>
            <div class="account-nav__logout">
              <a href="#" onclick="logout(); return false;">Sign Out</a>
            </div>
          </nav>

          <!-- Content -->
          <div>
            <?php if (empty($wishlistItems)): ?>
              <div class="wishlist-empty">
                <h3>No saved products yet</h3>
                <p>Save products you're interested in and they'll appear here.</p>
                <a href="<?= SHOP_URL ?>/" class="btn btn--green" style="margin-top: 16px;">Browse Products</a>
              </div>
            <?php else: ?>
              <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                <div class="wishlist-card" id="wishlist-<?= htmlspecialchars($item['sku']) ?>">
                  <button class="wishlist-card__remove" onclick="removeFromWishlist('<?= htmlspecialchars($item['sku']) ?>')" title="Remove">&times;</button>
                  <div class="wishlist-card__img">
                    <?php if (!empty($item['primary_image'])): ?>
                      <img src="<?= htmlspecialchars($item['primary_image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <?php else: ?>
                      <span class="wishlist-card__img-placeholder"><?= htmlspecialchars($item['name']) ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="wishlist-card__body">
                    <div class="wishlist-card__cat"><?= htmlspecialchars($item['category'] ?? '') ?></div>
                    <div class="wishlist-card__name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="wishlist-card__price">$<?= number_format($item['price_per_vial'] ?? $item['sale_price'] ?? 0, 2) ?></div>
                    <div class="wishlist-card__actions">
                      <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($item['sku']) ?>" class="btn btn--navy">View Product</a>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script>
  function removeFromWishlist(sku) {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
    fetch('<?= SHOP_URL ?>/php/wishlist-actions.php?action=remove&sku=' + encodeURIComponent(sku), {
      method: 'POST',
      headers: { 'X-CSRF-Token': token },
      credentials: 'include'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        var card = document.getElementById('wishlist-' + sku);
        if (card) card.remove();
        // If no more items, show empty state
        if (document.querySelectorAll('.wishlist-card').length === 0) {
          location.reload();
        }
      }
    });
  }

  function logout() {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var fd = new FormData();
    fd.append('csrf_token', token);
    fetch('<?= SHOP_URL ?>/php/auth-actions.php?action=logout', { method: 'POST', body: fd, credentials: 'include' })
      .then(function() { window.location.href = '<?= SHOP_URL ?>/gate/sign-in'; });
  }
  </script>
</body>
</html>
