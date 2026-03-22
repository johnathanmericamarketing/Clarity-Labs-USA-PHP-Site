<?php
/* ============================================================
   ClarityLabsUSA — Shop (Product Listing)
   Gated: requires age verification + login
   Products loaded from clarity-ops API
   Uses same CSS classes as main site shop.php for consistent styling
   ============================================================ */

$base_path = '../';
$current_page = 'shop';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';
require_once __DIR__ . '/../includes/api-client.php';
require_once __DIR__ . '/../includes/product-helpers.php';

// Enforce both gates
access_guard();

$page_title = 'Shop Research Compounds';
$page_description = 'Browse ClarityLabs USA research-grade peptides. All compounds third-party tested with Certificates of Analysis. US-based fulfillment.';

// Fetch products from API
$api = new ClarityApiClient();
$categoryFilter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$filters = ['per_page' => 50];
if ($categoryFilter) $filters['category'] = $categoryFilter;
if ($search) $filters['search'] = $search;

$productsResponse = $api->getProducts($filters);
$categoriesResponse = $api->getCategories();

$apiProducts = $productsResponse['data'] ?? [];
$apiCategories = $categoriesResponse['data'] ?? [];

// Fallback: if API is down, load static data
$apiDown = false;
if (empty($apiProducts) && !($productsResponse['success'] ?? false)) {
    require_once __DIR__ . '/../includes/product-data.php';
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

<div class="breadcrumb">
  <div class="breadcrumb__inner">
    <a href="<?= SITE_URL ?>">ClarityLabsUSA</a>
    <span class="breadcrumb__sep">/</span>
    <span class="breadcrumb__current">Shop</span>
  </div>
</div>

<!-- Shop Hero -->
<section class="shop-hero">
  <div class="shop-hero__inner">
    <p class="section-label" style="color:var(--green-rule);">Research Compounds</p>
    <h1 class="fade-up">Research-Grade Compounds</h1>
    <p class="shop-hero__text fade-up stagger-1">Every compound is independently tested, verified for purity, and shipped from the United States.</p>
  </div>
</section>

<!-- Filters -->
<div class="shop-filters">
  <div class="shop-filters__inner">
    <button class="filter-pill <?= empty($categoryFilter) ? 'active' : '' ?>" data-category="all" onclick="filterCategory('all')">All</button>
    <?php
    // Build category list from API or static
    if (!$apiDown && !empty($apiCategories)) {
        foreach ($apiCategories as $cat):
            $catName = $cat['category'] ?? $cat['name'] ?? '';
            $catKey = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($catName)));
    ?>
        <button class="filter-pill <?= $categoryFilter === $catName ? 'active' : '' ?>" data-category="<?= htmlspecialchars($catKey) ?>" onclick="filterCategory('<?= htmlspecialchars($catKey) ?>')"><?= htmlspecialchars($catName) ?></button>
    <?php endforeach;
    } elseif ($apiDown && !empty($products)) {
        $categories = [];
        foreach ($products as $p) {
            if (!empty($p['hidden'])) continue;
            $cat = $p['category'];
            $key = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($cat)));
            if (!isset($categories[$key])) $categories[$key] = $cat;
        }
        foreach ($categories as $key => $label): ?>
        <button class="filter-pill" data-category="<?= $key ?>" onclick="filterCategory('<?= $key ?>')"><?= $label ?></button>
        <?php endforeach;
    } ?>
  </div>
</div>

<!-- Product Grid -->
<section class="shop-grid">
  <div class="shop-grid__inner">
    <?php if (!$apiDown && !empty($apiProducts)):
      // Group variants by compound so each compound shows once
      $groupedProducts = group_products_by_compound($apiProducts);
    ?>
      <?php foreach ($groupedProducts as $product):
        $name = htmlspecialchars($product['name'] ?? '');
        $sku = $product['sku'] ?? '';
        $cat = $product['category'] ?? '';
        $catKey = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($cat)));
        $price = $product['min_price'] ?? 0;
        $stockStatus = $product['stock_status'] ?? 'Unknown';
        $primaryImage = $product['primary_image'] ?? '';
        $shortDesc = htmlspecialchars($product['short_description'] ?? '');
        $sizeTags = array_map(function($s) { return $s['mg']; }, $product['sizes'] ?? []);
      ?>
      <div class="shop-card fade-up" data-category="<?= htmlspecialchars($catKey) ?>">
        <div class="shop-card__img">
          <?php if ($primaryImage): ?>
            <img src="<?= htmlspecialchars($primaryImage) ?>" alt="<?= $name ?>" loading="lazy">
          <?php else: ?>
            <span class="shop-card__img-placeholder"><?= $name ?></span>
          <?php endif; ?>
        </div>
        <div class="shop-card__body">
          <span class="shop-card__cat"><?= htmlspecialchars($cat) ?></span>
          <h3 class="shop-card__name"><?= $name ?></h3>
          <span class="shop-card__sizes"><?= implode(' · ', $sizeTags) ?></span>
          <p class="shop-card__desc"><?= $shortDesc ?></p>
        </div>
        <div class="shop-card__footer">
          <span class="shop-card__price">From $<?= number_format($price, 2) ?></span>
          <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($sku) ?>" class="shop-card__btn">View Details &rarr;</a>
        </div>
        <?php if ($stockStatus === 'Out of Stock'): ?>
          <div style="position: absolute; top: 12px; right: 12px; background: #DC2626; color: white; font-size: 10px; font-weight: 600; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px;">Out of Stock</div>
        <?php elseif ($stockStatus === 'Low Stock'): ?>
          <div style="position: absolute; top: 12px; right: 12px; background: #D97706; color: white; font-size: 10px; font-weight: 600; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px;">Low Stock</div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php elseif ($apiDown && !empty($products)): ?>
      <?php foreach ($products as $slug => $p):
        if (!empty($p['hidden'])) continue;
        $catKey = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($p['category'])));
        $imgDir = $base_path . 'images/products/' . $slug . '/images/';
        $imgFile = '';
        if (is_dir(__DIR__ . '/../images/products/' . $slug . '/images/')) {
          $allImgFiles = scandir(__DIR__ . '/../images/products/' . $slug . '/images/');
          foreach ($allImgFiles as $f) {
            if (stripos($f, 'mobile') !== false) continue;
            if (stripos($f, '220') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $imgFile = $imgDir . $f; break; }
          }
          if (!$imgFile) {
            foreach ($allImgFiles as $f) {
              if ($f === '.' || $f === '..') continue;
              if (stripos($f, 'COA') !== false || stripos($f, 'mobile') !== false) continue;
              if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $imgFile = $imgDir . $f; break; }
            }
          }
        }
      ?>
      <div class="shop-card fade-up" data-category="<?= $catKey ?>">
        <div class="shop-card__img">
          <?php if ($imgFile): ?>
            <img src="<?= $imgFile ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php else: ?>
            <span class="shop-card__img-placeholder"><?= htmlspecialchars($p['name']) ?></span>
          <?php endif; ?>
        </div>
        <div class="shop-card__body">
          <span class="shop-card__cat"><?= $p['category'] ?></span>
          <h3 class="shop-card__name"><?= htmlspecialchars($p['name']) ?></h3>
          <p class="shop-card__desc"><?= htmlspecialchars($p['short_desc']) ?></p>
        </div>
        <div class="shop-card__footer">
          <span class="shop-card__price">From $<?= number_format($p['starting_price'], 2) ?></span>
          <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($slug) ?>" class="shop-card__btn">View Details &rarr;</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="text-align: center; padding: 60px 0; width: 100%;">
        <h3 style="color: var(--navy); margin-bottom: 12px;">No products found</h3>
        <p style="color: var(--gray-600);">Check back soon — we're updating our catalog.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Can't Find Section -->
<section class="section section--white section-border" style="padding: 60px 40px;">
  <div class="section-inner" style="text-align: center; max-width: var(--max-width); margin: 0 auto;">
    <p class="section-label">Can't Find What You're Looking For?</p>
    <h2 class="fade-up" style="margin-bottom: 12px;">We Can Source It</h2>
    <hr class="teal-rule teal-rule--wide teal-rule--center" style="margin: 20px auto;">
    <p class="fade-up" style="max-width: 500px; margin: 0 auto 24px;">We offer a curated selection, but our supplier network extends much further. Tell us what you need and we'll see if we can source it for you.</p>
    <a href="<?= SHOP_URL ?>/support?type=product_request" class="btn btn--green fade-up">Request a Product</a>
  </div>
</section>

<!-- Bottom CTA -->
<section class="section section--white section-border">
  <div class="section-inner" style="text-align:center;">
    <p class="section-label">Quality Assurance</p>
    <h2 class="fade-up">Every Compound. Every Batch. Tested.</h2>
    <hr class="teal-rule teal-rule--wide teal-rule--center" style="margin:20px auto;">
    <p class="fade-up" style="max-width:500px;margin:0 auto 24px;">All ClarityLabs compounds undergo independent third-party testing before distribution. COAs available on request.</p>
    <a href="<?= SITE_URL ?>/#testing" class="btn btn--green fade-up">View Testing Standards</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Client-side category filtering (matches main site behavior)
function filterCategory(category) {
  document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.classList.toggle('active', pill.dataset.category === category);
  });
  document.querySelectorAll('.shop-card').forEach(card => {
    if (category === 'all' || card.dataset.category === category) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });
}
</script>

</body>
</html>
