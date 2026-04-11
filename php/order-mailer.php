<?php
/* ============================================================
   DISABLED — Legacy order mailer removed 2026-04-11.

   This file used to accept form POSTs and send order emails
   without any authentication, database storage, or pricing
   validation. All orders must now go through the authenticated
   storefront checkout → ops API pipeline.

   If a customer hits this endpoint, return a JSON error
   directing them to the real checkout.
   ============================================================ */

header('Content-Type: application/json');

echo json_encode([
    'success' => false,
    'message' => 'This order form is no longer active. Please create an account and use our checkout at shop.claritylabsusa.com to place your order.',
]);
exit;
