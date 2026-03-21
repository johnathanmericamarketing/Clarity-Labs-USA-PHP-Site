<?php
/* ============================================================
   ClarityLabsUSA — Session Management & Cart Helpers
   Include this at the top of every page that needs sessions.

   Usage:
     require_once __DIR__ . '/session.php';
     clarity_session_start();
   ============================================================ */

/**
 * Start a secure session (call once per request)
 */
function clarity_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 86400 * 30,  // 30 days
        'path'     => '/',
        'domain'   => '',          // auto-detect
        'secure'   => $isHttps,
        'httponly'  => true,
        'samesite' => 'Lax',      // Lax allows cross-subdomain redirects
    ]);

    session_name('clarity_session');
    session_start();

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

/* ──────────────────────────────────────────
   Auth Helpers
   ────────────────────────────────────────── */

/**
 * Check if customer is logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['customer_token']) && !empty($_SESSION['customer']);
}

/**
 * Get the customer's API bearer token
 */
function get_customer_token(): ?string {
    return $_SESSION['customer_token'] ?? null;
}

/**
 * Get customer data array
 */
function get_customer(): ?array {
    return $_SESSION['customer'] ?? null;
}

/**
 * Get customer's display name
 */
function get_customer_name(): string {
    $c = get_customer();
    if (!$c) return 'Guest';
    return trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?: 'Customer';
}

/**
 * Store customer data after login
 */
function set_customer(array $customerData, string $token): void {
    $_SESSION['customer'] = $customerData;
    $_SESSION['customer_token'] = $token;
    $_SESSION['logged_in_at'] = time();
}

/**
 * Clear customer session (logout)
 */
function clear_customer(): void {
    unset($_SESSION['customer'], $_SESSION['customer_token'], $_SESSION['logged_in_at']);
}

/* ──────────────────────────────────────────
   Age Verification
   ────────────────────────────────────────── */

/**
 * Check if visitor has confirmed age (cookie-based)
 */
function is_age_verified(): bool {
    return isset($_COOKIE['age_verified']) && $_COOKIE['age_verified'] === '1';
}

/**
 * Set age verification cookie (30 days)
 */
function set_age_verified(): void {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie('age_verified', '1', [
        'expires'  => time() + (86400 * 30),
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
}

/* ──────────────────────────────────────────
   Cart Helpers
   Cart structure: $_SESSION['cart'] = [
     [
       'sku'       => 'BPC157-10MG',
       'name'      => 'BPC-157',
       'size'      => '10 mg',
       'price'     => 64.99,
       'qty'       => 1,
       'image_url' => 'https://...',
     ],
     ...
   ]
   ────────────────────────────────────────── */

/**
 * Initialize cart if not exists
 */
function cart_init(): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart (or update qty if SKU already exists)
 */
function cart_add(string $sku, string $name, string $size, float $price, int $qty = 1, string $imageUrl = ''): void {
    cart_init();

    // Check if SKU already in cart
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['sku'] === $sku) {
            $item['qty'] += $qty;
            return;
        }
    }
    unset($item);

    // New item
    $_SESSION['cart'][] = [
        'sku'       => $sku,
        'name'      => $name,
        'size'      => $size,
        'price'     => $price,
        'qty'       => $qty,
        'image_url' => $imageUrl,
    ];
}

/**
 * Update quantity for a SKU
 */
function cart_update(string $sku, int $qty): void {
    cart_init();
    if ($qty <= 0) {
        cart_remove($sku);
        return;
    }
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['sku'] === $sku) {
            $item['qty'] = $qty;
            return;
        }
    }
}

/**
 * Remove item from cart by SKU
 */
function cart_remove(string $sku): void {
    cart_init();
    $_SESSION['cart'] = array_values(array_filter(
        $_SESSION['cart'],
        fn($item) => $item['sku'] !== $sku
    ));
}

/**
 * Clear entire cart
 */
function cart_clear(): void {
    $_SESSION['cart'] = [];
}

/**
 * Get all cart items
 */
function cart_items(): array {
    cart_init();
    return $_SESSION['cart'];
}

/**
 * Get total number of items in cart
 */
function cart_count(): int {
    cart_init();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'];
    }
    return $count;
}

/**
 * Get cart subtotal
 */
function cart_subtotal(): float {
    cart_init();
    $total = 0.0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return round($total, 2);
}

/**
 * Check if cart is empty
 */
function cart_is_empty(): bool {
    cart_init();
    return empty($_SESSION['cart']);
}

/**
 * Get cart items formatted for API order validation
 */
function cart_items_for_api(): array {
    return array_map(fn($item) => [
        'sku' => $item['sku'],
        'qty' => $item['qty'],
    ], cart_items());
}
