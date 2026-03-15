<?php
$bp = isset($base_path) ? $base_path : '';
if (!isset($products)) { include (isset($base_path) ? $base_path : '') . 'includes/product-data.php'; }

// Group products by category (exclude hidden)
$menu_groups = [];
foreach ($products as $pslug => $p) {
  if (!empty($p['hidden'])) continue;
  $cat = $p['category'];
  if (!isset($menu_groups[$cat])) $menu_groups[$cat] = [];
  $menu_groups[$cat][$pslug] = $p;
}
?>
<header class="header" id="header">
  <div class="header__inner">
    <a href="<?php echo $bp; ?>index.php" class="header__logo">
      <img src="<?php echo $bp; ?>Logo/icon_no_background.webp" alt="ClarityLabs USA" class="header__logo-icon">
      <span class="header__logo-text">
        <span class="header__logo-name">Clarity<br>Labs <span class="header__logo-usa">USA</span></span>
        <span class="header__logo-tagline">Clarity &bull; Confidence &bull; Simplicity</span>
      </span>
    </a>
    <nav class="header__nav" id="nav">
      <a href="<?php echo $bp; ?>index.php" class="header__link <?php echo (isset($current_page) && $current_page === 'home') ? 'header__link--active' : ''; ?>">Home</a>
      <div class="header__dropdown">
        <a href="<?php echo $bp; ?>shop.php" class="header__link <?php echo (isset($current_page) && $current_page === 'shop') ? 'header__link--active' : ''; ?>">Shop <span class="header__chevron">&#9662;</span></a>
        <div class="mega-menu" id="mega-menu">
          <div class="mega-menu__inner">
            <?php foreach ($menu_groups as $cat => $group): ?>
            <div class="mega-menu__col">
              <h4 class="mega-menu__heading"><?php echo $cat; ?></h4>
              <?php foreach ($group as $mslug => $mp): ?>
              <a href="<?php echo $bp; ?>products/index.php?product=<?php echo $mslug; ?>" class="mega-menu__item">
                <span class="mega-menu__item-name"><?php echo htmlspecialchars($mp['name']); ?></span>
              </a>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <div class="mega-menu__col mega-menu__col--cta">
              <div class="mega-menu__promo">
                <p class="mega-menu__promo-label">Quality Assurance</p>
                <h4 class="mega-menu__promo-title">Every Batch Tested</h4>
                <p class="mega-menu__promo-text">Independent third-party COA on every compound, every lot.</p>
                <a href="<?php echo $bp; ?>shop.php" class="btn btn--green btn--sm">Browse All &rarr;</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <a href="<?php echo $bp; ?>about.php" class="header__link <?php echo (isset($current_page) && $current_page === 'about') ? 'header__link--active' : ''; ?>">About</a>
      <a href="<?php echo $bp; ?>faq.php" class="header__link <?php echo (isset($current_page) && $current_page === 'faq') ? 'header__link--active' : ''; ?>">FAQ</a>
      <a href="<?php echo $bp; ?>contact.php" class="header__link <?php echo (isset($current_page) && $current_page === 'contact') ? 'header__link--active' : ''; ?>">Contact</a>
    </nav>
    <a href="<?php echo $bp; ?>shop.php" class="header__cta">Shop Now</a>
    <button class="header__hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
