<?php
/* ============================================================
   ClarityLabsUSA — CSRF Protection
   Generates and validates CSRF tokens for all POST forms.

   Usage:
     In form:   <?= csrf_field() ?>
     In handler: csrf_verify(); // dies with 403 if invalid
   ============================================================ */

/**
 * Generate or retrieve the current CSRF token
 */
function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('Session must be started before using CSRF tokens');
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Output a hidden form field with the CSRF token
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Get the CSRF token as a meta tag (for AJAX requests)
 */
function csrf_meta(): string {
    return '<meta name="csrf-token" content="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify the CSRF token from a POST request
 * Dies with 403 if invalid
 */
function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (empty($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die(json_encode([
            'success' => false,
            'error'   => 'Invalid or missing CSRF token. Please refresh the page and try again.',
        ]));
    }
}

/**
 * Regenerate the CSRF token (call after successful form submission)
 */
function csrf_regenerate(): void {
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
