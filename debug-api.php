<?php
/* Temporary debug script — DELETE after testing */
header('Content-Type: application/json');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/api-client.php';
require_once __DIR__ . '/includes/product-helpers.php';

$api = new ClarityApiClient();
$response = $api->getProducts(['per_page' => 50]);
$products = $response['data'] ?? [];

$debug = [
    'total_raw_products' => count($products),
    'raw_products' => array_map(function($p) {
        return [
            'sku' => $p['sku'] ?? null,
            'name' => $p['name'] ?? null,
            'compound' => $p['compound'] ?? null,
            'mg_specification' => $p['mg_specification'] ?? null,
            'price_per_vial' => $p['price_per_vial'] ?? null,
            'sale_price' => $p['sale_price'] ?? null,
        ];
    }, $products),
    'grouped' => array_map(function($g) {
        return [
            'name' => $g['name'],
            'sku' => $g['sku'],
            'variant_count' => $g['variant_count'],
            'sizes' => $g['sizes'],
            'min_price' => $g['min_price'],
        ];
    }, group_products_by_compound($products)),
    'total_grouped' => count(group_products_by_compound($products)),
];

echo json_encode($debug, JSON_PRETTY_PRINT);
