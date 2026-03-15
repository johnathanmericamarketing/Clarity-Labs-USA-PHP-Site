<?php
$current_page = 'shop';
$page_title = 'Shop Research Compounds';
$page_description = 'Browse ClarityLabs USA research-grade peptides. All compounds third-party tested with Certificates of Analysis. US-based fulfillment.';
include 'includes/product-data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="breadcrumb">
  <div class="breadcrumb__inner">
    <a href="index.php">ClarityLabsUSA</a>
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
    <?php
    $categories = ['all' => 'All'];
    foreach ($products as $p) {
      if (!empty($p['hidden'])) continue;
      $cat = $p['category'];
      $key = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($cat)));
      if (!isset($categories[$key])) $categories[$key] = $cat;
    }
    foreach ($categories as $key => $label): ?>
    <button class="filter-pill <?php echo $key === 'all' ? 'active' : ''; ?>" data-category="<?php echo $key; ?>"><?php echo $label; ?></button>
    <?php endforeach; ?>
  </div>
</div>

<!-- Product Grid -->
<section class="shop-grid">
  <div class="shop-grid__inner">
    <?php foreach ($products as $slug => $p):
      if (!empty($p['hidden'])) continue;
      $catKey = strtolower(str_replace(['&', ' '], ['-', '-'], strip_tags($p['category'])));
      $imgDir = 'images/products/' . $slug . '/images/';
      $imgFile = '';
      $imgMobile = '';
      if (is_dir($imgDir)) {
        $allImgFiles = scandir($imgDir);
        // Desktop: look for 220px image
        foreach ($allImgFiles as $f) {
          if (stripos($f, 'mobile') !== false) continue;
          if (stripos($f, '220') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $imgFile = $imgDir . $f; break; }
        }
        // Mobile: look for mobile image
        foreach ($allImgFiles as $f) {
          if (stripos($f, 'mobile') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $imgMobile = $imgDir . $f; break; }
        }
        // Fallback: first non-COA image
        if (!$imgFile) {
          foreach ($allImgFiles as $f) {
            if ($f === '.' || $f === '..') continue;
            if (stripos($f, 'COA') !== false || stripos($f, 'mobile') !== false) continue;
            if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) { $imgFile = $imgDir . $f; break; }
          }
        }
      }
    ?>
    <div class="shop-card fade-up" data-category="<?php echo $catKey; ?>">
      <div class="shop-card__img">
        <?php if ($imgFile): ?>
          <?php if ($imgMobile): ?>
          <picture>
            <source media="(max-width: 768px)" srcset="<?php echo $imgMobile; ?>">
            <img src="<?php echo $imgFile; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
          </picture>
          <?php else: ?>
          <img src="<?php echo $imgFile; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <?php endif; ?>
        <?php else: ?>
          <span class="shop-card__img-placeholder"><?php echo htmlspecialchars($p['name']); ?></span>
        <?php endif; ?>
      </div>
      <div class="shop-card__body">
        <span class="shop-card__cat"><?php echo $p['category']; ?></span>
        <h3 class="shop-card__name"><?php echo htmlspecialchars($p['name']); ?></h3>
        <p class="shop-card__desc"><?php echo htmlspecialchars($p['short_desc']); ?></p>
      </div>
      <div class="shop-card__footer">
        <span class="shop-card__price">From $<?php echo number_format($p['starting_price'], 2); ?></span>
        <a href="products/index.php?product=<?php echo $slug; ?>" class="shop-card__btn">View Details &rarr;</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Bottom CTA -->
<section class="section section--white section-border">
  <div class="section-inner" style="text-align:center;">
    <p class="section-label">Quality Assurance</p>
    <h2 class="fade-up">Every Compound. Every Batch. Tested.</h2>
    <hr class="teal-rule teal-rule--wide teal-rule--center" style="margin:20px auto;">
    <p class="fade-up" style="max-width:500px;margin:0 auto 24px;">All ClarityLabs compounds undergo independent third-party testing before distribution. COAs available on request.</p>
    <a href="index.php#testing" class="btn btn--green fade-up">View Testing Standards</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
