<?php
$bp = isset($base_path) ? $base_path : '';
$footerShopUrl = defined('SHOP_URL') ? SHOP_URL : 'https://shop.claritylabsusa.com';
$footerSiteUrl = defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com';
$footerIsShop = (strpos($_SERVER['HTTP_HOST'] ?? '', 'shop.') === 0);

// Load product-helpers for deduplication
$helpersFile = __DIR__ . '/product-helpers.php';
if (!file_exists($helpersFile)) $helpersFile = __DIR__ . '/../includes/product-helpers.php';
if (file_exists($helpersFile) && !function_exists('group_products_by_compound')) { require_once $helpersFile; }

// Load footer compounds from API if available
$footer_compounds = [];
$_footerRawProducts = null;

if (isset($apiMenuProducts) && !empty($apiMenuProducts)) {
    $_footerRawProducts = $apiMenuProducts;
} elseif (isset($homeApiProducts) && !empty($homeApiProducts)) {
    $_footerRawProducts = $homeApiProducts;
} else {
    // Try API
    $footerConfigFile = __DIR__ . '/../config/config.php';
    if (!file_exists($footerConfigFile)) $footerConfigFile = __DIR__ . '/config/config.php';
    if (!defined('CLARITY_API_KEY') && file_exists($footerConfigFile)) { require_once $footerConfigFile; }
    if (defined('CLARITY_API_KEY') && CLARITY_API_KEY !== '' && CLARITY_API_KEY !== 'your-api-key-here') {
        if (!class_exists('ClarityApiClient')) {
            $apiFile = __DIR__ . '/api-client.php';
            if (!file_exists($apiFile)) $apiFile = __DIR__ . '/../includes/api-client.php';
            if (file_exists($apiFile)) require_once $apiFile;
        }
        if (class_exists('ClarityApiClient')) {
            $footerApi = new ClarityApiClient();
            $footerResponse = $footerApi->getProducts(['per_page' => 50]);
            $_footerRawProducts = $footerResponse['data'] ?? [];
        }
    }
}

// Group by compound to deduplicate, then take first 6
if (!empty($_footerRawProducts) && function_exists('group_products_by_compound')) {
    $footer_compounds = array_slice(group_products_by_compound($_footerRawProducts), 0, 6);
} elseif (!empty($_footerRawProducts)) {
    $footer_compounds = array_slice($_footerRawProducts, 0, 6);
} else {
    // Final fallback: static data
    if (!isset($products)) { include $bp . 'includes/product-data.php'; }
    $footer_compounds = [];
    foreach (array_slice($products, 0, 6, true) as $fslug => $fp) {
        $footer_compounds[] = ['name' => $fp['name'], 'sku' => $fslug];
    }
}
?>
<footer class="footer">
  <div class="footer__inner">
    <div class="footer__top">
      <div class="footer__brand">
        <div class="footer__logo">
          <img src="<?php echo $bp; ?>Logo/icon_no_background.webp" alt="ClarityLabs USA" class="footer__logo-icon">
          <span class="footer__logo-text">
            <span class="footer__logo-name">Clarity<br>Labs <span class="footer__logo-usa">USA</span></span>
            <span class="footer__logo-tagline">Clarity &bull; Confidence &bull; Simplicity</span>
          </span>
        </div>
        <p class="footer__tagline">Research-grade peptides with transparent testing and independent lab verification. Trusted by the research community since 2018.</p>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Navigation</h4>
        <a href="<?= $footerSiteUrl ?>">Home</a>
        <a href="<?= $footerShopUrl ?>/">Shop</a>
        <a href="<?= $footerSiteUrl ?>/about">About Us</a>
        <a href="<?= $footerSiteUrl ?>/faq">FAQ</a>
        <a href="<?= $footerSiteUrl ?>/contact">Contact</a>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Popular Compounds</h4>
        <?php foreach ($footer_compounds as $fc): ?>
        <a href="<?= $footerShopUrl ?>/product?sku=<?= urlencode($fc['sku'] ?? '') ?>"><?= htmlspecialchars($fc['name'] ?? '') ?></a>
        <?php endforeach; ?>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Quality</h4>
        <a href="<?= $footerSiteUrl ?>/#testing">Lab Testing</a>
        <a href="<?= $footerShopUrl ?>/">All Compounds</a>
        <a href="<?= $footerSiteUrl ?>/faq">Testing & COAs</a>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__disclaimer"><?= defined('COMPANY_DISCLAIMER') ? COMPANY_DISCLAIMER : 'All compounds are sold strictly for research and laboratory use only. Not for human consumption. By purchasing, you agree to our terms of use.' ?></p>
      <p class="footer__copyright">&copy; <?php echo date('Y'); ?> ClarityLabs USA. All rights reserved.</p>
      <div class="footer__legal">
        <a href="<?= $footerSiteUrl ?>/terms">Terms of Service</a>
        <a href="<?= $footerSiteUrl ?>/privacy">Privacy Policy</a>
        <a href="<?= $footerSiteUrl ?>/refund">Refund Policy</a>
      </div>
    </div>
  </div>
</footer>
<script src="<?php echo $bp; ?>js/main.js"></script>
