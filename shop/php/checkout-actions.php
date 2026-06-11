<?php
/* ============================================================
   ClarityLabsUSA — Checkout Actions (AJAX Handler)
   Handles shipping rates, tax calculation, order placement
   ============================================================ */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

// Ensure logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to checkout.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    /* ──────────────────────────────────────────
       GET SHIPPING RATES
       ────────────────────────────────────────── */
    case 'shipping-rates':
        csrf_verify();

        $address = [
            'name'    => $_POST['shipping_name'] ?? '',
            'street1' => $_POST['shipping_address'] ?? '',
            'street2' => $_POST['shipping_address2'] ?? '',
            'city'    => $_POST['shipping_city'] ?? '',
            'state'   => $_POST['shipping_state'] ?? '',
            'zip'     => $_POST['shipping_zip'] ?? '',
            'country' => 'US',
        ];

        $api = new ClarityApiClient();
        $result = $api->getShippingRates([
            'address' => $address,
            'items'   => cart_items_for_api(),
        ], get_customer_token());

        echo json_encode($result);
        break;

    /* ──────────────────────────────────────────
       VERIFY ADDRESS
       ────────────────────────────────────────── */
    case 'verify-address':
        csrf_verify();

        $api = new ClarityApiClient();
        $result = $api->verifyAddress([
            'street1' => $_POST['shipping_address'] ?? '',
            'street2' => $_POST['shipping_address2'] ?? '',
            'city'    => $_POST['shipping_city'] ?? '',
            'state'   => $_POST['shipping_state'] ?? '',
            'zip'     => $_POST['shipping_zip'] ?? '',
            'country' => 'US',
        ]);

        echo json_encode($result);
        break;

    /* ──────────────────────────────────────────
       CALCULATE TAX
       ────────────────────────────────────────── */
    case 'tax':
        csrf_verify();

        $api = new ClarityApiClient();
        $result = $api->calculateTax([
            'subtotal' => cart_subtotal(),
            'shipping' => (float) ($_POST['shipping_amount'] ?? 0),
            'state'    => $_POST['shipping_state'] ?? '',
            'zip'      => $_POST['shipping_zip'] ?? '',
        ]);

        echo json_encode($result);
        break;

    /* ──────────────────────────────────────────
       VALIDATE COUPON
       ────────────────────────────────────────── */
    case 'validate-coupon':
        csrf_verify();

        $code = trim($_POST['coupon_code'] ?? '');
        if (empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a promo code.']);
            exit;
        }

        $subtotal = cart_subtotal();
        $items = cart_items_for_api();
        $customer = get_customer();
        $customerId = $customer['id'] ?? null;

        $result = $api->validateCoupon($code, $subtotal, $items, $customerId);

        if (($result['valid'] ?? false)) {
            // Store in session for place-order to use
            $_SESSION['applied_coupon'] = [
                'code'            => $code,
                'discount_amount' => (float) ($result['discount_amount'] ?? 0),
                'discount_type'   => $result['discount_type'] ?? '',
                'free_shipping'   => (bool) ($result['free_shipping'] ?? false),
                'message'         => $result['message'] ?? '',
            ];

            echo json_encode([
                'success'         => true,
                'valid'           => true,
                'discount_amount' => (float) ($result['discount_amount'] ?? 0),
                'discount_type'   => $result['discount_type'] ?? '',
                'free_shipping'   => (bool) ($result['free_shipping'] ?? false),
                'message'         => $result['message'] ?? 'Promo code applied!',
                'code'            => $code,
            ]);
        } else {
            // Clear any previously applied coupon
            unset($_SESSION['applied_coupon']);

            echo json_encode([
                'success' => false,
                'valid'   => false,
                'error'   => $result['message'] ?? 'Invalid promo code.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       REMOVE COUPON
       ────────────────────────────────────────── */
    case 'remove-coupon':
        csrf_verify();
        unset($_SESSION['applied_coupon']);
        echo json_encode(['success' => true, 'message' => 'Promo code removed.']);
        break;

    /* ──────────────────────────────────────────
       PLACE ORDER
       ────────────────────────────────────────── */
    case 'place-order':
        // Accept JSON body
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        // CSRF from header or body
        $csrfToken = $input['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !hash_equals(csrf_token(), $csrfToken)) {
            echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh and try again.']);
            exit;
        }

        if (cart_is_empty()) {
            echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
            exit;
        }

        $api = new ClarityApiClient();
        $customerToken = get_customer_token();

        // Audit M14: Validate cart items against ops API immediately before
        // creating the order.
        $validation = $api->validateOrder(cart_items_for_api(), $customerToken);
        if (empty($validation['success']) || empty($validation['valid'])) {
            echo json_encode([
                'success' => false,
                'error'   => $validation['message'] ?? $validation['error'] ?? 'Some items in your cart are no longer available.',
            ]);
            exit;
        }

        // Build order data — map to API expected format
        $orderData = [
            'customer_email'   => $_SESSION['customer']['email'] ?? '',
            'items'            => cart_items_for_api(),
            'shipping_address' => [
                'name'    => $input['shipping_name'] ?? '',
                'line1'   => $input['shipping_address_line1'] ?? '',
                'line2'   => $input['shipping_address_line2'] ?? '',
                'city'    => $input['shipping_city'] ?? '',
                'state'   => $input['shipping_state'] ?? '',
                'zip'     => $input['shipping_zip'] ?? '',
                'country' => $input['shipping_country'] ?? 'US',
                'phone'   => $input['shipping_phone'] ?? '',
            ],
            'shipping_amount'  => (float) ($input['shipping_amount'] ?? 0),
            'shipping_rate_id' => $input['shipping_rate_id'] ?? null,
            'shipping_carrier' => $input['shipping_carrier'] ?? null,
            'shipping_service' => $input['shipping_service'] ?? null,
            'shipping_est_days' => $input['shipping_est_days'] ?? null,
            'payment_method'   => $input['payment_method'] ?? 'pending',
            'payment_reference' => $input['payment_reference'] ?? 'awaiting_invoice',
            'coupon_code'      => $_SESSION['applied_coupon']['code'] ?? null,
            'affiliate_code'   => $_SESSION['affiliate_code'] ?? null,
            'save_shipping_as_default' => !empty($input['save_shipping_as_default']),
        ];

        $result = $api->createOrder($orderData, get_customer_token());

        if (!empty($result['success'])) {
            // Clear cart + applied coupon after successful order
            cart_clear();
            unset($_SESSION['applied_coupon']);

            echo json_encode([
                'success'      => true,
                'order_number' => $result['order_number'] ?? $result['data']['order_number'] ?? '',
                'order_id'     => $result['order_id'] ?? $result['data']['id'] ?? '',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error'   => $result['message'] ?? $result['error'] ?? 'Failed to place order.',
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}
