<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title . ' — ClarityLabs USA' : 'ClarityLabs USA — Research-Grade Peptides'; ?></title>
<meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Research-grade peptides with transparent Certificates of Analysis and independent lab verification. Trusted since 2018.'; ?>">
<?php
    $r2Favicon = defined('R2_PUBLIC_URL') ? R2_PUBLIC_URL : 'https://pub-ff60dc038f7644d1afd85fa7910382f3.r2.dev';
    $og_title = isset($page_title) ? htmlspecialchars($page_title . ' — ClarityLabs USA') : 'ClarityLabs USA — Research-Grade Peptides';
    $og_desc  = isset($page_description) ? htmlspecialchars($page_description) : 'Research-grade peptides with transparent Certificates of Analysis and independent lab verification. Trusted since 2018.';
    $og_image = isset($page_image) ? htmlspecialchars($page_image) : $r2Favicon . '/clarity-logo/favicon/apple-touch-icon.png';
    $og_url   = isset($page_url) ? htmlspecialchars($page_url) : (defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com') . $_SERVER['REQUEST_URI'];
    $og_type  = isset($page_type) ? $page_type : 'website';
?>
<!-- Canonical -->
<link rel="canonical" href="<?= $og_url ?>">

<!-- Open Graph -->
<meta property="og:type" content="<?= $og_type ?>">
<meta property="og:title" content="<?= $og_title ?>">
<meta property="og:description" content="<?= $og_desc ?>">
<meta property="og:image" content="<?= $og_image ?>">
<meta property="og:url" content="<?= $og_url ?>">
<meta property="og:site_name" content="ClarityLabs USA">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= $og_title ?>">
<meta name="twitter:description" content="<?= $og_desc ?>">
<meta name="twitter:image" content="<?= $og_image ?>">

<!-- Favicons -->
<link rel="icon" type="image/x-icon" href="<?= $r2Favicon ?>/clarity-logo/favicon/favicon.ico">
<link rel="icon" type="image/svg+xml" href="<?= $r2Favicon ?>/clarity-logo/favicon/favicon.svg">
<link rel="apple-touch-icon" href="<?= $r2Favicon ?>/clarity-logo/favicon/apple-touch-icon.png">
<link rel="manifest" href="<?= $r2Favicon ?>/clarity-logo/favicon/site.webmanifest">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Serif+Display&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

<?php if (function_exists('csrf_meta')) { echo csrf_meta(); } ?>
<link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>css/styles.css?v=6">

<!-- JSON-LD: Organization (site-wide) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "<?= defined('COMPANY_NAME') ? COMPANY_NAME : 'Clarity Labs USA' ?>",
  "url": "<?= defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com' ?>",
  "logo": "<?= $r2Favicon ?>/clarity-logo/favicon/apple-touch-icon.png",
  "contactPoint": {
    "@type": "ContactPoint",
    "email": "<?= defined('COMPANY_EMAIL_SUPPORT') ? COMPANY_EMAIL_SUPPORT : 'support@claritylabsusa.com' ?>",
    "contactType": "customer service"
  },
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "<?= defined('COMPANY_ADDRESS') ? COMPANY_ADDRESS : '' ?>",
    "addressLocality": "<?= defined('COMPANY_CITY') ? COMPANY_CITY : '' ?>",
    "addressRegion": "<?= defined('COMPANY_STATE') ? COMPANY_STATE : '' ?>",
    "postalCode": "<?= defined('COMPANY_ZIP') ? COMPANY_ZIP : '' ?>",
    "addressCountry": "US"
  }
}
</script>

<?php
/* ── Analytics (only loads when IDs are configured in config.php) ── */

// Determine which GA4 property to use (shop vs main site)
$isShop = strpos($_SERVER['HTTP_HOST'] ?? '', 'shop.') === 0;
$ga4Id = $isShop
    ? (defined('GA4_SHOP_ID') ? GA4_SHOP_ID : '')
    : (defined('GA4_SITE_ID') ? GA4_SITE_ID : '');

// Google Analytics 4
if ($ga4Id !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga4Id ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= $ga4Id ?>');</script>
<?php endif;

// Microsoft Clarity
// Microsoft Clarity
if (defined('CLARITY_PROJECT_ID') && CLARITY_PROJECT_ID !== ''): ?>
<script type="text/javascript">(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script","<?= CLARITY_PROJECT_ID ?>");</script>
<?php endif;

// E-commerce event tracking (shop only)
if ($isShop && $ga4Id !== ''): ?>
<script src="<?= isset($base_path) ? $base_path : '/' ?>js/analytics-events.js?v=1"></script>
<?php endif; ?>
