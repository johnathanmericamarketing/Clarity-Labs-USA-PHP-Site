<?php
/* ============================================================
   ClarityLabsUSA — Wishlist Actions (AJAX Handler)
   ============================================================ */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

$action = $_GET['action'] ?? '';
$sku = $_GET['sku'] ?? $_POST['sku'] ?? '';
$token = get_customer_token();
$api = new ClarityApiClient();

switch ($action) {
    case 'add':
        if (empty($sku)) {
            echo json_encode(['success' => false, 'error' => 'Missing SKU.']);
            exit;
        }
        $result = $api->addToWishlist($sku, $token);
        echo json_encode([
            'success' => ($result['status'] ?? '') === 'ok',
            'message' => $result['message'] ?? 'Saved.',
        ]);
        break;

    case 'remove':
        if (empty($sku)) {
            echo json_encode(['success' => false, 'error' => 'Missing SKU.']);
            exit;
        }
        $result = $api->removeFromWishlist($sku, $token);
        echo json_encode([
            'success' => ($result['status'] ?? '') === 'ok',
            'message' => $result['message'] ?? 'Removed.',
        ]);
        break;

    case 'check':
        // Check if a product is in the wishlist
        $wishlist = $api->getWishlist($token);
        $items = $wishlist['data'] ?? [];
        $saved = false;
        foreach ($items as $item) {
            if (($item['sku'] ?? '') === $sku) {
                $saved = true;
                break;
            }
        }
        echo json_encode(['success' => true, 'saved' => $saved]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}
