<?php
/* ============================================================
   ClarityLabsUSA — Shop (Product Listing)
   Gated: requires age verification + login
   Products loaded from clarity-ops API
   ============================================================ */

$base_path = '../';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';
require_once __DIR__ . '/../includes/api-client.php';

// Enforce both gates
access_guard();

$page_title = 'Shop Research Peptides';
$page_description = 'Browse our curated catalog of research-grade peptides. Third-party tested with transparent Certificates of Analysis.';

// Fetch products from API
$api = new ClarityApiClient();
$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';

$filters = ['per_page' => 50];
if ($category) $filters['category'] = $category;
if ($search)   $filters['search']   = $search;

$productsResponse = $api->getProducts($filters);
$categoriesResponse = $api->getCategories();

$products   = $productsResponse['data'] ?? [];
$categories = $categoriesResponse['data'] ?? [];

// Fallback: if API is down, load static data
if (empty($products) && !$productsResponse['success']) {
    require_once __DIR__ . '/../includes/product-data.php';
    // Use static products as fallback
    $apiDown = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <!-- Shop Hero -->
    <section class="shop-hero" style="background: var(--navy); padding: 60px 0 40px; text-align: center;">
      <div class="container">
        <span class="section-label" style="color: var(--green);">RESEARCH CATALOG</span>
        <h1 style="color: var(--white); margin-top: 12px;">Research-Grade Peptides</h1>
        <p style="color: var(--gray-400); max-width: 600px; margin: 16px auto 0;">
          Browse our curated selection of third-party tested compounds. All products include transparent Certificates of Analysis.
        </p>
      </div>
    </section>

    <!-- Category Filters -->
    <section style="padding: 24px 0 0;">
      <div class="container">
        <div class="shop__filters" style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
          <a href="<?= SHOP_URL ?>/" class="shop__filter-pill <?= empty($category) ? 'active' : '' ?>">All</a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= SHOP_URL ?>/?category=<?= urlencode($cat['name'] ?? $cat['slug'] ?? '') ?>"
               class="shop__filter-pill <?= $category === ($cat['name'] ?? '') ? 'active' : '' ?>">
              <?= htmlspecialchars($cat['name'] ?? '') ?>
              <?php if (isset($cat['products_count'])): ?>
                <span class="shop__filter-count">(<?= $cat['products_count'] ?>)</span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Product Grid -->
    <section class="shop-grid" style="padding: 40px 0 80px;">
      <div class="container">
        <?php if (empty($products)): ?>
          <div style="text-align: center; padding: 60px 0;">
            <h3 style="color: var(--navy); margin-bottom: 12px;">No products found</h3>
            <p style="color: var(--gray-600);">
              <?php if ($search): ?>
                No results for "<?= htmlspecialchars($search) ?>". Try a different search term.
              <?php else: ?>
                No products available in this category yet.
              <?php endif; ?>
            </p>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <?php
                $name      = htmlspecialchars($product['name'] ?? '');
                $sku       = htmlspecialchars($product['sku'] ?? '');
                $cat       = htmlspecialchars($product['category'] ?? '');
                $price     = $product['sale_price'] ?? $product['retail_price'] ?? 0;
                $stock     = $product['stock_status'] ?? 'Unknown';
                $image     = $product['primary_image'] ?? '';
                $shortDesc = htmlspecialchars($product['short_description'] ?? '');
                $slug      = $product['slug'] ?? strtolower(str_replace(' ', '-', $product['name'] ?? ''));
              ?>
              <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($sku) ?>" class="product-card fade-up">
                <div class="product-card__img">
                  <?php if ($image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= $name ?>" loading="lazy">
                  <?php else: ?>
                    <div style="width: 100%; height: 220px; background: var(--gray-50); display: flex; align-items: center; justify-content: center; color: var(--gray-400);">No Image</div>
                  <?php endif; ?>

                  <?php if ($stock === 'Out of Stock'): ?>
                    <span class="product-card__badge product-card__badge--oos">Out of Stock</span>
                  <?php elseif ($stock === 'Low Stock'): ?>
                    <span class="product-card__badge product-card__badge--low">Low Stock</span>
                  <?php endif; ?>
                </div>
                <div class="product-card__body">
                  <span class="product-card__cat"><?= $cat ?></span>
                  <h4 class="product-card__name"><?= $name ?></h4>
                  <p class="product-card__desc"><?= $shortDesc ?></p>
                  <div class="product-card__price">
                    From $<?= number_format($price, 2) ?>
                  </div>
                  <span class="product-card__cta">View Details &rarr;</span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Can't Find What You're Looking For? -->
        <div class="shop__request" style="text-align: center; padding: 60px 0 0;">
          <h3 style="color: var(--navy); margin-bottom: 8px;">Can't Find What You're Looking For?</h3>
          <p style="color: var(--gray-600); max-width: 500px; margin: 0 auto 20px;">
            We offer a curated selection, but our supplier network extends much further. Tell us what you need and we'll see if we can source it.
          </p>
          <a href="<?= SHOP_URL ?>/support?type=product_request" class="btn btn--outline" style="display: inline-block; padding: 12px 28px; border: 2px solid var(--green); border-radius: 50px; color: var(--green); font-weight: 600; font-size: 14px; transition: all 0.2s;">
            Request a Product &rarr;
          </a>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
