<?php
/* ============================================================
   ClarityLabsUSA — Access Guard
   Include at the top of ALL shop pages.
   Enforces two gates:
     1. Age verification (21+ cookie)
     2. Sign-in required (customer auth session)

   Usage (at top of any shop page):
     require_once __DIR__ . '/../config/config.php';
     require_once __DIR__ . '/../includes/session.php';
     require_once __DIR__ . '/../includes/access-guard.php';
     access_guard();
   ============================================================ */

/**
 * Enforce both access gates. Redirects if either fails.
 * Call at the top of every shop page (after session start).
 */
function access_guard(): void {
    clarity_session_start();

    // Gate 1: Age verification
    if (!is_age_verified()) {
        $currentUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SHOP_URL . '/gate/age-verify?redirect=' . $currentUrl);
        exit;
    }

    // Gate 2: Must be logged in
    if (!is_logged_in()) {
        $currentUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SHOP_URL . '/gate/sign-in?redirect=' . $currentUrl);
        exit;
    }
}

/**
 * Enforce only age gate (for pages that don't require login, like the sign-in page itself)
 */
function age_gate_only(): void {
    clarity_session_start();

    if (!is_age_verified()) {
        $currentUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SHOP_URL . '/gate/age-verify?redirect=' . $currentUrl);
        exit;
    }
}
