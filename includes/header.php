<?php
$bp = isset($base_path) ? $base_path : '';

// Detect if we're on the shop subdomain
$isShopSite = (strpos($_SERVER['HTTP_HOST'] ?? '', 'shop.') === 0);
$shopUrl = defined('SHOP_URL') ? SHOP_URL : 'https://shop.claritylabsusa.com';
$siteUrl = defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com';

// Load product-helpers for grouping
$helpersFile = __DIR__ . '/product-helpers.php';
if (!file_exists($helpersFile)) $helpersFile = __DIR__ . '/../includes/product-helpers.php';
if (file_exists($helpersFile) && !function_exists('group_products_by_compound')) { require_once $helpersFile; }

// On shop subdomain: load products from API for the menu
// On main site: load from local product-data.php
if ($isShopSite && defined('CLARITY_API_KEY') && CLARITY_API_KEY !== '') {
    if (!isset($apiMenuProducts)) {
        require_once __DIR__ . '/api-client.php';
        $menuApi = new ClarityApiClient();
        $menuResponse = $menuApi->getProducts(['per_page' => 50]);
        $apiMenuProducts = $menuResponse['data'] ?? [];
    }
    // Group by compound to deduplicate menu items
    $groupedMenu = function_exists('group_products_by_compound') ? group_products_by_compound($apiMenuProducts) : $apiMenuProducts;
    $menu_groups = [];
    foreach ($groupedMenu as $mp) {
        $cat = $mp['category'] ?? 'Other';
        if (!isset($menu_groups[$cat])) $menu_groups[$cat] = [];
        $menu_groups[$cat][] = $mp;
    }
} else {
    // Main site — also load from API if config is available, otherwise static
    $apiConfigFile = __DIR__ . '/../config/config.php';
    if (file_exists($apiConfigFile) && !defined('CLARITY_API_KEY')) {
        require_once $apiConfigFile;
    }
    if (defined('CLARITY_API_KEY') && CLARITY_API_KEY !== '' && CLARITY_API_KEY !== 'your-api-key-here') {
        require_once __DIR__ . '/api-client.php';
        $menuApi = new ClarityApiClient();
        $menuResponse = $menuApi->getProducts(['per_page' => 50]);
        $apiMenuProducts = $menuResponse['data'] ?? [];
        // Group by compound to deduplicate menu items
        $groupedMenu = function_exists('group_products_by_compound') ? group_products_by_compound($apiMenuProducts) : $apiMenuProducts;
        $menu_groups = [];
        foreach ($groupedMenu as $mp) {
            $cat = $mp['category'] ?? 'Other';
            if (!isset($menu_groups[$cat])) $menu_groups[$cat] = [];
            $menu_groups[$cat][] = $mp;
        }
        $isShopSite = true; // Use shop URLs for product links
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
      <?php if ($isShopSite): ?>
      <!-- Shop site: mega menu with API products -->
      <div class="header__dropdown">
        <a href="<?= $shopUrl ?>/" class="header__link <?php echo (isset($current_page) && $current_page === 'shop') ? 'header__link--active' : ''; ?>">Shop <span class="header__chevron">&#9662;</span></a>
        <div class="mega-menu" id="mega-menu">
          <div class="mega-menu__inner">
            <?php foreach ($menu_groups as $cat => $group): ?>
            <div class="mega-menu__col">
              <h4 class="mega-menu__heading"><?php echo htmlspecialchars($cat); ?></h4>
              <?php foreach ($group as $mp):
                $mgTags = '';
                if (!empty($mp['sizes'])) {
                    $mgs = array_map(function($s) { return $s['mg']; }, $mp['sizes']);
                    $mgTags = implode(' / ', $mgs);
                } elseif (!empty($mp['mg_specification'])) {
                    $mgTags = $mp['mg_specification'];
                }
              ?>
              <a href="<?= $shopUrl ?>/product?sku=<?= urlencode($mp['sku'] ?? '') ?>" class="mega-menu__item">
                <span class="mega-menu__item-name"><?= htmlspecialchars($mp['name'] ?? '') ?></span>
                <?php if ($mgTags): ?>
                <span class="mega-menu__item-mg"><?= htmlspecialchars($mgTags) ?></span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <div class="mega-menu__col mega-menu__col--cta">
              <div class="mega-menu__promo">
                <p class="mega-menu__promo-label">Quality Assurance</p>
                <h4 class="mega-menu__promo-title">Every Batch Tested</h4>
                <p class="mega-menu__promo-text">Independent third-party COA on every compound, every lot.</p>
                <a href="<?= $shopUrl ?>/" class="btn btn--green btn--sm">Browse All &rarr;</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <!-- Main site: simple Shop link, no dropdown -->
      <a href="<?= $shopUrl ?>/" class="header__link <?php echo (isset($current_page) && $current_page === 'shop') ? 'header__link--active' : ''; ?>">Shop</a>
      <?php endif; ?>
      <a href="<?= $isShopSite ? $siteUrl . '/about' : $bp . 'about.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'about') ? 'header__link--active' : ''; ?>">About</a>
      <a href="<?= $isShopSite ? $siteUrl . '/faq' : $bp . 'faq.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'faq') ? 'header__link--active' : ''; ?>">FAQ</a>
      <a href="<?= $isShopSite ? $siteUrl . '/contact' : $bp . 'contact.php' ?>" class="header__link <?php echo (isset($current_page) && $current_page === 'contact') ? 'header__link--active' : ''; ?>">Contact</a>
    </nav>
    <div class="header__icons">
      <?php if ($isShopSite): ?>
      <!-- Account Icon -->
      <a href="<?= $shopUrl ?>/account/" class="header__icon" title="My Account">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <!-- Cart Icon with Badge -->
      <a href="<?= $shopUrl ?>/cart" class="header__icon header__icon--cart" title="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <?php
          $cartCount = 0;
          if (function_exists('cart_count')) { $cartCount = cart_count(); }
        ?>
        <span class="header__cart-badge cart-count" style="<?= $cartCount > 0 ? '' : 'display:none;' ?>"><?= $cartCount ?></span>
      </a>
      <?php else: ?>
      <!-- Main site: just Shop Now CTA -->
      <a href="<?= $shopUrl ?>/" class="header__cta">Shop Now</a>
      <?php endif; ?>
    </div>
    <button class="header__hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
