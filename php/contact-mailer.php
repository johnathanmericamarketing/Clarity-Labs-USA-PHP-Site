<?php
/* ============================================================
   ClarityLabsUSA — Contact Form Mailer
   Configure the $to variable below with your email address.
   ============================================================ */

header('Content-Type: application/json');

// ── Configuration ──
$to = 'your@email.com';  // <-- CHANGE THIS to your email address
$subject_prefix = '[ClarityLabs USA] ';

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// ── Honeypot check ──
if (!empty($_POST['website'])) {
    // Bot detected
    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    exit;
}

// ── Rate limiting (simple session-based) ──
session_start();
$now = time();
$cooldown = 60; // seconds between submissions
if (isset($_SESSION['last_contact_submit']) && ($now - $_SESSION['last_contact_submit']) < $cooldown) {
    echo json_encode(['success' => false, 'message' => 'Please wait a moment before submitting again.']);
    exit;
}

// ── Get and sanitize fields ──
$name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
$phone   = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

// ── Validate required fields ──
$errors = [];
if (empty($name))    $errors[] = 'Name is required.';
if (empty($email))   $errors[] = 'Email is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (empty($subject)) $errors[] = 'Subject is required.';
if (empty($message)) $errors[] = 'Message is required.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Build email ──
$subject_map = [
    'general'   => 'General Inquiry',
    'products'  => 'Product Question',
    'orders'    => 'Order Support',
    'testing'   => 'Testing & COAs',
    'wholesale' => 'Wholesale / Bulk',
    'other'     => 'Other',
];
$subject_text = isset($subject_map[$subject]) ? $subject_map[$subject] : $subject;

$email_subject = $subject_prefix . $subject_text . ' from ' . $name;

$email_body  = "New contact form submission from ClarityLabsUSA.com\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "Name:    $name\n";
$email_body .= "Email:   $email\n";
if (!empty($phone)) {
    $email_body .= "Phone:   $phone\n";
}
$email_body .= "Subject: $subject_text\n\n";
$email_body .= "Message:\n$message\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$email_body .= "Sent from ClarityLabsUSA contact form\n";
$email_body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$email_body .= "Date: " . date('Y-m-d H:i:s') . "\n";

$headers  = "From: ClarityLabs USA <noreply@claritylabsusa.com>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ── Send ──
$sent = @mail($to, $email_subject, $email_body, $headers);

if ($sent) {
    $_SESSION['last_contact_submit'] = $now;
    echo json_encode(['success' => true, 'message' => 'Message sent successfully. We\'ll be in touch within 24 business hours.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to send message at this time. Please try again later or email us directly.']);
}
