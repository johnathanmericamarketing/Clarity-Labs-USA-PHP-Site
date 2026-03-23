<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title . ' — ClarityLabs USA' : 'ClarityLabs USA — Research-Grade Peptides'; ?></title>
<meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Research-grade peptides with transparent Certificates of Analysis and independent lab verification. Trusted since 2018.'; ?>">
<?php $r2Favicon = defined('R2_PUBLIC_URL') ? R2_PUBLIC_URL : 'https://pub-ff60dc038f7644d1afd85fa7910382f3.r2.dev'; ?>
<link rel="icon" type="image/x-icon" href="<?= $r2Favicon ?>/clarity-logo/favicon/favicon.ico">
<link rel="icon" type="image/svg+xml" href="<?= $r2Favicon ?>/clarity-logo/favicon/favicon.svg">
<link rel="apple-touch-icon" href="<?= $r2Favicon ?>/clarity-logo/favicon/apple-touch-icon.png">
<link rel="manifest" href="<?= $r2Favicon ?>/clarity-logo/favicon/site.webmanifest">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Serif+Display&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php if (function_exists('csrf_meta')) { echo csrf_meta(); } ?>
<link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>css/styles.css?v=4">
