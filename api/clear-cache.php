<?php
/* ============================================================
   ClarityLabsUSA — Clear Cache Endpoint
   POST /api/clear-cache.php

   Accepts POST with X-Cache-Token header, clears all cached
   API responses, returns JSON with count of files removed.
   ============================================================ */

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => 'Method not allowed. Use POST.',
    ]);
    exit;
}

// Load config (defines CACHE_CLEAR_TOKEN)
require_once __DIR__ . '/../config/config.php';

// Verify token
$token = $_SERVER['HTTP_X_CACHE_TOKEN'] ?? '';

if (!defined('CACHE_CLEAR_TOKEN') || CACHE_CLEAR_TOKEN === '' || $token === '' || !hash_equals(CACHE_CLEAR_TOKEN, $token)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error'   => 'Unauthorized. Invalid or missing cache token.',
    ]);
    exit;
}

// Clear cache files
$cacheDir = __DIR__ . '/../cache';
$cleared  = 0;
$errors   = [];

if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.json');
    foreach ($files as $file) {
        if (@unlink($file)) {
            $cleared++;
        } else {
            $errors[] = basename($file);
        }
    }
}

http_response_code(200);
echo json_encode([
    'success'       => true,
    'files_cleared' => $cleared,
    'errors'        => $errors,
    'timestamp'     => date('c'),
]);
