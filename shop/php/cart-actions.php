<?php
/* ============================================================
   ClarityLabsUSA — Cart Actions (AJAX Handler)
   Handles add/update/remove cart items
   ============================================================ */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

clarity_session_start();

$action = $_GET['action'] ?? '';

switch ($action) {

    /* ──────────────────────────────────────────
       ADD TO CART
       ────────────────────────────────────────── */
    case 'add':
        csrf_verify();

        $sku      = trim($_POST['sku'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $size     = trim($_POST['size'] ?? '');
        $price    = (float) ($_POST['price'] ?? 0);
        $qty      = max(1, (int) ($_POST['qty'] ?? 1));
        $imageUrl = trim($_POST['image_url'] ?? '');

        if (empty($sku) || empty($name) || $price <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product data.']);
            exit;
        }

        // Optional: validate stock via API before adding
        // $api = new ClarityApiClient();
        // $avail = $api->getProductAvailability($sku);
        // if (!$avail['success'] || ($avail['data']['stock_status'] ?? '') === 'Out of Stock') {
        //     echo json_encode(['success' => false, 'error' => 'This product is currently out of stock.']);
        //     exit;
        // }

        cart_add($sku, $name, $size, $price, $qty, $imageUrl);

        echo json_encode([
            'success'    => true,
            'message'    => $name . ' added to cart.',
            'cart_count' => cart_count(),
            'subtotal'   => cart_subtotal(),
        ]);
        break;

    /* ──────────────────────────────────────────
       UPDATE QUANTITY
       ────────────────────────────────────────── */
    case 'update':
        csrf_verify();

        $sku = trim($_POST['sku'] ?? '');
        $qty = (int) ($_POST['qty'] ?? 0);

        if (empty($sku)) {
            echo json_encode(['success' => false, 'error' => 'Missing SKU.']);
            exit;
        }

        if ($qty <= 0) {
            cart_remove($sku);
        } else {
            cart_update($sku, $qty);
        }

        echo json_encode([
            'success'    => true,
            'cart_count' => cart_count(),
            'subtotal'   => cart_subtotal(),
        ]);
        break;

    /* ──────────────────────────────────────────
       REMOVE ITEM
       ────────────────────────────────────────── */
    case 'remove':
        csrf_verify();

        $sku = trim($_POST['sku'] ?? '');

        if (empty($sku)) {
            echo json_encode(['success' => false, 'error' => 'Missing SKU.']);
            exit;
        }

        cart_remove($sku);

        echo json_encode([
            'success'    => true,
            'message'    => 'Item removed from cart.',
            'cart_count' => cart_count(),
            'subtotal'   => cart_subtotal(),
        ]);
        break;

    /* ──────────────────────────────────────────
       CLEAR CART
       ────────────────────────────────────────── */
    case 'clear':
        csrf_verify();
        cart_clear();

        echo json_encode([
            'success'    => true,
            'message'    => 'Cart cleared.',
            'cart_count' => 0,
            'subtotal'   => 0,
        ]);
        break;

    /* ──────────────────────────────────────────
       GET CART (for AJAX refresh)
       ────────────────────────────────────────── */
    case 'get':
        echo json_encode([
            'success'    => true,
            'items'      => cart_items(),
            'cart_count' => cart_count(),
            'subtotal'   => cart_subtotal(),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}
