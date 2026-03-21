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

        // First validate items
        $validation = $api->validateOrder(cart_items_for_api());
        if (empty($validation['success']) || empty($validation['valid'])) {
            echo json_encode([
                'success' => false,
                'error'   => $validation['message'] ?? 'Some items in your cart are no longer available.',
            ]);
            exit;
        }

        // Build order data
        $orderData = [
            'items'                  => cart_items_for_api(),
            'shipping_name'          => $input['shipping_name'] ?? '',
            'shipping_address_line1' => $input['shipping_address_line1'] ?? '',
            'shipping_address_line2' => $input['shipping_address_line2'] ?? '',
            'shipping_city'          => $input['shipping_city'] ?? '',
            'shipping_state'         => $input['shipping_state'] ?? '',
            'shipping_zip'           => $input['shipping_zip'] ?? '',
            'shipping_country'       => $input['shipping_country'] ?? 'US',
            'shipping_phone'         => $input['shipping_phone'] ?? '',
            'payment_method'         => 'stripe',
            'payment_reference'      => $input['payment_intent_id'] ?? 'pending',
            'affiliate_code'         => $_SESSION['affiliate_code'] ?? null,
        ];

        $result = $api->createOrder($orderData, get_customer_token());

        if (!empty($result['success'])) {
            // Clear cart after successful order
            cart_clear();

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
