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
require_once __DIR__ . '/../includes/product-helpers.php';

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

// PHASE 12c: Check for a PUBLISHED AI-generated page for this compound.
// If one exists, we render a completely different template (the AI page)
// and bypass the legacy product-data.php flow entirely.
$aiPage = null;
try {
    $aiResponse = $api->getProductPage($sku);
    if (($aiResponse['status'] ?? '') === 'ok' && !empty($aiResponse['data'])) {
        $aiPage = $aiResponse['data'];
    }
} catch (\Throwable $e) {
    // No published page, fall through to legacy renderer
}

if ($aiPage !== null) {
    // AI page exists — render the new template with SEO head tags injected.
    // Variables consumed by includes/head.php:
    //   $page_title       — used as <title> + og:title
    //   $page_description — used as meta description + og:description
    //   $page_image       — used as og:image (Phase 13a will populate this)
    //   $page_url         — used as canonical + og:url
    //   $page_type        — og:type (product for SEO)
    $seo = $aiPage['seo'] ?? [];

    $page_title       = $seo['meta_title'] ?? ($aiPage['compound'] . ' — Research Peptide');
    $page_description = $seo['meta_description'] ?? ($aiPage['short_description'] ?? '');
    $page_image       = $seo['og_image_url'] ?? null;
    $page_url         = SHOP_URL . '/product?sku=' . urlencode($sku);
    $page_type        = 'product';
    $base_path        = '../';
    $current_page     = 'shop';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <?php include __DIR__ . '/../includes/head.php'; ?>

    <?php /* JSON-LD Product schema for rich Google results */ ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": <?= json_encode($aiPage['compound']) ?>,
      "description": <?= json_encode($page_description) ?>,
      "category": <?= json_encode($aiPage['category'] ?? '') ?>,
      "brand": { "@type": "Brand", "name": "ClarityLabs USA" },
      "url": <?= json_encode($page_url) ?>,
      "offers": [
      <?php $first = true; foreach (($aiPage['variants'] ?? []) as $v): if (!$first) echo ','; $first = false; ?>
        {
          "@type": "Offer",
          "sku": <?= json_encode($v['sku']) ?>,
          "name": <?= json_encode(($aiPage['compound'] ?? '') . ' ' . ($v['mg'] ?? '')) ?>,
          "price": <?= json_encode((float) ($v['sale_price'] ?? 0)) ?>,
          "priceCurrency": "USD",
          "availability": <?= json_encode($v['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock') ?>,
          "url": <?= json_encode(SHOP_URL . '/product?sku=' . urlencode($v['sku'])) ?>
        }
      <?php endforeach; ?>
      ]
    }
    </script>
    </head>
    <body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <?php include __DIR__ . '/../includes/ai-product-template.php'; ?>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Restrict the local $products marketing catalog to items that are currently
// visible in the API. This is what makes Test Mode work on the product detail
// page: when Test Mode is ON the API only returns SUP-TEST products, so no
// local marketing entries match and the "Other Compounds to Explore" section
// at the bottom of this page hides entirely. In production it's a no-op
// because every local entry has a matching live API product.
try {
    $liveList = $api->getProducts(['per_page' => 100]);
    $liveNames = [];
    foreach (($liveList['data'] ?? []) as $lp) {
        if (!empty($lp['name']))     $liveNames[strtolower($lp['name'])] = true;
        if (!empty($lp['compound'])) $liveNames[strtolower($lp['compound'])] = true;
    }
    if (!empty($liveNames)) {
        $products = array_filter($products, function ($p) use ($liveNames) {
            return isset($liveNames[strtolower($p['name'] ?? '')])
                || isset($liveNames[strtolower($p['compound'] ?? '')]);
        });
    }
} catch (\Throwable $e) {
    // Fail open to full local catalog if the API is unreachable
}

$apiResponse = $api->getProduct($sku);
$apiProduct = ($apiResponse['success'] ?? false) ? ($apiResponse['data'] ?? null) : null;

if ($apiProduct) {
    // Find matching local product by name or compound slug
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
        $slugFromApi = strtolower(str_replace([' ', '_'], '-', $apiProduct['compound'] ?? $apiProduct['name'] ?? ''));
        if (isset($products[$slugFromApi])) {
            $slug = $slugFromApi;
            $product = $products[$slugFromApi];
        }
    }

    // ── Fetch ALL variants for this compound ──
    $compoundName = $apiProduct['compound'] ?? '';
    $allVariants = [];
    if (!empty($compoundName)) {
        $variantsResponse = $api->getProducts(['search' => $compoundName, 'per_page' => 50]);
        $variantsList = $variantsResponse['data'] ?? [];
        // Filter to exact compound match
        foreach ($variantsList as $v) {
            if (strtolower($v['compound'] ?? '') === strtolower($compoundName)) {
                $allVariants[] = $v;
            }
        }
    }
    // Fallback: if variant fetch failed, use the single product
    if (empty($allVariants)) {
        $allVariants = [$apiProduct];
    }

    // Sort variants by mg value (numeric)
    usort($allVariants, function ($a, $b) {
        return (float) ($a['mg_specification'] ?? 0) - (float) ($b['mg_specification'] ?? 0);
    });

    // Build sizes array from all variants, each with its own SKU
    $apiSizes = [];
    $defaultSizeIndex = 0;
    foreach ($allVariants as $vi => $variant) {
        $apiSizes[] = [
            'mg'           => $variant['mg_specification'] ?? '',
            'phase'        => 'Standard Phase',
            'price'        => (float) ($variant['price_per_vial'] ?? $variant['sale_price'] ?? 0),
            'purity'       => $variant['purity'] ?? null,
            'sku'          => $variant['sku'] ?? '',
            'stock_status' => $variant['stock_status'] ?? 'Unknown',
            'popular'      => count($allVariants) > 1 && $vi === 1, // middle size = popular
            'card_desc'    => $variant['short_description'] ?? '',
            'primary_image' => $variant['primary_image'] ?? '',
            'gallery_images' => $variant['gallery_images'] ?? [],
            'coa_preview'  => $variant['coa_preview'] ?? '',
            'coa_pdf'      => $variant['coa_pdf'] ?? '',
        ];
        // Default to the variant matching the requested SKU
        if (($variant['sku'] ?? '') === $sku) {
            $defaultSizeIndex = $vi;
        }
    }

    // Merge API data into local product
    if ($product) {
        // Override sizes with API variants (each has its own SKU & price)
        $product['sizes'] = $apiSizes;
        $product['starting_price'] = $apiSizes[$defaultSizeIndex]['price'] ?? $apiSizes[0]['price'] ?? 0;
        $product['default_size_index'] = $defaultSizeIndex;
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
        if (!empty($apiProduct['coa_preview'])) {
            $product['api_coa_preview'] = $apiProduct['coa_preview'];
        }
        // Stock status from requested variant
        $product['stock_status'] = $apiProduct['stock_status'] ?? 'Unknown';
        // Use compound name as display name
        if (!empty($compoundName)) {
            $product['name'] = $compoundName;
        }
    } else {
        // No local product — build minimal product array from API data
        $slug = strtolower(str_replace([' ', '_'], '-', $compoundName ?: ($apiProduct['name'] ?? 'product')));
        $product = [
            'name' => $compoundName ?: ($apiProduct['name'] ?? 'Product'),
            'category' => $apiProduct['category'] ?? '',
            'badge' => strtoupper($compoundName) . ' · RESEARCH COMPOUND',
            'tagline' => strtoupper($compoundName) . ' RESEARCH COMPOUND',
            'short_desc' => $apiProduct['short_description'] ?? '',
            'research_profile' => $apiProduct['short_description'] ?? '',
            'starting_price' => $apiSizes[$defaultSizeIndex]['price'] ?? 0,
            'default_size_index' => $defaultSizeIndex,
            'store_url' => '#',
            'sizes' => $apiSizes,
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
            'api_coa_preview' => $apiProduct['coa_preview'] ?? '',
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
