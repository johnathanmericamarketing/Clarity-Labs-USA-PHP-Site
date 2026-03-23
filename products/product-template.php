<?php
/* ============================================================
   ClarityLabsUSA — Product Detail Template (Shopify-style)
   Renders any product from $product array
   ============================================================ */
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
          if (!$heroImg) {
              $heroDir = $base_path . 'images/products/' . $slug . '/images/';
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
    <div class="protocol-context__content">
      <?php foreach ($product['protocol_context'] as $i => $para): ?>
      <p class="fade-up stagger-<?php echo $i + 1; ?>"><?php echo htmlspecialchars($para); ?></p>
      <?php endforeach; ?>
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

      <form id="order-form" class="order-form">
        <input type="hidden" name="product" value="<?php echo htmlspecialchars($product['name']); ?>">
        <input type="hidden" name="product_slug" value="<?php echo htmlspecialchars($slug); ?>">
        <input type="hidden" name="size" id="order-size-input" value="<?php echo htmlspecialchars($product['sizes'][$defaultIdx]['mg']); ?>">
        <input type="hidden" name="price" id="order-price-input" value="<?php echo number_format($product['sizes'][$defaultIdx]['price'], 2); ?>">
        <input type="hidden" name="sku" id="order-sku-input" value="<?php echo htmlspecialchars($product['sizes'][$defaultIdx]['sku'] ?? ''); ?>">
        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

        <!-- Error Message -->
        <div id="order-form-error"></div>

        <!-- ══ STEP 1: Product ══ -->
        <div class="order-step active" data-step="1">
          <?php
            // Find checkout/product image for modal (check_ prefix first, then mockup, then 800px fallback)
            $modalImg = '';
            if (is_dir($heroDir)) {
              foreach (scandir($heroDir) as $f) {
                if (stripos($f, 'check_') === 0 && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $modalImg = $heroDir . $f; break; }
              }
              if (!$modalImg) {
                foreach (scandir($heroDir) as $f) {
                  if (stripos($f, 'mockup') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $modalImg = $heroDir . $f; break; }
                }
              }
              if (!$modalImg && $heroImg) $modalImg = $heroImg;
            }
          ?>
          <div class="order-product-hero">
            <?php if ($modalImg): ?>
            <div class="order-product-hero__img">
              <img src="<?php echo $modalImg; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <?php endif; ?>
            <div class="order-product-hero__info">
              <h3 class="order-product-hero__name"><?php echo htmlspecialchars($product['name']); ?></h3>
              <p class="order-product-hero__tagline"><?php echo htmlspecialchars($product['tagline']); ?></p>
              <div class="order-product-hero__badge">&#10003; Research Grade</div>
              <p class="order-product-hero__desc"><?php echo htmlspecialchars($product['short_desc']); ?></p>
              <div class="order-product-hero__trust">
                <span>&#10003; Third-Party Tested</span>
                <span>&#10003; COA Available</span>
                <span>&#10003; US Shipping</span>
              </div>
              <p class="order-product-hero__micro">For research and laboratory use only.</p>
            </div>
          </div>

          <hr class="order-form__divider">

          <div class="order-select-row">
            <div class="order-select-row__size">
              <label class="form-label">Select Size</label>
              <div class="order-size-options">
                <?php foreach ($product['sizes'] as $i => $size): ?>
                <label class="order-size-option <?php echo $i === $defaultIdx ? 'active' : ''; ?>">
                  <input type="radio" name="selected_size" value="<?php echo htmlspecialchars($size['mg']); ?>" data-price="<?php echo number_format($size['price'], 2); ?>" data-sku="<?php echo htmlspecialchars($size['sku'] ?? ''); ?>" <?php echo $i === $defaultIdx ? 'checked' : ''; ?>>
                  <span class="order-size-option__mg"><?php echo htmlspecialchars($size['mg']); ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="order-select-row__price">
              <label class="form-label">Price</label>
              <div class="order-price-display" id="order-price-display">$<?php echo number_format($product['sizes'][0]['price'], 2); ?></div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="order-qty">Quantity (Per Vials)</label>
            <input type="number" name="quantity" id="order-qty" class="form-input form-input--qty" min="1" max="50" value="1" required>
          </div>

          <div class="order-step__footer">
            <div></div>
            <button type="button" class="btn btn--navy order-step__next" data-next="2">Continue &rarr;</button>
          </div>
        </div>

        <!-- ══ STEP 2: Contact ══ -->
        <div class="order-step" data-step="2">
          <p class="order-step__heading">Contact Information</p>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="order-first-name">First Name *</label>
              <input type="text" name="first_name" id="order-first-name" class="form-input" placeholder="First name" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="order-last-name">Last Name *</label>
              <input type="text" name="last_name" id="order-last-name" class="form-input" placeholder="Last name" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="order-email">Email *</label>
            <input type="email" name="email" id="order-email" class="form-input" placeholder="you@email.com" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="order-phone">Phone *</label>
            <input type="tel" name="phone" id="order-phone" class="form-input" placeholder="(555) 555-5555" required>
          </div>

          <div class="order-step__footer">
            <button type="button" class="btn btn--outline-navy order-step__back" data-back="1">&larr; Back</button>
            <button type="button" class="btn btn--navy order-step__next" data-next="3">Continue &rarr;</button>
          </div>
        </div>

        <!-- ══ STEP 3: Shipping ══ -->
        <div class="order-step" data-step="3">
          <p class="order-step__heading">Shipping Address</p>

          <div class="form-group">
            <label class="form-label" for="order-street">Street Address *</label>
            <input type="text" name="street" id="order-street" class="form-input" placeholder="123 Main St" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="order-city">City *</label>
              <input type="text" name="city" id="order-city" class="form-input" placeholder="City" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="order-state">State *</label>
              <input type="text" name="state" id="order-state" class="form-input" placeholder="State" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="order-zip">ZIP *</label>
              <input type="text" name="zip" id="order-zip" class="form-input" placeholder="ZIP" required>
            </div>
          </div>

          <hr class="order-form__divider">

          <div class="form-checkbox">
            <input type="checkbox" name="age_verified" id="order-age" required>
            <label for="order-age">I confirm that I am 21 years of age or older.</label>
          </div>
          <div class="form-checkbox">
            <input type="checkbox" name="research_use" id="order-research" required>
            <label for="order-research">I acknowledge this product is for research and laboratory use only.</label>
          </div>

          <div class="order-step__footer">
            <button type="button" class="btn btn--outline-navy order-step__back" data-back="2">&larr; Back</button>
            <button type="submit" class="btn btn--navy order-form__submit">Submit Order</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>

<script>
(function() {
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
      formData.append('csrf_token', csrfToken);

      var cartUrl = '<?php echo defined("SHOP_URL") ? SHOP_URL : ""; ?>/php/cart-actions.php?action=add';

      fetch(cartUrl, { method: 'POST', body: formData, credentials: 'include' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success) {
            addBtn.textContent = 'Added!';
            // Update cart badge if exists
            var badge = document.querySelector('.cart-count');
            if (badge) { badge.textContent = data.cart_count; badge.style.display = 'inline-flex'; }
            setTimeout(function() { addBtn.textContent = 'Add to Cart'; addBtn.disabled = false; }, 1500);
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

</body>
</html>
