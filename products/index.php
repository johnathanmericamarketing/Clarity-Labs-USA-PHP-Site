<?php
/* ============================================================
   ClarityLabsUSA — Product Router
   Routes ?product=slug to the product template
   ============================================================ */

$base_path = '../';
include $base_path . 'includes/product-data.php';

// Get product slug from query string
$slug = isset($_GET['product']) ? trim($_GET['product']) : '';

// Validate
if (empty($slug) || !isset($products[$slug]) || !empty($products[$slug]['hidden'])) {
    // Redirect to shop if invalid product
    header('Location: ' . (defined('SHOP_URL') ? SHOP_URL : $base_path . 'shop/'));
    exit;
}

$product = $products[$slug];
$current_page = 'shop';
$page_title = $product['name'];
$page_description = $product['name'] . ' — ' . $product['short_desc'] . ' Research-grade, third-party tested by ClarityLabs USA.';

// Get related products (same category, excluding current)
$related = [];
foreach ($products as $rslug => $rp) {
    if ($rslug !== $slug && empty($rp['hidden']) && count($related) < 3) {
        $related[$rslug] = $rp;
    }
}

include 'product-template.php';
