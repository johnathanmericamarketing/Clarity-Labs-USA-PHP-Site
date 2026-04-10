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
// If one exists, we MERGE the AI content into the same $product array
// that the existing products/product-template.php expects, so the hero
// (image, size selector, cart, trust badges) stays exactly as customers
// know it, while the sections below the hero (Why, Designed For, Protocol
// Context) get the richer AI-generated copy.
$aiPage = null;
try {
    $aiResponse = $api->getProductPage($sku);
    if (($aiResponse['status'] ?? '') === 'ok' && !empty($aiResponse['data'])) {
        $aiPage = $aiResponse['data'];
    }
} catch (\Throwable $e) {
    // No published page — silent fall-through to the legacy flow below
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

// PHASE 12c: If a published AI page exists, merge its content into the
// $product array AFTER the legacy lookup has populated everything else.
// The hero (image, size selector, cart, trust badges) keeps its existing
// shape — we only override the copy fields used by the sections below
// the hero (Why, Designed For, Protocol Context, Research Profile).
if ($aiPage !== null) {
    $hero      = $aiPage['hero']      ?? [];
    $whyCards  = $aiPage['why_cards'] ?? [];
    $audience  = $aiPage['audience']  ?? [];
    $research  = $aiPage['research_overview'] ?? '';
    $brandPhil = $aiPage['brand_philosophy']  ?? '';
    $seo       = $aiPage['seo']       ?? [];

    // ── SEO meta tags (head.php consumes $page_title/$page_description) ──
    if (!empty($seo['meta_title']))       $page_title       = $seo['meta_title'];
    if (!empty($seo['meta_description'])) $page_description = $seo['meta_description'];
    if (!empty($seo['og_image_url']))     $page_image       = $seo['og_image_url'];

    // ── Hero copy enhancements (does NOT touch image / sizes / cart) ──
    if (!empty($hero['title']))         $product['name']     = $hero['title'];
    if (!empty($hero['subtitle']))      $product['tagline']  = $hero['subtitle'];
    if (!empty($hero['intro']))         $product['short_desc'] = $hero['intro'];
    if (!empty($hero['category_label'])) $product['badge']    = $hero['category_label'];
    if (!empty($hero['trust_bullets']) && is_array($hero['trust_bullets'])) {
        $product['hero_checklist'] = $hero['trust_bullets'];
    }

    // ── "Why Researchers Choose This" section (4 cards) ──
    if (!empty($whyCards) && is_array($whyCards)) {
        $product['why_cards'] = array_map(function ($c) {
            return [
                'icon'  => '&#9678;',
                'title' => $c['title']       ?? '',
                'desc'  => $c['description'] ?? '',
            ];
        }, $whyCards);
    }

    // ── Research Profile (the SHORT intro that goes in the Why section
    //    header-right column on desktop). Use the hero subtitle since it's
    //    a punchy 1-line tagline. Falls back to short_description from DB.
    if (!empty($hero['subtitle'])) {
        $product['research_profile'] = $hero['subtitle'];
    } elseif (!empty($aiPage['short_description'])) {
        $product['research_profile'] = $aiPage['short_description'];
    }

    // ── "Designed For" section: numbered audience profiles + intro ──
    if (!empty($audience['profiles']) && is_array($audience['profiles'])) {
        $product['designed_for_profiles'] = array_map(function ($p) {
            return ['title' => $p, 'desc' => ''];
        }, $audience['profiles']);
    }
    if (!empty($audience['intro'])) {
        $product['designed_for_intro'] = $audience['intro'];
    }

    // ── Protocol Context (dark navy section): the LONG research overview
    //    split into paragraphs. Strip any {{CALLOUT: ...}} markers since
    //    the legacy template renders plain paragraphs.
    if (!empty($research)) {
        $cleaned = preg_replace('/\{\{CALLOUT:.+?\}\}/s', '', $research);
        $paras = array_values(array_filter(array_map('trim', preg_split('/\n\n+/', $cleaned))));
        if (!empty($paras)) {
            $product['protocol_context'] = $paras;
        }
    }

    // ── Brand philosophy gets the dedicated sidebar callout slot in the
    //    Protocol Context section instead of being appended to paragraphs.
    //    The new 2-column layout in product-template.php fills the
    //    previously-empty right column with this callout.
    if (!empty($brandPhil)) {
        $product['protocol_context_callout'] = $brandPhil;
    }
}

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
