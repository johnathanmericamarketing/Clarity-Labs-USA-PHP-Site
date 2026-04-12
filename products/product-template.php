<?php
/* ============================================================
   ClarityLabsUSA — Product Detail Template (Shopify-style)
   Renders any product from $product array
   ============================================================ */


// Set SEO variables for head.php OG tags
$page_type = 'product';
$page_url = (defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com') . '/products/?product=' . urlencode($slug);
if (!empty($product['sizes'][0]['primary_image'])) {
    $page_image = $product['sizes'][0]['primary_image'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include $base_path . 'includes/head.php'; ?>
</head>
<body>

<?php include $base_path . 'includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
  <div class="breadcrumb__inner">
    <a href="<?php echo defined('SITE_URL') ? SITE_URL : $base_path . 'index.php'; ?>">ClarityLabsUSA</a>
    <span class="breadcrumb__sep">/</span>
    <a href="<?php echo defined('SHOP_URL') ? SHOP_URL : $base_path . 'shop/'; ?>">Shop</a>
    <span class="breadcrumb__sep">/</span>
    <span class="breadcrumb__current"><?php echo htmlspecialchars($product['name']); ?></span>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     PRODUCT HERO — Two-Column Shopify Layout
     ═══════════════════════════════════════════ -->
<section class="product-hero">
  <div class="product-hero__inner">

    <!-- Left: Image Gallery -->
    <div class="product-hero__gallery fade-up">
      <div class="product-hero__main-img">
        <?php
          // ── Build image sources: API first, then local filesystem fallback ──
          $heroImg = '';
          $galleryImages = [];
          $coaPreview = '';
          $coaPdf = '';

          // 1. Check API images (from ops/R2)
          $apiPrimary = $product['api_primary_image'] ?? '';
          $apiGallery = $product['api_gallery_images'] ?? [];
          $apiCoaPdf  = $product['api_coa_pdf'] ?? '';
          $apiCoaPreview = $product['api_coa_preview'] ?? '';

          if (!empty($apiPrimary)) {
              $heroImg = $apiPrimary;
          }
          if (!empty($apiGallery) && is_array($apiGallery)) {
              $galleryImages = $apiGallery;
          }
          if (!empty($apiCoaPreview)) {
              $coaPreview = $apiCoaPreview;
          }
          if (!empty($apiCoaPdf)) {
              $coaPdf = $apiCoaPdf;
          }

          // 2. Fallback: local filesystem (legacy)
          $heroDir = $base_path . 'images/products/' . $slug . '/images/';
          if (!$heroImg) {
              if (is_dir($heroDir)) {
                  $allFiles = scandir($heroDir);
                  foreach ($allFiles as $f) {
                      if (stripos($f, '800') !== false) { $heroImg = $heroDir . $f; break; }
                  }
                  if (!$heroImg) {
                      foreach ($allFiles as $f) {
                          if ($f === '.' || $f === '..') continue;
                          if (stripos($f, 'COA') !== false || stripos($f, '220') !== false) continue;
                          if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $heroImg = $heroDir . $f; break; }
                      }
                  }
              }
          }

          // Fallback COA PDF from local
          if (!$coaPdf) {
              $pdfDir = $base_path . 'images/products/' . $slug . '/pdf/';
              if (is_dir($pdfDir)) {
                  foreach (scandir($pdfDir) as $f) {
                      if ($f === '.' || $f === '..') continue;
                      if (preg_match('/\.pdf$/i', $f)) { $coaPdf = $pdfDir . $f; break; }
                  }
              }
          }

          if ($heroImg):
        ?>
          <img src="<?php echo htmlspecialchars($heroImg); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="hero-main-image">
        <?php else: ?>
          <span class="placeholder-text"><?php echo htmlspecialchars($product['name']); ?></span>
        <?php endif; ?>
      </div>
      <div class="product-hero__thumbs">
        <?php
          // Build thumbnail array
          $thumbs = [];

          // Thumb 1: Primary product image
          if ($heroImg) {
              $thumbs[] = ['preview' => $heroImg, 'full' => $heroImg, 'pdf' => ''];
          }

          // Additional gallery images from API
          foreach ($galleryImages as $gImg) {
              if (!empty($gImg) && $gImg !== $heroImg) {
                  $thumbs[] = ['preview' => $gImg, 'full' => $gImg, 'pdf' => ''];
              }
          }

          // COA preview image (clickable, links to COA PDF if available)
          if ($coaPreview) {
              $thumbs[] = ['preview' => $coaPreview, 'full' => $coaPreview, 'pdf' => $coaPdf];
          } elseif (!$coaPreview && $coaPdf) {
              // No COA preview image but have PDF — show a "COA" text thumb
              // Skip — will show link below instead
          }

          // Fallback: local filesystem thumbnails if no API images
          if (empty($thumbs)) {
              $heroDir = $base_path . 'images/products/' . $slug . '/images/';
              if (is_dir($heroDir)) {
                  $thumbProduct = '';
                  $thumbCoa = '';
                  foreach (scandir($heroDir) as $f) {
                      if (stripos($f, '220') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $thumbProduct = $heroDir . $f; break; }
                  }
                  foreach (scandir($heroDir) as $f) {
                      if (stripos($f, 'COA') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $thumbCoa = $heroDir . $f; break; }
                  }
                  if ($thumbProduct) { $thumbs[] = ['preview' => $thumbProduct, 'full' => $heroImg ?: $thumbProduct, 'pdf' => '']; }
                  if ($thumbCoa) { $thumbs[] = ['preview' => $thumbCoa, 'full' => $thumbCoa, 'pdf' => $coaPdf]; }
              }
          }

          $idx = 0;
          foreach ($thumbs as $t): $idx++;
        ?>
        <div class="product-hero__thumb <?php echo $idx === 1 ? 'active' : ''; ?>"
             data-src="<?php echo htmlspecialchars($t['full']); ?>"
             <?php if (!empty($t['pdf'])): ?>data-pdf="<?php echo htmlspecialchars($t['pdf']); ?>"<?php endif; ?>>
          <img src="<?php echo htmlspecialchars($t['preview']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> view <?php echo $idx; ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($coaPdf): ?>
      <a href="<?php echo htmlspecialchars($coaPdf); ?>" target="_blank" class="product-hero__coa-link">View COA (PDF) &darr;</a>
      <?php endif; ?>
    </div>

    <!-- Right: Product Info Panel -->
    <div class="product-hero__info fade-up stagger-1">
      <!-- Compound Pill -->
      <div class="product-pill">
        <span>&#9679;</span>
        <span><?php echo htmlspecialchars($product['badge']); ?></span>
      </div>

      <!-- Name -->
      <h1><?php echo htmlspecialchars($product['name']); ?></h1>

      <!-- Tagline -->
      <p class="product-tagline"><?php echo htmlspecialchars($product['tagline']); ?></p>

      <!-- Research Grade Badge -->
      <div class="product-badge">&#10003; Research Grade</div>

      <!-- Price -->
      <?php $defaultIdx = $product['default_size_index'] ?? 0; ?>
      <div class="product-price" id="product-price">$<?php echo number_format($product['sizes'][$defaultIdx]['price'], 2); ?></div>

      <!-- Short Description -->
      <p class="product-desc"><?php echo htmlspecialchars($product['short_desc']); ?></p>

      <hr class="product-divider">

      <!-- Size Selector -->
      <div class="size-selector" id="size-selector">
        <span class="size-selector__label">Select Size</span>
        <?php foreach ($product['sizes'] as $i => $size): ?>
        <div class="size-option <?php echo $i === $defaultIdx ? 'active' : ''; ?>"
             data-price="<?php echo number_format($size['price'], 2); ?>"
             data-sku="<?php echo htmlspecialchars($size['sku'] ?? ''); ?>"
             data-mg="<?php echo htmlspecialchars($size['mg'] ?? ''); ?>"
             data-stock="<?php echo htmlspecialchars($size['stock_status'] ?? 'Unknown'); ?>"
             data-image="<?php echo htmlspecialchars($size['primary_image'] ?? ''); ?>"
             data-coa-preview="<?php echo htmlspecialchars($size['coa_preview'] ?? ''); ?>"
             data-coa-pdf="<?php echo htmlspecialchars($size['coa_pdf'] ?? ''); ?>"
             data-gallery="<?php echo htmlspecialchars(json_encode($size['gallery_images'] ?? [])); ?>">
          <div class="size-option__left">
            <span class="size-option__mg"><?php echo htmlspecialchars($size['mg']); ?></span>
            <?php if (!empty($size['purity'])): ?>
            <span class="size-option__purity"><?php echo number_format($size['purity'], 2); ?>% Pure</span>
            <?php endif; ?>
            <span class="size-option__phase"><?php echo htmlspecialchars($size['phase'] ?? ''); ?></span>
          </div>
          <?php if (!empty($size['popular'])): ?>
          <span class="size-option__popular">POPULAR</span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <hr class="product-divider">

      <!-- Hidden fields for cart -->
      <input type="hidden" id="selected-sku" value="<?php echo htmlspecialchars($product['sizes'][$defaultIdx]['sku'] ?? $sku ?? ''); ?>">
      <input type="hidden" id="selected-price" value="<?php echo number_format($product['sizes'][$defaultIdx]['price'], 2); ?>">
      <input type="hidden" id="selected-size" value="<?php echo htmlspecialchars($product['sizes'][$defaultIdx]['mg'] ?? ''); ?>">
      <input type="hidden" id="product-name" value="<?php echo htmlspecialchars($product['name']); ?>">
      <input type="hidden" id="product-image" value="<?php echo htmlspecialchars($product['api_primary_image'] ?? ''); ?>">

      <!-- CTA Buttons -->
      <button type="button" class="btn btn--navy btn--block" id="add-to-cart-btn">Add to Cart</button>
      <button type="button" class="btn btn--outline-navy btn--block" id="save-product-btn" style="margin-top: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <svg id="save-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span id="save-label">Save for Later</span>
      </button>

      <!-- Trust Row -->
      <div class="product-trust">
        <div class="product-trust__item">
          <span class="product-trust__icon">&#10003;</span>
          <span>Third-Party Tested</span>
        </div>
        <div class="product-trust__item">
          <span class="product-trust__icon">&#10003;</span>
          <span>COA Available</span>
        </div>
        <div class="product-trust__item">
          <span class="product-trust__icon">&#10003;</span>
          <span>US Shipping</span>
        </div>
      </div>

      <p class="product-micro">For research and laboratory use only.</p>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     WHY RESEARCHERS CHOOSE
     ═══════════════════════════════════════════ -->
<section class="why-section">
  <div class="why-section__inner">
    <div class="why-section__header">
      <div class="why-section__header-left">
        <p class="section-label">Research Profile</p>
        <h2>Why Researchers Choose <?php echo htmlspecialchars($product['name']); ?></h2>
        <hr class="teal-rule teal-rule--wide">
      </div>
      <div class="why-section__header-right">
        <p><?php echo htmlspecialchars($product['research_profile']); ?></p>
      </div>
    </div>
    <div class="why-grid">
      <?php foreach ($product['why_cards'] as $i => $card): $num = str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?>
      <div class="why-card fade-up stagger-<?php echo $i + 1; ?>">
        <div class="why-card__icon-col">
          <div class="why-card__icon-box"><?php echo $card['icon']; ?></div>
          <span class="why-card__num"><?php echo $num; ?></span>
        </div>
        <div class="why-card__text">
          <h4 class="why-card__title"><?php echo htmlspecialchars($card['title']); ?></h4>
          <p class="why-card__body"><?php echo htmlspecialchars($card['desc']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     RESEARCH APPLICATIONS
     ═══════════════════════════════════════════ -->
<?php if (!empty($product['research_apps'])): ?>
<section class="research-apps">
  <div class="research-apps__inner">
    <div style="text-align:center;margin-bottom:48px;">
      <p class="section-label">Research Applications</p>
      <h2 class="fade-up">Areas of Active Study</h2>
      <hr class="teal-rule teal-rule--center" style="margin:16px auto;">
      <?php if (!empty($product['research_apps_intro'])): ?>
      <p class="fade-up" style="max-width:620px;margin:0 auto;"><?php echo htmlspecialchars($product['research_apps_intro']); ?></p>
      <?php endif; ?>
    </div>
    <div class="research-apps__grid">
      <?php foreach ($product['research_apps'] as $i => $app): ?>
      <div class="research-app-card fade-up stagger-<?php echo ($i % 4) + 1; ?>">
        <span class="research-app-card__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
        <h4 class="research-app-card__title"><?php echo htmlspecialchars($app['title']); ?></h4>
        <p class="research-app-card__desc"><?php echo htmlspecialchars($app['desc']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     WHO THIS IS DESIGNED FOR
     ═══════════════════════════════════════════ -->
<?php if (!empty($product['designed_for_profiles'])): ?>
<section class="designed-for">
  <div class="designed-for__inner">
    <div class="designed-for__header">
      <div class="designed-for__header-left">
        <p class="section-label">Researcher Profiles</p>
        <h2 class="fade-up">Who This Is Designed For</h2>
        <hr class="teal-rule teal-rule--wide">
      </div>
      <?php if (!empty($product['designed_for_intro'])): ?>
      <div class="designed-for__header-right">
        <p><?php echo htmlspecialchars($product['designed_for_intro']); ?></p>
      </div>
      <?php endif; ?>
    </div>
    <div class="designed-for__list">
      <?php foreach ($product['designed_for_profiles'] as $i => $profile): ?>
      <div class="designed-for__item fade-up stagger-<?php echo $i + 1; ?>">
        <div class="designed-for__icon">&#9679;</div>
        <div class="designed-for__text">
          <?php if (is_array($profile)): ?>
            <h4><?php echo htmlspecialchars($profile['title']); ?></h4>
            <p><?php echo htmlspecialchars($profile['desc']); ?></p>
          <?php else: ?>
            <p><?php echo htmlspecialchars($profile); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     PROTOCOL CONTEXT
     ═══════════════════════════════════════════ -->
<?php if (!empty($product['protocol_context'])): ?>
<section class="protocol-context section--navy">
  <div class="protocol-context__inner">
    <p class="section-label" style="color:var(--green-rule);">Protocol Context</p>
    <h2 style="color:var(--white);" class="fade-up">Research Protocol Considerations</h2>
    <hr class="teal-rule teal-rule--wide" style="margin-bottom:32px;">
    <div class="protocol-context__layout">
      <div class="protocol-context__content">
        <?php foreach ($product['protocol_context'] as $i => $para): ?>
        <p class="fade-up stagger-<?php echo $i + 1; ?>"><?php echo htmlspecialchars($para); ?></p>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($product['protocol_context_callout'])): ?>
      <aside class="protocol-context__callout fade-up">
        <div class="protocol-context__callout-label">Research Note</div>
        <p class="protocol-context__callout-text"><?php echo htmlspecialchars($product['protocol_context_callout']); ?></p>
      </aside>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     BRAND VALUES
     ═══════════════════════════════════════════ -->
<?php if (!empty($product['brand_values'])): ?>
<section class="brand-values">
  <div class="brand-values__inner">
    <div style="text-align:center;margin-bottom:48px;">
      <p class="section-label">Our Approach</p>
      <h2 class="fade-up">Built on Research Integrity</h2>
      <hr class="teal-rule teal-rule--center" style="margin:16px auto;">
    </div>
    <div class="brand-values__grid">
      <?php foreach ($product['brand_values'] as $i => $val): ?>
      <div class="brand-value-card fade-up stagger-<?php echo $i + 1; ?>">
        <div class="brand-value-card__icon">&#10003;</div>
        <h4 class="brand-value-card__title"><?php echo htmlspecialchars($val['title']); ?></h4>
        <p class="brand-value-card__desc"><?php echo htmlspecialchars($val['desc']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     AVAILABLE SIZES
     ═══════════════════════════════════════════ -->
<section class="sizes-section">
  <div class="sizes-section__inner">
    <div style="text-align:center;margin-bottom:48px;">
      <p class="section-label">Size Options</p>
      <h2 class="fade-up">Available Sizes</h2>
      <hr class="teal-rule teal-rule--center" style="margin:16px auto;">
      <p class="fade-up" style="max-width:560px;margin:0 auto;">Choose the size that fits your research protocol. All sizes undergo the same rigorous third-party testing.</p>
    </div>
    <div class="sizes-grid">
      <?php foreach ($product['sizes'] as $i => $size):
        $featured = !empty($size['popular']);
      ?>
      <div class="size-card <?php echo $featured ? 'size-card--featured' : ''; ?> fade-up stagger-<?php echo $i + 1; ?>">
        <?php if ($featured): ?>
        <span class="size-card__badge">Most Popular</span>
        <?php endif; ?>
        <span class="size-card__phase"><?php echo htmlspecialchars($size['phase']); ?></span>
        <span class="size-card__mg"><?php echo htmlspecialchars($size['mg']); ?></span>
        <?php if (!empty($size['purity'])): ?>
        <span class="size-card__purity"><?php echo number_format($size['purity'], 2); ?>% Pure</span>
        <?php endif; ?>
        <p class="size-card__desc"><?php echo htmlspecialchars($size['card_desc']); ?></p>
        <span class="size-card__note">$<?php echo number_format($size['price'], 2); ?></span>
        <button type="button" class="btn <?php echo $featured ? 'btn--navy' : 'btn--outline-navy'; ?> btn--block js-order-modal-open" data-size="<?php echo htmlspecialchars($size['mg']); ?>" data-sku="<?php echo htmlspecialchars($size['sku'] ?? ''); ?>">Select — <?php echo htmlspecialchars($size['mg']); ?></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     RELATED COMPOUNDS
     ═══════════════════════════════════════════ -->
<?php if (!empty($related)): ?>
<section class="related">
  <div class="related__inner">
    <p class="section-label">Related Compounds</p>
    <h2 class="fade-up">Other Compounds to Explore</h2>
    <div class="related__grid">
      <?php foreach ($related as $rslug => $rp): ?>
      <a href="<?php echo $base_path; ?>products/index.php?product=<?php echo $rslug; ?>" class="compound-card fade-up">
        <span class="compound-card__cat"><?php echo htmlspecialchars($rp['category']); ?></span>
        <span class="compound-card__name"><?php echo htmlspecialchars($rp['name']); ?></span>
        <span class="compound-card__desc"><?php echo htmlspecialchars($rp['short_desc']); ?></span>
        <span class="compound-card__link">View Compound &rarr;</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     MINI COA TRUST SECTION
     ═══════════════════════════════════════════ -->
<section id="coa-section" class="section" style="background:var(--white);">
  <div class="section-inner" style="text-align:center;">
    <p class="section-label">Quality Assurance</p>
    <h2 class="fade-up">Every Compound. Every Batch. Tested.</h2>
    <hr class="teal-rule teal-rule--center" style="margin:16px auto;">
    <p class="fade-up" style="max-width:520px;margin:0 auto 24px;">
      <?php echo htmlspecialchars($product['name']); ?> undergoes independent third-party laboratory analysis before distribution. Certificates of Analysis are available on request for every lot.
    </p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:32px;">
      <span class="lab-badge lab-badge--light">&#10003; Identity Verified</span>
      <span class="lab-badge lab-badge--light">&#10003; Purity Tested</span>
      <span class="lab-badge lab-badge--light">&#10003; Contaminant Screened</span>
    </div>
    <?php
      $coaFile = '';
      $coaDir = $base_path . 'images/products/' . $slug . '/pdf/';
      if (is_dir($coaDir)) {
        foreach (scandir($coaDir) as $f) {
          if ($f === '.' || $f === '..') continue;
          if (preg_match('/\.pdf$/i', $f)) { $coaFile = $coaDir . $f; break; }
        }
      }
      if ($coaFile):
    ?>
    <a href="<?php echo $coaFile; ?>" class="btn btn--green" target="_blank">View COA (PDF)</a>
    <?php else: ?>
    <a href="<?php echo $base_path; ?>contact.php" class="btn btn--green">Request COA</a>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     ORDER FORM MODAL (3-Step)
     ═══════════════════════════════════════════ -->
<div class="order-modal" id="order-modal">
  <div class="order-modal__overlay"></div>
  <div class="order-modal__container">
    <div class="order-modal__header">
      <span class="order-modal__title">Place Order</span>
      <button class="order-modal__close" id="order-modal-close">&times;</button>
    </div>

    <!-- Step Indicator -->
    <div class="order-steps">
      <div class="order-steps__item active" data-step="1">
        <span class="order-steps__num">1</span>
        <span class="order-steps__label">Product</span>
      </div>
      <div class="order-steps__line"></div>
      <div class="order-steps__item" data-step="2">
        <span class="order-steps__num">2</span>
        <span class="order-steps__label">Contact</span>
      </div>
      <div class="order-steps__line"></div>
      <div class="order-steps__item" data-step="3">
        <span class="order-steps__num">3</span>
        <span class="order-steps__label">Shipping</span>
      </div>
    </div>

    <div class="order-modal__body">
      <!-- Success Message -->
      <div id="order-form-success">
        <div class="order-success__icon">&#10003;</div>
        <h3>Order Received</h3>
        <p>Thank you! We'll be in touch shortly to confirm your order and arrange payment.</p>
      </div>

      <!-- Legacy order form removed 2026-04-11. All orders go through the authenticated shop checkout. -->
      <div style="text-align: center; padding: 32px 24px;">
        <div style="font-size: 48px; margin-bottom: 16px;">&#128722;</div>
        <h3 style="margin: 0 0 8px; font-size: 20px; color: #0B1E3F;">Ready to Order?</h3>
        <p style="margin: 0 0 20px; font-size: 14px; color: #6B7185; line-height: 1.6;">Create a free account to browse products, view pricing, and place secure orders with tracking.</p>
        <a href="https://shop.claritylabsusa.com/" class="btn btn--navy" style="display: inline-block; padding: 14px 36px; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">Shop Now &rarr;</a>
      </div>
    </div>
  </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>

<script>
(function() {
  // GA4 view_item event on page load
  if (typeof ClarityAnalytics !== 'undefined') {
    ClarityAnalytics.viewItem({
      name: <?= json_encode($product['name']) ?>,
      sku: <?= json_encode($defaultSize['sku'] ?? '') ?>,
      price: <?= json_encode($defaultSize['price'] ?? $product['starting_price'] ?? 0) ?>,
      category: <?= json_encode($product['category'] ?? '') ?>
    });
  }

  // ── Thumb click handler: swap hero image ──
  function thumbClickHandler() {
    var src = this.getAttribute('data-src');
    var heroImg = document.getElementById('hero-main-image');
    if (heroImg && src) heroImg.src = src;
    document.querySelectorAll('.product-hero__thumb').forEach(function(t) { t.classList.remove('active'); });
    this.classList.add('active');
  }
  document.querySelectorAll('.product-hero__thumb').forEach(function(t) {
    t.addEventListener('click', thumbClickHandler);
  });

  // ── Size selector: update price, SKU, and hidden fields on click ──
  var sizeOptions = document.querySelectorAll('#size-selector .size-option');
  var priceDisplay = document.getElementById('product-price');
  var skuInput = document.getElementById('selected-sku');
  var priceInput = document.getElementById('selected-price');
  var sizeInput = document.getElementById('selected-size');
  // Also update modal hidden fields if they exist
  var orderSizeInput = document.getElementById('order-size-input');
  var orderPriceInput = document.getElementById('order-price-input');
  var orderSkuInput = document.getElementById('order-sku-input');

  sizeOptions.forEach(function(opt) {
    opt.addEventListener('click', function() {
      // Update active state
      sizeOptions.forEach(function(o) { o.classList.remove('active'); });
      opt.classList.add('active');

      var price = opt.getAttribute('data-price');
      var sku = opt.getAttribute('data-sku');
      var mg = opt.getAttribute('data-mg');

      // Update hero price display
      if (priceDisplay) priceDisplay.textContent = '$' + price;
      // Update hidden fields for cart
      if (skuInput) skuInput.value = sku;
      if (priceInput) priceInput.value = price;
      if (sizeInput) sizeInput.value = mg;
      // Update modal fields
      if (orderSizeInput) orderSizeInput.value = mg;
      if (orderPriceInput) orderPriceInput.value = price;
      if (orderSkuInput) orderSkuInput.value = sku;

      // Update modal price display
      var orderPriceDisplay = document.getElementById('order-price-display');
      if (orderPriceDisplay) orderPriceDisplay.textContent = '$' + price;

      // ── Swap product images when MG size changes ──
      var newImage = opt.getAttribute('data-image');
      var newCoaPreview = opt.getAttribute('data-coa-preview');
      var newCoaPdf = opt.getAttribute('data-coa-pdf');
      var newGallery = [];
      try { newGallery = JSON.parse(opt.getAttribute('data-gallery') || '[]'); } catch(e) {}

      var heroMainImg = document.getElementById('hero-main-image');
      var thumbsContainer = document.querySelector('.product-hero__thumbs');
      var coaLink = document.querySelector('.product-hero__coa-link');

      // Update hero image
      if (newImage && heroMainImg) {
          heroMainImg.src = newImage;
          heroMainImg.alt = opt.getAttribute('data-mg') + ' ' + document.getElementById('product-name').value;
      }

      // Rebuild thumbnails
      if (thumbsContainer && (newImage || newCoaPreview)) {
          thumbsContainer.innerHTML = '';
          var thumbIdx = 0;

          // Product image thumb
          if (newImage) {
              thumbIdx++;
              var div = document.createElement('div');
              div.className = 'product-hero__thumb' + (thumbIdx === 1 ? ' active' : '');
              div.setAttribute('data-src', newImage);
              div.innerHTML = '<img src="' + newImage + '" alt="Product view ' + thumbIdx + '">';
              div.addEventListener('click', thumbClickHandler);
              thumbsContainer.appendChild(div);
          }

          // Gallery images
          newGallery.forEach(function(gImg) {
              if (gImg && gImg !== newImage) {
                  thumbIdx++;
                  var div = document.createElement('div');
                  div.className = 'product-hero__thumb';
                  div.setAttribute('data-src', gImg);
                  div.innerHTML = '<img src="' + gImg + '" alt="Product view ' + thumbIdx + '">';
                  div.addEventListener('click', thumbClickHandler);
                  thumbsContainer.appendChild(div);
              }
          });

          // COA preview thumb
          if (newCoaPreview) {
              thumbIdx++;
              var div = document.createElement('div');
              div.className = 'product-hero__thumb';
              div.setAttribute('data-src', newCoaPreview);
              if (newCoaPdf) div.setAttribute('data-pdf', newCoaPdf);
              div.innerHTML = '<img src="' + newCoaPreview + '" alt="COA Preview">';
              div.addEventListener('click', thumbClickHandler);
              thumbsContainer.appendChild(div);
          }
      }

      // Update COA link
      if (coaLink) {
          if (newCoaPdf) {
              coaLink.href = newCoaPdf;
              coaLink.style.display = '';
          } else {
              coaLink.style.display = 'none';
          }
      }

      // Update hidden product image for cart
      var productImageInput = document.getElementById('product-image');
      if (productImageInput && newImage) productImageInput.value = newImage;

      // Update modal radio to match
      var radios = document.querySelectorAll('input[name="selected_size"]');
      radios.forEach(function(r) {
        var label = r.closest('.order-size-option');
        if (r.value === mg) {
          r.checked = true;
          if (label) label.classList.add('active');
        } else {
          r.checked = false;
          if (label) label.classList.remove('active');
        }
      });
    });
  });

  // ── Modal radio size change → update price/SKU ──
  var modalRadios = document.querySelectorAll('input[name="selected_size"]');
  modalRadios.forEach(function(radio) {
    radio.addEventListener('change', function() {
      var price = radio.getAttribute('data-price');
      var sku = radio.getAttribute('data-sku');
      var mg = radio.value;
      // Update all displays
      if (priceDisplay) priceDisplay.textContent = '$' + price;
      if (skuInput) skuInput.value = sku;
      if (priceInput) priceInput.value = price;
      if (sizeInput) sizeInput.value = mg;
      if (orderSizeInput) orderSizeInput.value = mg;
      if (orderPriceInput) orderPriceInput.value = price;
      if (orderSkuInput) orderSkuInput.value = sku;
      var orderPriceDisplay = document.getElementById('order-price-display');
      if (orderPriceDisplay) orderPriceDisplay.textContent = '$' + price;
      // Update hero size selector
      sizeOptions.forEach(function(o) {
        o.classList.toggle('active', o.getAttribute('data-sku') === sku);
      });
      // Update radio labels
      modalRadios.forEach(function(r) {
        var label = r.closest('.order-size-option');
        if (label) label.classList.toggle('active', r === radio);
      });
    });
  });

  // ── Add to Cart button ──
  var addBtn = document.getElementById('add-to-cart-btn');
  if (addBtn) {
    addBtn.addEventListener('click', function() {
      var sku = skuInput ? skuInput.value : '';
      var name = document.getElementById('product-name') ? document.getElementById('product-name').value : '';
      var size = sizeInput ? sizeInput.value : '';
      var price = priceInput ? priceInput.value : '';
      var imageUrl = document.getElementById('product-image') ? document.getElementById('product-image').value : '';

      if (!sku || !name) return;

      addBtn.disabled = true;
      addBtn.textContent = 'Adding...';

      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

      var formData = new FormData();
      formData.append('sku', sku);
      formData.append('name', name);
      formData.append('size', size);
      formData.append('price', price);
      formData.append('qty', '1');
      formData.append('image_url', imageUrl);
      formData.append('_csrf_token', csrfToken);

      var cartUrl = '<?php echo defined("SHOP_URL") ? SHOP_URL : ""; ?>/php/cart-actions.php?action=add';

      fetch(cartUrl, { method: 'POST', body: formData, credentials: 'include' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success) {
            addBtn.textContent = 'Added! Going to cart...';
            // GA4 add_to_cart event
            if (typeof ClarityAnalytics !== 'undefined') {
              ClarityAnalytics.addToCart({ name: name, sku: sku, price: price, quantity: 1 });
            }
            // Redirect to cart page
            window.location.href = '<?php echo defined("SHOP_URL") ? SHOP_URL : ""; ?>/cart';
          } else {
            addBtn.textContent = data.error || 'Error';
            setTimeout(function() { addBtn.textContent = 'Add to Cart'; addBtn.disabled = false; }, 2000);
          }
        })
        .catch(function() {
          addBtn.textContent = 'Error';
          setTimeout(function() { addBtn.textContent = 'Add to Cart'; addBtn.disabled = false; }, 2000);
        });
    });
  }
  // ── Save for Later (Wishlist) button ──
  var saveBtn = document.getElementById('save-product-btn');
  var saveIcon = document.getElementById('save-icon');
  var saveLabel = document.getElementById('save-label');
  var isSaved = false;

  // Check if product is already saved
  var initialSku = skuInput ? skuInput.value : '';
  if (initialSku && saveBtn) {
    fetch('<?php echo defined("SHOP_URL") ? SHOP_URL : ""; ?>/php/wishlist-actions.php?action=check&sku=' + encodeURIComponent(initialSku), { credentials: 'include' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.saved) {
          isSaved = true;
          saveIcon.setAttribute('fill', 'currentColor');
          saveLabel.textContent = 'Saved';
          saveBtn.classList.remove('btn--outline-navy');
          saveBtn.classList.add('btn--green');
        }
      })
      .catch(function() {});
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function() {
      var sku = skuInput ? skuInput.value : '';
      if (!sku) return;
      var action = isSaved ? 'remove' : 'add';
      saveBtn.disabled = true;

      var wishCsrfMeta = document.querySelector('meta[name="csrf-token"]');
      var wishCsrfToken = wishCsrfMeta ? wishCsrfMeta.getAttribute('content') : '';

      fetch('<?php echo defined("SHOP_URL") ? SHOP_URL : ""; ?>/php/wishlist-actions.php?action=' + action + '&sku=' + encodeURIComponent(sku), {
        method: 'POST',
        credentials: 'include',
        headers: { 'X-CSRF-TOKEN': wishCsrfToken }
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          isSaved = !isSaved;
          if (isSaved) {
            saveIcon.setAttribute('fill', 'currentColor');
            saveLabel.textContent = 'Saved';
            saveBtn.classList.remove('btn--outline-navy');
            saveBtn.classList.add('btn--green');
          } else {
            saveIcon.setAttribute('fill', 'none');
            saveLabel.textContent = 'Save for Later';
            saveBtn.classList.remove('btn--green');
            saveBtn.classList.add('btn--outline-navy');
          }
        }
        saveBtn.disabled = false;
      })
      .catch(function() { saveBtn.disabled = false; });
    });
  }
})();
</script>

<?php
// JSON-LD: Product Schema
$defaultSize = $product['sizes'][$defaultIdx ?? 0] ?? ($product['sizes'][0] ?? []);
$productPrice = $defaultSize['price'] ?? $product['starting_price'] ?? 0;
$productImage = $defaultSize['primary_image'] ?? $page_image ?? '';
$productAvailability = ($defaultSize['stock_status'] ?? 'Unknown') !== 'Out of Stock'
    ? 'https://schema.org/InStock'
    : 'https://schema.org/OutOfStock';

$productSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['short_desc'] ?? '',
    'image' => $productImage,
    'brand' => [
        '@type' => 'Brand',
        'name' => defined('COMPANY_NAME') ? COMPANY_NAME : 'Clarity Labs USA',
    ],
    'offers' => [
        '@type' => 'Offer',
        'url' => $page_url,
        'priceCurrency' => 'USD',
        'price' => number_format($productPrice, 2, '.', ''),
        'availability' => $productAvailability,
        'seller' => [
            '@type' => 'Organization',
            'name' => defined('COMPANY_NAME') ? COMPANY_NAME : 'Clarity Labs USA',
        ],
    ],
];

// Add purity if available
if (!empty($defaultSize['purity'])) {
    $productSchema['additionalProperty'] = [
        '@type' => 'PropertyValue',
        'name' => 'Purity',
        'value' => number_format($defaultSize['purity'], 2) . '%',
    ];
}

// JSON-LD: BreadcrumbList
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com',
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Shop',
            'item' => defined('SHOP_URL') ? SHOP_URL : 'https://shop.claritylabsusa.com',
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $product['name'],
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>

</body>
</html>
