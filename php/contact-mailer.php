<?php
/* ============================================================
   ClarityLabsUSA — Contact Form Handler
   Routes contact submissions through the Ops API as support
   tickets (POST /api/v1/support/ticket). This ensures:
   - Tickets are trackable in the admin panel
   - Email confirmations sent via Postmark (not PHP mail())
   - Customer gets a public ticket URL to track their inquiry
   ============================================================ */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/api-client.php';

header('Content-Type: application/json');

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// ── Honeypot check ──
if (!empty($_POST['website'])) {
    // Bot detected — silently accept to not reveal the trap
    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    exit;
}

// ── CSRF + Rate limiting ──
csrf_verify();
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

// ── Map subject codes to friendly names ──
$subject_map = [
    'general'   => 'General Inquiry',
    'products'  => 'Product Question',
    'orders'    => 'Order Support',
    'testing'   => 'Testing & COAs',
    'wholesale' => 'Wholesale / Bulk',
    'other'     => 'Other',
];
$subject_text = isset($subject_map[$subject]) ? $subject_map[$subject] : $subject;

// ── Build description with contact details ──
$description = $message;
if (!empty($phone)) {
    $description .= "\n\n---\nPhone: " . $phone;
}

// ── Submit as ticket via Ops API ──
$api = new ClarityApiClient();

try {
    $result = $api->createTicket([
        'name'        => $name,
        'email'       => $email,
        'subject'     => $subject_text . ' — ' . $name,
        'description' => $description,
        'type'        => 'question',
        'priority'    => 'normal',
        'url'         => 'https://claritylabsusa.com/contact',
    ]);

    if (($result['status'] ?? '') === 'ok') {
        $_SESSION['last_contact_submit'] = $now;
        $ticketNum = $result['ticket_number'] ?? '';
        echo json_encode([
            'success' => true,
            'message' => "Message received! We'll be in touch within 24 business hours." . ($ticketNum ? " Your reference number is {$ticketNum}." : ''),
        ]);
    } else {
        // API returned an error — fall through to generic error
        error_log('Contact form API error: ' . json_encode($result));
        echo json_encode([
            'success' => false,
            'message' => 'Unable to send message at this time. Please try again later or email us directly at ' . CONTACT_EMAIL,
        ]);
    }
} catch (\Throwable $e) {
    error_log('Contact form exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send message at this time. Please try again later or email us directly at ' . CONTACT_EMAIL,
    ]);
}
