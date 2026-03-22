<?php
/* ============================================================
   ClarityLabsUSA — API Client
   Server-side cURL wrapper for clarity-ops REST API

   Usage:
     require_once __DIR__ . '/../config/config.php';
     require_once __DIR__ . '/api-client.php';

     $api = new ClarityApiClient();
     $products = $api->getProducts(['category' => 'Recovery & Repair']);
   ============================================================ */

class ClarityApiClient {

    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private string $cacheDir;
    private int $cacheTtl = 300; // 5 minutes default

    public function __construct() {
        $this->baseUrl = OPS_API_URL;
        $this->apiKey  = CLARITY_API_KEY;
        $this->timeout = 10; // seconds
        $this->cacheDir = dirname(__DIR__) . '/cache';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /* ──────────────────────────────────────────
       Cache Methods
       ────────────────────────────────────────── */

    /**
     * Get data from cache if fresh, otherwise return null
     */
    private function cacheGet(string $key): ?array {
        $file = $this->cacheDir . '/' . md5($key) . '.json';
        if (!file_exists($file)) return null;

        $mtime = filemtime($file);
        if ((time() - $mtime) > $this->cacheTtl) {
            @unlink($file);
            return null;
        }

        $data = @file_get_contents($file);
        if ($data === false) return null;

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Save data to cache
     */
    private function cacheSet(string $key, array $data): void {
        $file = $this->cacheDir . '/' . md5($key) . '.json';
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Clear all cache or specific key
     */
    public function cacheClear(?string $key = null): void {
        if ($key) {
            $file = $this->cacheDir . '/' . md5($key) . '.json';
            @unlink($file);
        } else {
            $files = glob($this->cacheDir . '/*.json');
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Make a cached GET request — only caches successful responses
     */
    public function getCached(string $endpoint, array $params = [], int $ttl = 0): array {
        $cacheKey = 'api:' . $endpoint . ':' . json_encode($params);

        // Override TTL if specified
        $originalTtl = $this->cacheTtl;
        if ($ttl > 0) $this->cacheTtl = $ttl;

        $cached = $this->cacheGet($cacheKey);
        $this->cacheTtl = $originalTtl;

        if ($cached !== null) {
            $cached['_from_cache'] = true;
            return $cached;
        }

        // Cache miss — fetch fresh
        $result = $this->get($endpoint, $params);

        // Only cache successful responses
        if (!empty($result['success']) || ($result['_http_code'] ?? 0) >= 200 && ($result['_http_code'] ?? 0) < 300) {
            if ($ttl > 0) $this->cacheTtl = $ttl;
            $this->cacheSet($cacheKey, $result);
            $this->cacheTtl = $originalTtl;
        }

        return $result;
    }

    /* ──────────────────────────────────────────
       HTTP Methods
       ────────────────────────────────────────── */

    /**
     * Make a GET request
     */
    public function get(string $endpoint, array $params = [], ?string $bearerToken = null): array {
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request('GET', $url, null, $bearerToken);
    }

    /**
     * Make a POST request
     */
    public function post(string $endpoint, array $data = [], ?string $bearerToken = null): array {
        $url = $this->baseUrl . $endpoint;
        return $this->request('POST', $url, $data, $bearerToken);
    }

    /**
     * Make a PUT request
     */
    public function put(string $endpoint, array $data = [], ?string $bearerToken = null): array {
        $url = $this->baseUrl . $endpoint;
        return $this->request('PUT', $url, $data, $bearerToken);
    }

    /**
     * Core cURL request handler
     */
    private function request(string $method, string $url, ?array $data = null, ?string $bearerToken = null): array {
        $ch = curl_init();

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
        ];

        if ($bearerToken) {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        $curlErrno  = curl_errno($ch);
        curl_close($ch);

        // Connection error
        if ($curlErrno !== 0) {
            return [
                'success' => false,
                'error'   => 'Connection failed: ' . $curlError,
                'code'    => 0,
            ];
        }

        // Parse JSON
        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error'   => 'Invalid JSON response',
                'code'    => $httpCode,
                'raw'     => substr($response, 0, 500),
            ];
        }

        // Add HTTP status context
        $decoded['_http_code'] = $httpCode;
        $decoded['success'] = ($httpCode >= 200 && $httpCode < 300);

        return $decoded;
    }

    /* ──────────────────────────────────────────
       Product Endpoints
       ────────────────────────────────────────── */

    /**
     * Get paginated product list (cached 5 min)
     * @param array $filters [category, search, per_page, page]
     */
    public function getProducts(array $filters = []): array {
        return $this->getCached('/products', $filters, 300);
    }

    /**
     * Get single product by SKU (cached 5 min)
     */
    public function getProduct(string $sku): array {
        return $this->getCached('/products/' . urlencode($sku), [], 300);
    }

    /**
     * Check product stock availability
     */
    public function getProductAvailability(string $sku): array {
        return $this->get('/products/' . urlencode($sku) . '/availability');
    }

    /**
     * Get categories with product counts
     */
    public function getCategories(): array {
        return $this->getCached('/categories', [], 600); // 10 min cache
    }

    /* ──────────────────────────────────────────
       Customer Auth Endpoints
       ────────────────────────────────────────── */

    /**
     * Register a new customer
     */
    public function register(array $data): array {
        return $this->post('/auth/register', $data);
    }

    /**
     * Verify customer email
     */
    public function verifyEmail(array $data): array {
        return $this->post('/auth/verify', $data);
    }

    /**
     * Login customer
     */
    public function login(string $email, string $password): array {
        return $this->post('/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * Get current customer profile
     */
    public function getMe(string $bearerToken): array {
        return $this->get('/auth/me', [], $bearerToken);
    }

    /**
     * Logout customer
     */
    public function logout(string $bearerToken): array {
        return $this->post('/auth/logout', [], $bearerToken);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(string $email): array {
        return $this->post('/auth/forgot-password', ['email' => $email]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(array $data): array {
        return $this->post('/auth/reset-password', $data);
    }

    /**
     * Change password (forced on first login)
     */
    public function changePassword(array $data, string $bearerToken): array {
        return $this->post('/auth/change-password', $data, $bearerToken);
    }

    /* ──────────────────────────────────────────
       Order Endpoints
       ────────────────────────────────────────── */

    /**
     * Validate order items and pricing before checkout
     */
    public function validateOrder(array $items): array {
        return $this->post('/orders/validate', ['items' => $items]);
    }

    /**
     * Create a new order
     */
    public function createOrder(array $orderData, string $bearerToken): array {
        return $this->post('/orders', $orderData, $bearerToken);
    }

    /**
     * Get customer order history
     */
    public function getOrders(string $bearerToken, array $params = []): array {
        return $this->get('/orders', $params, $bearerToken);
    }

    /**
     * Get single order detail
     */
    public function getOrder(int $orderId, string $bearerToken): array {
        return $this->get('/orders/' . $orderId, [], $bearerToken);
    }

    /* ──────────────────────────────────────────
       Shipping & Tax Endpoints (to be added in ops)
       ────────────────────────────────────────── */

    /**
     * Get shipping rates for address + items
     */
    public function getShippingRates(array $data, string $bearerToken): array {
        return $this->post('/shipping/rates', $data, $bearerToken);
    }

    /**
     * Calculate tax for address + subtotal
     */
    public function calculateTax(array $data): array {
        return $this->post('/tax/calculate', $data);
    }

    /**
     * Verify/correct shipping address
     */
    public function verifyAddress(array $address): array {
        return $this->post('/address/verify', $address);
    }

    /* ──────────────────────────────────────────
       Support Endpoints
       ────────────────────────────────────────── */

    /**
     * Submit contact form
     */
    public function submitContact(array $data): array {
        return $this->post('/support/contact', $data);
    }

    /**
     * Create support ticket
     */
    public function createTicket(array $data): array {
        return $this->post('/support/ticket', $data);
    }

    /* ──────────────────────────────────────────
       Coupon Endpoints (to be added in ops)
       ────────────────────────────────────────── */

    /**
     * Validate a coupon code
     */
    public function validateCoupon(string $code, array $cartItems = []): array {
        return $this->post('/coupons/validate', [
            'code'  => $code,
            'items' => $cartItems,
        ]);
    }

    /* ──────────────────────────────────────────
       Profile Endpoints
       ────────────────────────────────────────── */

    /**
     * Update customer profile/address
     */
    public function updateProfile(array $data, string $bearerToken): array {
        return $this->put('/auth/profile', $data, $bearerToken);
    }

    /* ──────────────────────────────────────────
       Wishlist Endpoints
       ────────────────────────────────────────── */

    /**
     * Get customer wishlist
     */
    public function getWishlist(string $bearerToken): array {
        return $this->get('/wishlist', [], $bearerToken);
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(string $sku, string $bearerToken): array {
        return $this->post('/wishlist', ['sku' => $sku], $bearerToken);
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist(string $sku, string $bearerToken): array {
        return $this->request('DELETE', $this->baseUrl . '/wishlist/' . urlencode($sku), null, $bearerToken);
    }
}
