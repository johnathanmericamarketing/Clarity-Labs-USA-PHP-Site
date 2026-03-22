<?php
/* ============================================================
   ClarityLabsUSA — Product Helper Functions
   Groups flat API product rows by compound for display
   ============================================================ */

/**
 * Group flat API products by compound name.
 *
 * The API returns one row per SKU (e.g., RT10, RT20, RT30 for Retatrutide).
 * This function groups them into one entry per compound with a sizes array.
 *
 * @param array $products  Flat array of API product objects
 * @return array  Grouped array, one entry per compound
 */
function group_products_by_compound(array $products): array {
    $grouped = [];

    foreach ($products as $p) {
        $compound = $p['compound'] ?? $p['name'] ?? '';
        if (empty($compound)) continue;

        $key = strtolower(trim($compound));

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'name'              => $compound,
                'compound'          => $compound,
                'sku'               => $p['sku'] ?? '',
                'category'          => $p['category'] ?? '',
                'short_description' => $p['short_description'] ?? '',
                'primary_image'     => $p['primary_image'] ?? '',
                'stock_status'      => $p['stock_status'] ?? 'Unknown',
                'min_price'         => (float) ($p['price_per_vial'] ?? $p['sale_price'] ?? 0),
                'sizes'             => [],
            ];
        }

        // Add this variant to sizes
        $price = (float) ($p['price_per_vial'] ?? $p['sale_price'] ?? 0);
        $grouped[$key]['sizes'][] = [
            'mg'           => $p['mg_specification'] ?? '',
            'price'        => $price,
            'sku'          => $p['sku'] ?? '',
            'stock_status' => $p['stock_status'] ?? 'Unknown',
        ];

        // Track lowest price across variants
        if ($price > 0 && $price < $grouped[$key]['min_price']) {
            $grouped[$key]['min_price'] = $price;
        }

        // Use best image available
        if (empty($grouped[$key]['primary_image']) && !empty($p['primary_image'])) {
            $grouped[$key]['primary_image'] = $p['primary_image'];
        }

        // Best stock status wins (In Stock > Moderate > Low Stock > Out of Stock)
        $grouped[$key]['stock_status'] = best_stock_status(
            $grouped[$key]['stock_status'],
            $p['stock_status'] ?? 'Unknown'
        );
    }

    // Sort sizes within each compound by mg value (numeric)
    foreach ($grouped as &$g) {
        usort($g['sizes'], function ($a, $b) {
            return (float) $a['mg'] - (float) $b['mg'];
        });
        // Default SKU to first (smallest) size
        if (!empty($g['sizes'])) {
            $g['sku'] = $g['sizes'][0]['sku'];
        }
        $g['variant_count'] = count($g['sizes']);
    }
    unset($g);

    return array_values($grouped);
}

/**
 * Return the "better" of two stock statuses.
 * Priority: In Stock > Moderate > Low Stock > Out of Stock > Unknown
 */
function best_stock_status(string $a, string $b): string {
    $priority = [
        'In Stock'     => 4,
        'Moderate'     => 3,
        'Low Stock'    => 2,
        'Out of Stock' => 1,
        'Unknown'      => 0,
    ];
    $pa = $priority[$a] ?? 0;
    $pb = $priority[$b] ?? 0;
    return $pa >= $pb ? $a : $b;
}
