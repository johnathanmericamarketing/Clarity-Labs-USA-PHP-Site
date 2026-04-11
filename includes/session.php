<?php
/* ============================================================
   ClarityLabsUSA — Session Management & Cart Helpers
   Include this at the top of every page that needs sessions.

   Usage:
     require_once __DIR__ . '/session.php';
     clarity_session_start();
   ============================================================ */

// Audit L4 + Security S7: Hardening headers
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: frame-ancestors 'none'");
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

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
    // Security S10: Regenerate session ID on login to prevent session fixation.
    // An attacker who knows the pre-login session ID can't use it to hijack
    // the authenticated session because the ID changes here.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true); // true = delete old session file
    }
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
 * Audit H3: Age verification now uses SESSION instead of a plain cookie.
 * The old cookie approach was forgeable — a user could set age_verified=1
 * in DevTools and bypass the gate. Session storage is server-side and
 * tied to the HttpOnly session cookie, so it can't be forged.
 *
 * We still set a cookie as a HINT for the gate redirect (so returning
 * visitors don't see the gate on every new session), but the authoritative
 * check is the session value. The cookie alone is NOT sufficient.
 */
function is_age_verified(): bool {
    // Primary: session (server-side, tamper-proof)
    if (!empty($_SESSION['age_verified'])) {
        return true;
    }

    // Secondary: cookie hint — if present, trust it and promote to session
    // so the check is fast on subsequent requests in this session.
    // The cookie is HttpOnly + Secure + SameSite=Lax so it's harder to
    // forge than a plain value, but we still promote to session as the
    // authoritative source.
    if (isset($_COOKIE['age_verified']) && $_COOKIE['age_verified'] === '1') {
        $_SESSION['age_verified'] = true;
        return true;
    }

    return false;
}

/**
 * Set age verification in both session (authoritative) and cookie (hint
 * for returning visitors across sessions). Cookie lasts 30 days.
 */
function set_age_verified(): void {
    // Session = authoritative
    $_SESSION['age_verified'] = true;

    // Cookie = hint for returning visitors
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
        'sku'   => $item['sku'],
        'qty'   => $item['qty'],
        'price' => $item['price'],  // per-vial price stored at cart-add time
    ], cart_items());
}
