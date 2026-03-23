<?php
/* ============================================================
   ClarityLabsUSA — Site Configuration
   Reads secrets from config/.env (gitignored)
   ============================================================ */

// Load .env file
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Get an environment variable with optional default
 */
function env(string $key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $_ENV[$key] ?? $default;
    }
    return $value;
}

/* ──────────────────────────────────────────
   Site Constants
   ────────────────────────────────────────── */

define('SITE_URL', 'https://claritylabsusa.com');
define('SHOP_URL', 'https://shop.claritylabsusa.com');
define('OPS_API_URL', env('CLARITY_API_URL', 'https://ops.claritylabsbio.com/api/v1'));
define('CLARITY_API_KEY', env('CLARITY_API_KEY', ''));
define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', ''));
define('CACHE_CLEAR_TOKEN', env('CACHE_CLEAR_TOKEN', ''));

/* ──────────────────────────────────────────
   Company Info (mirrors clarity-ops config/clarity.php)
   ────────────────────────────────────────── */

define('COMPANY_NAME', 'Clarity Labs USA');
define('COMPANY_ADDRESS', '5441 South Macadam Avenue #5835');
define('COMPANY_CITY', 'Portland');
define('COMPANY_STATE', 'Oregon');
define('COMPANY_ZIP', '97239');
define('COMPANY_COUNTRY', 'United States');
define('COMPANY_EMAIL_ORDERS', 'orders@claritylabsusa.com');
define('COMPANY_EMAIL_SUPPORT', 'support@claritylabsusa.com');
define('CONTACT_EMAIL', env('CONTACT_EMAIL', 'support@claritylabsusa.com'));
define('COMPANY_WEBSITE', 'https://claritylabsusa.com');

define('COMPANY_DISCLAIMER', 'Research Use Only. All products sold by ClarityLabsUSA are intended exclusively for in vitro research and laboratory use by qualified professionals. They are not for human or veterinary consumption, are not evaluated by the Food and Drug Administration, and are not intended to diagnose, treat, cure, or prevent any disease or condition. By completing a purchase, the buyer confirms they are 21 years of age or older and a qualified research professional acting within applicable laws and regulations.');

define('AGE_GATE_DISCLAIMER', 'All products are sold in powder (lyophilized) form and require reconstitution with a suitable diluent for research purposes only. Research supplies (e.g., syringes, bacteriostatic water) are not included. No dosing instructions are provided. We adhere to all local and state laws around Research Only Chemical sales. We are not a pharmacy, nor do we promote or provide any advice for human or animal consumption. Please review our terms and conditions carefully before making a purchase on our website.');

/* ──────────────────────────────────────────
   R2 / Media
   ────────────────────────────────────────── */

define('R2_PUBLIC_URL', 'https://pub-ff60dc038f7644d1afd85fa7910382f3.r2.dev');
