<?php
$bp = isset($base_path) ? $base_path : '';

// Detect if we're on the shop subdomain
$isShopSite = (strpos($_SERVER['HTTP_HOST'] ?? '', 'shop.') === 0);
$shopUrl = defined('SHOP_URL') ? SHOP_URL : 'https://shop.claritylabsusa.com';
$siteUrl = defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com';

// On shop subdomain: load products from API for the menu
// On main site: load from local product-data.php
if ($isShopSite && defined('CLARITY_API_KEY') && CLARITY_API_KEY !== '') {
    if (!isset($apiMenuProducts)) {
        require_once __DIR__ . '/api-client.php';
        $menuApi = new ClarityApiClient();
        $menuResponse = $menuApi->getProducts(['per_page' => 50]);
        $apiMenuProducts = $menuResponse['data'] ?? [];
    }
    $menu_groups = [];
    foreach ($apiMenuProducts as $mp) {
        $cat = $mp['category'] ?? 'Other';
        if (!isset($menu_groups[$cat])) $menu_groups[$cat] = [];
        $menu_groups[$cat][] = $mp;
    }
} else {
    if (!isset($products)) { include $bp . 'includes/product-data.php'; }
    $menu_groups = [];
    foreach ($products as $pslug => $p) {
        if (!empty($p['hidden'])) continue;
        $cat = $p['category'];
        if (!isset($menu_groups[$cat])) $menu_groups[$cat] = [];
        $menu_groups[$cat][$pslug] = $p;
    }
}
?>
<header class="header" id="header">
  <div class="header__inner">
    <a href="<?= $isShopSite ? $siteUrl : $bp . 'index.php' ?>" class="header__logo">
      <img src="<?php echo $bp; ?>Logo/icon_no_background.webp" alt="ClarityLabs USA" class="header__logo-icon">
      <span class="header__logo-text">
        <span class="header__logo-name">Clarity<br>Labs <span class="header__logo-usa">USA</span></span>
        <span class="header__logo-tagline">Clarity &bull; Confidence &bull; Simplicity</span>
      </span>
    </a>
    <nav class="header__nav" id="nav">
      <a href="<?= $isShopSite ? $siteUrl : $bp . 'index.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'home') ? 'header__link--active' : ''; ?>">Home</a>
      <div class="header__dropdown">
        <a href="<?= $isShopSite ? $shopUrl . '/' : $bp . 'shop.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'shop') ? 'header__link--active' : ''; ?>">Shop <span class="header__chevron">&#9662;</span></a>
        <div class="mega-menu" id="mega-menu">
          <div class="mega-menu__inner">
            <?php foreach ($menu_groups as $cat => $group): ?>
            <div class="mega-menu__col">
              <h4 class="mega-menu__heading"><?php echo htmlspecialchars($cat); ?></h4>
              <?php foreach ($group as $mslug => $mp):
                if ($isShopSite) {
                    $menuItemName = htmlspecialchars($mp['name'] ?? '');
                    $menuItemSku = urlencode($mp['sku'] ?? '');
                    $menuItemUrl = $shopUrl . '/product?sku=' . $menuItemSku;
                } else {
                    $menuItemName = htmlspecialchars($mp['name'] ?? '');
                    $menuItemUrl = $bp . 'products/index.php?product=' . $mslug;
                }
              ?>
              <a href="<?= $menuItemUrl ?>" class="mega-menu__item">
                <span class="mega-menu__item-name"><?= $menuItemName ?></span>
              </a>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <div class="mega-menu__col mega-menu__col--cta">
              <div class="mega-menu__promo">
                <p class="mega-menu__promo-label">Quality Assurance</p>
                <h4 class="mega-menu__promo-title">Every Batch Tested</h4>
                <p class="mega-menu__promo-text">Independent third-party COA on every compound, every lot.</p>
                <a href="<?= $isShopSite ? $shopUrl . '/' : $bp . 'shop.php' ?>" class="btn btn--green btn--sm">Browse All &rarr;</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <a href="<?= $isShopSite ? $siteUrl . '/about' : $bp . 'about.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'about') ? 'header__link--active' : ''; ?>">About</a>
      <a href="<?= $isShopSite ? $siteUrl . '/faq' : $bp . 'faq.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'faq') ? 'header__link--active' : ''; ?>">FAQ</a>
      <a href="<?= $isShopSite ? $siteUrl . '/contact' : $bp . 'contact.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'contact') ? 'header__link--active' : ''; ?>">Contact</a>
    </nav>
    <a href="<?= $isShopSite ? $shopUrl . '/' : $bp . 'shop.php' ?>" class="header__cta">Shop Now</a>
    <button class="header__hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
