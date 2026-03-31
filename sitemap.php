<?php
/* ============================================================
   ClarityLabsUSA — Dynamic XML Sitemap
   Serves static pages + product pages from ops API.
   Cached for 1 hour to avoid hitting the API on every crawl.
   ============================================================ */

require_once __DIR__ . '/config/config.php';

$siteUrl = defined('SITE_URL') ? SITE_URL : 'https://claritylabsusa.com';
$cacheFile = __DIR__ . '/cache/sitemap_cache.xml';
$cacheTtl = 3600; // 1 hour

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    header('Content-Type: application/xml; charset=UTF-8');
    readfile($cacheFile);
    exit;
}

// Static pages with priorities
$staticPages = [
    ['loc' => '/',            'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/about.php',   'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/faq.php',     'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/contact.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/terms.php',   'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/privacy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/refund.php',     'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/fda-notice.php', 'priority' => '0.4', 'changefreq' => 'yearly'],
];

// Product pages from local product-data.php (always available, no API dependency)
$productPages = [];
$productDataFile = __DIR__ . '/includes/product-data.php';
if (file_exists($productDataFile)) {
    require_once $productDataFile;
    if (isset($products) && is_array($products)) {
        foreach ($products as $slug => $product) {
            $productPages[] = [
                'loc' => '/products/?product=' . urlencode($slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }
    }
}

// Build XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$today = date('Y-m-d');

foreach ($staticPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($siteUrl . $page['loc']) . "</loc>\n";
    $xml .= "    <lastmod>{$today}</lastmod>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

foreach ($productPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($siteUrl . $page['loc']) . "</loc>\n";
    $xml .= "    <lastmod>{$today}</lastmod>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

$xml .= '</urlset>';

// Cache the result
if (is_dir(__DIR__ . '/cache') || mkdir(__DIR__ . '/cache', 0755, true)) {
    file_put_contents($cacheFile, $xml);
}

header('Content-Type: application/xml; charset=UTF-8');
echo $xml;
