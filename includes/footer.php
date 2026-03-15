<?php
$bp = isset($base_path) ? $base_path : '';
if (!isset($products)) { include (isset($base_path) ? $base_path : '') . 'includes/product-data.php'; }

// Pick top compounds for footer (first 6)
$footer_compounds = array_slice($products, 0, 6, true);
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
        <a href="<?php echo $bp; ?>index.php">Home</a>
        <a href="<?php echo $bp; ?>shop.php">Shop</a>
        <a href="<?php echo $bp; ?>about.php">About Us</a>
        <a href="<?php echo $bp; ?>faq.php">FAQ</a>
        <a href="<?php echo $bp; ?>contact.php">Contact</a>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Popular Compounds</h4>
        <?php foreach ($footer_compounds as $fslug => $fp): ?>
        <a href="<?php echo $bp; ?>products/index.php?product=<?php echo $fslug; ?>"><?php echo htmlspecialchars($fp['name']); ?></a>
        <?php endforeach; ?>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Quality</h4>
        <a href="<?php echo $bp; ?>index.php#testing">Lab Testing</a>
        <a href="<?php echo $bp; ?>shop.php">All Compounds</a>
        <a href="<?php echo $bp; ?>faq.php">Testing & COAs</a>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__disclaimer">All compounds are sold strictly for research and laboratory use only. Not for human consumption. By purchasing, you agree to our terms of use.</p>
      <p class="footer__copyright">&copy; <?php echo date('Y'); ?> ClarityLabs USA. All rights reserved.</p>
    </div>
  </div>
</footer>
<script src="<?php echo $bp; ?>js/main.js"></script>
