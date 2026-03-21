<?php
/* ============================================================
   ClarityLabsUSA — Product Detail Page
   Gated: requires age verification + login
   Product data from clarity-ops API + local marketing content
   ============================================================ */

$base_path = '../';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';
require_once __DIR__ . '/../includes/api-client.php';

access_guard();

// Get product by SKU
$sku = $_GET['sku'] ?? '';
if (empty($sku)) {
    header('Location: ' . SHOP_URL . '/');
    exit;
}

$api = new ClarityApiClient();
$response = $api->getProduct($sku);

if (!$response['success'] || empty($response['data'])) {
    // Product not found — redirect to shop
    header('Location: ' . SHOP_URL . '/?error=product_not_found');
    exit;
}

$product = $response['data'];

// Load local marketing content if available
$slug = $product['slug'] ?? strtolower(str_replace(' ', '-', $product['name'] ?? ''));
$localContent = [];
$contentFile = __DIR__ . '/../includes/product-content.php';
if (file_exists($contentFile)) {
    require_once $contentFile;
    $localContent = $productContent[$slug] ?? [];
}

$page_title = $product['name'] ?? 'Product';
$page_description = $product['short_description'] ?? '';

$name          = htmlspecialchars($product['name'] ?? '');
$category      = htmlspecialchars($product['category'] ?? '');
$compound      = htmlspecialchars($product['compound'] ?? '');
$shortDesc     = $product['short_description'] ?? '';
$longDesc      = $product['long_description'] ?? '';
$mgSpec        = $product['mg_specification'] ?? '';
$salePrice     = $product['sale_price'] ?? 0;
$stockStatus   = $product['stock_status'] ?? 'Unknown';
$primaryImage  = $product['primary_image'] ?? '';
$galleryImages = $product['gallery_images'] ?? [];
$coaPdf        = $product['coa_pdf'] ?? '';
$coaPreview    = $product['coa_preview'] ?? '';
$tags          = $product['tags'] ?? [];
$relatedProducts = $product['related_products'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <?= csrf_meta() ?>
  <style>
    .product-detail { padding: 40px 0 80px; }

    .product-detail__breadcrumb {
      font-size: 13px;
      color: var(--gray-400);
      margin-bottom: 28px;
    }
    .product-detail__breadcrumb a { color: var(--green); }

    .product-detail__hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 48px;
      align-items: start;
    }

    /* Image Gallery */
    .product-gallery { position: sticky; top: 100px; }

    .product-gallery__main {
      width: 100%;
      border-radius: 16px;
      background: var(--gray-50);
      overflow: hidden;
      margin-bottom: 12px;
    }

    .product-gallery__main img {
      width: 100%;
      height: auto;
      display: block;
    }

    .product-gallery__thumbs {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .product-gallery__thumb {
      width: 72px;
      height: 72px;
      border-radius: 8px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
      transition: border-color 0.2s;
    }

    .product-gallery__thumb.active,
    .product-gallery__thumb:hover {
      border-color: var(--green);
    }

    .product-gallery__thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Product Info */
    .product-info__cat {
      font-family: var(--font-mono);
      font-size: 11px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--green);
      margin-bottom: 8px;
    }

    .product-info__name {
      font-size: 36px;
      line-height: 1.15;
      margin-bottom: 8px;
    }

    .product-info__badge {
      display: inline-block;
      font-family: var(--font-mono);
      font-size: 10px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--gray-400);
      background: var(--gray-50);
      padding: 4px 12px;
      border-radius: 4px;
      margin-bottom: 16px;
    }

    .product-info__desc {
      font-size: 15px;
      color: var(--gray-600);
      line-height: 1.75;
      margin-bottom: 24px;
    }

    .product-info__price {
      font-size: 28px;
      font-weight: 600;
      color: var(--navy);
      margin-bottom: 4px;
    }

    .product-info__stock {
      font-size: 13px;
      margin-bottom: 20px;
    }

    .product-info__stock--in { color: #059669; }
    .product-info__stock--low { color: #D97706; }
    .product-info__stock--out { color: #DC2626; }

    /* Size Selector */
    .product-sizes { margin-bottom: 20px; }
    .product-sizes__label {
      font-size: 13px;
      font-weight: 600;
      color: var(--navy);
      margin-bottom: 8px;
    }

    .product-sizes__grid {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .product-size-btn {
      padding: 10px 20px;
      border: 2px solid var(--gray-200);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      color: var(--navy);
      cursor: pointer;
      transition: all 0.15s;
      background: var(--white);
    }

    .product-size-btn:hover { border-color: var(--green); }
    .product-size-btn.active {
      border-color: var(--green);
      background: var(--green-bg);
      color: var(--green);
    }

    /* Qty + Add to Cart */
    .product-actions {
      display: flex;
      gap: 12px;
      align-items: center;
      margin-bottom: 24px;
    }

    .product-qty {
      display: flex;
      align-items: center;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      overflow: hidden;
    }

    .product-qty button {
      width: 40px;
      height: 44px;
      font-size: 18px;
      color: var(--navy);
      background: var(--gray-50);
      cursor: pointer;
      border: none;
    }

    .product-qty input {
      width: 50px;
      height: 44px;
      text-align: center;
      border: none;
      font-size: 15px;
      font-weight: 500;
      color: var(--navy);
    }

    .product-atc {
      flex: 1;
      padding: 14px 24px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      color: var(--white);
      background: linear-gradient(135deg, var(--green), var(--navy));
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .product-atc:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26, 122, 110, 0.35);
    }

    .product-atc:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    /* COA Link */
    .product-coa-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--green);
      font-weight: 500;
      margin-bottom: 20px;
    }

    /* Tags */
    .product-tags {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-bottom: 24px;
    }

    .product-tag {
      font-size: 11px;
      padding: 4px 10px;
      border-radius: 20px;
      background: var(--gray-50);
      color: var(--gray-600);
      border: 1px solid var(--gray-200);
    }

    /* Disclaimer */
    .product-disclaimer {
      font-size: 11px;
      color: var(--gray-400);
      line-height: 1.6;
      padding: 16px;
      background: var(--gray-50);
      border-radius: 8px;
      border: 1px solid var(--rule);
    }

    /* Added to cart toast */
    .cart-toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--navy);
      color: var(--white);
      padding: 16px 24px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 500;
      z-index: 9999;
      transform: translateX(120%);
      transition: transform 0.3s ease;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    .cart-toast.show { transform: translateX(0); }

    @media (max-width: 768px) {
      .product-detail__hero { grid-template-columns: 1fr; gap: 24px; }
      .product-gallery { position: static; }
      .product-info__name { font-size: 28px; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <section class="product-detail">
      <div class="container">
        <!-- Breadcrumb -->
        <div class="product-detail__breadcrumb">
          <a href="<?= SHOP_URL ?>/">Shop</a> &rarr;
          <?php if ($category): ?><a href="<?= SHOP_URL ?>/?category=<?= urlencode($category) ?>"><?= $category ?></a> &rarr;<?php endif; ?>
          <?= $name ?>
        </div>

        <!-- Hero: Image + Info -->
        <div class="product-detail__hero">
          <!-- Gallery -->
          <div class="product-gallery">
            <div class="product-gallery__main">
              <?php if ($primaryImage): ?>
                <img src="<?= htmlspecialchars($primaryImage) ?>" alt="<?= $name ?>" id="main-image">
              <?php else: ?>
                <div style="height: 400px; display: flex; align-items: center; justify-content: center; color: var(--gray-400);">No Image Available</div>
              <?php endif; ?>
            </div>
            <?php if (count($galleryImages) > 1): ?>
              <div class="product-gallery__thumbs">
                <?php foreach ($galleryImages as $i => $img): ?>
                  <div class="product-gallery__thumb <?= $i === 0 ? 'active' : '' ?>" onclick="switchImage('<?= htmlspecialchars($img) ?>', this)">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= $name ?> view <?= $i + 1 ?>">
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Product Info -->
          <div class="product-info">
            <div class="product-info__cat"><?= $category ?></div>
            <h1 class="product-info__name"><?= $name ?></h1>

            <?php if ($mgSpec): ?>
              <span class="product-info__badge"><?= htmlspecialchars($mgSpec) ?></span>
            <?php endif; ?>

            <p class="product-info__desc"><?= htmlspecialchars($shortDesc) ?></p>

            <div class="product-info__price" id="display-price">$<?= number_format($salePrice, 2) ?></div>

            <div class="product-info__stock product-info__stock--<?= $stockStatus === 'In Stock' ? 'in' : ($stockStatus === 'Low Stock' ? 'low' : 'out') ?>">
              &#9679; <?= htmlspecialchars($stockStatus) ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($tags)): ?>
              <div class="product-tags">
                <?php foreach ($tags as $tag): ?>
                  <span class="product-tag"><?= htmlspecialchars(is_array($tag) ? $tag['name'] : $tag) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Qty + Add to Cart -->
            <div class="product-actions">
              <div class="product-qty">
                <button type="button" onclick="changeQty(-1)">−</button>
                <input type="number" id="product-qty" value="1" min="1" max="50">
                <button type="button" onclick="changeQty(1)">+</button>
              </div>
              <button type="button" class="product-atc" id="add-to-cart-btn"
                      <?= $stockStatus === 'Out of Stock' ? 'disabled' : '' ?>
                      onclick="addToCart()">
                <?= $stockStatus === 'Out of Stock' ? 'Out of Stock' : 'Add to Cart' ?>
              </button>
            </div>

            <!-- COA -->
            <?php if ($coaPdf): ?>
              <a href="<?= htmlspecialchars($coaPdf) ?>" target="_blank" class="product-coa-link">
                &#128196; View Certificate of Analysis (COA)
              </a>
            <?php endif; ?>

            <!-- Disclaimer -->
            <div class="product-disclaimer">
              <?= htmlspecialchars(COMPANY_DISCLAIMER) ?>
            </div>
          </div>
        </div>

        <!-- Long Description -->
        <?php if ($longDesc): ?>
          <section style="padding: 60px 0 0; max-width: 800px;">
            <h2 style="font-size: 28px; margin-bottom: 16px;">About <?= $name ?></h2>
            <div style="font-size: 15px; line-height: 1.8; color: var(--gray-600);">
              <?= nl2br(htmlspecialchars($longDesc)) ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
          <section style="padding: 60px 0 0;">
            <h2 style="font-size: 28px; margin-bottom: 24px;">Related Compounds</h2>
            <div class="products-grid" style="max-width: 900px;">
              <?php foreach (array_slice($relatedProducts, 0, 3) as $rp): ?>
                <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($rp['sku'] ?? '') ?>" class="product-card">
                  <div class="product-card__body">
                    <span class="product-card__cat"><?= htmlspecialchars($rp['category'] ?? '') ?></span>
                    <h4 class="product-card__name"><?= htmlspecialchars($rp['name'] ?? '') ?></h4>
                    <div class="product-card__price">$<?= number_format($rp['sale_price'] ?? 0, 2) ?></div>
                    <span class="product-card__cta">View Details &rarr;</span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <!-- Add to Cart Toast -->
  <div class="cart-toast" id="cart-toast"></div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  // Image gallery
  function switchImage(src, thumb) {
    document.getElementById('main-image').src = src;
    document.querySelectorAll('.product-gallery__thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
  }

  // Quantity
  function changeQty(delta) {
    const input = document.getElementById('product-qty');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 50) val = 50;
    input.value = val;
  }

  // Add to cart
  async function addToCart() {
    const btn = document.getElementById('add-to-cart-btn');
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Adding...';

    const formData = new FormData();
    formData.append('_csrf_token', csrfToken);
    formData.append('sku', <?= json_encode($sku) ?>);
    formData.append('name', <?= json_encode($product['name'] ?? '') ?>);
    formData.append('size', <?= json_encode($mgSpec) ?>);
    formData.append('price', <?= json_encode($salePrice) ?>);
    formData.append('qty', document.getElementById('product-qty').value);
    formData.append('image_url', <?= json_encode($primaryImage) ?>);

    try {
      const res = await fetch('<?= SHOP_URL ?>/php/cart-actions.php?action=add', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Added to cart!');
        // Update cart count in header if element exists
        const cartBadge = document.getElementById('cart-badge');
        if (cartBadge) {
          cartBadge.textContent = data.cart_count;
          cartBadge.style.display = 'flex';
        }
      } else {
        showToast(data.error || 'Failed to add to cart.', true);
      }
    } catch (err) {
      showToast('Something went wrong. Please try again.', true);
    }

    btn.disabled = false;
    btn.textContent = origText;
  }

  function showToast(message, isError = false) {
    const toast = document.getElementById('cart-toast');
    toast.textContent = message;
    toast.style.background = isError ? '#DC2626' : 'var(--navy)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
  }
  </script>
</body>
</html>
