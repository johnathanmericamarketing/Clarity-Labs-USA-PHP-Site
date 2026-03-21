<?php
/* ============================================================
   ClarityLabsUSA — Product Detail Page (Shop Subdomain)
   Gated: requires age verification + login

   Strategy: Load local product data (rich marketing content) and
   merge with API data (pricing, stock, images). Then render using
   the same product-template.php as the main site.
   ============================================================ */

$base_path = '../';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/access-guard.php';
require_once __DIR__ . '/../includes/api-client.php';

access_guard();

// Get product by SKU from API
$sku = $_GET['sku'] ?? '';
if (empty($sku)) {
    header('Location: ' . SHOP_URL . '/');
    exit;
}

// Load local product data (marketing content)
require_once __DIR__ . '/../includes/product-data.php';

// Try to find the product in local data by matching SKU or name
$slug = '';
$product = null;

// First: try API
$api = new ClarityApiClient();
$apiResponse = $api->getProduct($sku);
$apiProduct = ($apiResponse['success'] ?? false) ? ($apiResponse['data'] ?? null) : null;

if ($apiProduct) {
    // Find matching local product by name or slug
    $apiName = strtolower($apiProduct['name'] ?? '');
    $apiCompound = strtolower($apiProduct['compound'] ?? '');

    foreach ($products as $localSlug => $localProduct) {
        $localName = strtolower($localProduct['name'] ?? '');
        if ($localName === $apiName || $localName === $apiCompound || $localSlug === $apiCompound) {
            $slug = $localSlug;
            $product = $localProduct;
            break;
        }
    }

    // If no local match, try slug-style matching
    if (!$product) {
        $slugFromApi = strtolower(str_replace([' ', '_'], '-', $apiProduct['name'] ?? ''));
        if (isset($products[$slugFromApi])) {
            $slug = $slugFromApi;
            $product = $products[$slugFromApi];
        }
    }

    // Merge API data into local product
    if ($product) {
        // Override pricing from API
        if (!empty($apiProduct['sale_price'])) {
            $product['starting_price'] = $apiProduct['sale_price'];
            // Update sizes pricing if single size
            if (!empty($product['sizes'])) {
                $product['sizes'][0]['price'] = $apiProduct['sale_price'];
            }
        }
        // Override images from API if available
        if (!empty($apiProduct['primary_image'])) {
            $product['api_primary_image'] = $apiProduct['primary_image'];
        }
        if (!empty($apiProduct['gallery_images'])) {
            $product['api_gallery_images'] = $apiProduct['gallery_images'];
        }
        if (!empty($apiProduct['coa_pdf'])) {
            $product['api_coa_pdf'] = $apiProduct['coa_pdf'];
        }
        // Stock status
        $product['stock_status'] = $apiProduct['stock_status'] ?? 'Unknown';
    } else {
        // No local product — build minimal product array from API data
        $slug = strtolower(str_replace([' ', '_'], '-', $apiProduct['name'] ?? 'product'));
        $product = [
            'name' => $apiProduct['name'] ?? 'Product',
            'category' => $apiProduct['category'] ?? '',
            'badge' => strtoupper($apiProduct['compound'] ?? '') . ' · ' . strtoupper($apiProduct['mg_specification'] ?? ''),
            'tagline' => strtoupper($apiProduct['compound'] ?? '') . ' RESEARCH COMPOUND',
            'short_desc' => $apiProduct['short_description'] ?? '',
            'research_profile' => $apiProduct['short_description'] ?? '',
            'starting_price' => $apiProduct['sale_price'] ?? 0,
            'store_url' => '#',
            'sizes' => [
                [
                    'mg' => $apiProduct['mg_specification'] ?? '',
                    'phase' => 'Standard Phase',
                    'price' => $apiProduct['sale_price'] ?? 0,
                    'popular' => true,
                    'card_desc' => $apiProduct['short_description'] ?? '',
                ],
            ],
            'why_cards' => [],
            'hero_checklist' => [
                'Research grade — third-party COA verified',
                'Structured recovery protocol compatible',
                'US-based fulfillment · Educational resources included',
            ],
            'hero_long_desc' => $apiProduct['long_description'] ?? $apiProduct['short_description'] ?? '',
            'research_apps' => [],
            'stock_status' => $apiProduct['stock_status'] ?? 'Unknown',
            'api_primary_image' => $apiProduct['primary_image'] ?? '',
            'api_gallery_images' => $apiProduct['gallery_images'] ?? [],
            'api_coa_pdf' => $apiProduct['coa_pdf'] ?? '',
        ];
    }
} else {
    // API failed — try local data only
    $slug = $sku; // assume SKU matches slug
    if (isset($products[$slug])) {
        $product = $products[$slug];
    } else {
        // Not found anywhere
        header('Location: ' . SHOP_URL . '/?error=product_not_found');
        exit;
    }
}

// Set page vars
$page_title = $product['name'];
$page_description = $product['short_desc'] ?? $product['short_description'] ?? '';
$current_page = 'shop';

// Build related products
$related = [];
if (!empty($products)) {
    $productCategory = $product['category'] ?? '';
    foreach ($products as $rslug => $rp) {
        if ($rslug === $slug) continue;
        if (!empty($rp['hidden'])) continue;
        if ($rp['category'] === $productCategory) {
            $related[$rslug] = $rp;
            if (count($related) >= 3) break;
        }
    }
    // Fill with other products if not enough from same category
    if (count($related) < 3) {
        foreach ($products as $rslug => $rp) {
            if ($rslug === $slug || isset($related[$rslug])) continue;
            if (!empty($rp['hidden'])) continue;
            $related[$rslug] = $rp;
            if (count($related) >= 3) break;
        }
    }
}

// Include the same product template as the main site
include __DIR__ . '/../products/product-template.php';
?>
