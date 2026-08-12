<?php
/* ============================================================
   ClarityLabsUSA — Shop Support Ticket Form
   Public page (age-verified customers + locked-out users can submit)
   Self-submitting form to POST /api/v1/support/ticket
   ============================================================ */

$base_path = '../../';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/access-guard.php';
require_once __DIR__ . '/../../includes/api-client.php';

// One clarity_session_start() call at the top ensures csrf_field() and csrf_verify()
// share the same session cookie. This avoids 403 CSRF errors in self-submitting forms.
clarity_session_start();
age_gate_only();

$page_title = 'Support';
$page_description = 'Submit a support ticket. We\'re here to help with product questions, order issues, and more.';

// Get prefill values from query string + optional logged-in customer data
$customerData = is_logged_in() ? get_customer() : null;
$prefillName = htmlspecialchars(trim($_GET['name'] ?? '') ?: ($customerData['name'] ?? ''));
$prefillEmail = htmlspecialchars(trim($_GET['email'] ?? '') ?: ($customerData['email'] ?? ''));
$prefillSubject = htmlspecialchars(trim($_GET['subject'] ?? ''));
$prefillType = htmlspecialchars(trim($_GET['type'] ?? ''));

// Map type query param to subject dropdown. API only accepts: bug, feature, question, other
$typeToTopicMap = [
    'product_request' => 'feature',
    'bug'             => 'bug',
    'feature'         => 'feature',
    'question'        => 'question',
    'other'           => 'other',
];
$selectedTopic = $typeToTopicMap[$prefillType] ?? '';

// Topic dropdown options
$topicOptions = [
    ''                => 'Select a topic...',
    'question'        => 'General Question',
    'product_request' => 'Product Request',
    'bug'             => 'Report an Issue',
    'other'           => 'Other',
];

// Track form state and result
$formSubmitted = false;
$formSuccess = false;
$formError = '';
$ticketNumber = '';

// ──────────────────────────────────────────────────────────
// Handle POST (create ticket)
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot check
    if (!empty($_POST['website'])) {
        // Bot detected — silently accept to not reveal the trap
        $formSubmitted = true;
        $formSuccess = true;
        $ticketNumber = 'TKT-BOT-CHECK';
    } else {
        // CSRF verification
        try {
            csrf_verify();
        } catch (\Throwable $e) {
            $formSubmitted = true;
            $formError = 'Security verification failed. Please try again.';
        }

        if (!$formError) {
            // Get and sanitize fields
            $name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
            $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $topic   = trim(filter_input(INPUT_POST, 'topic', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

            // Validate required fields
            $errors = [];
            if (empty($name))    $errors[] = 'Name is required.';
            if (empty($email))   $errors[] = 'Email is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
            if (empty($subject)) $errors[] = 'Subject is required.';
            if (empty($message)) $errors[] = 'Message is required.';

            if (!empty($errors)) {
                $formSubmitted = true;
                $formError = implode(' ', $errors);
            } else {
                // Map topic dropdown to API type
                $typeToApiMap = [
                    'question'        => 'question',
                    'product_request' => 'feature',
                    'bug'             => 'bug',
                    'other'           => 'other',
                ];
                $apiType = $typeToApiMap[$topic] ?? 'question';

                // Submit ticket via API
                $api = new ClarityApiClient();
                try {
                    $result = $api->createTicket([
                        'name'        => $name,
                        'email'       => $email,
                        'subject'     => $subject,
                        'description' => $message,
                        'type'        => $apiType,
                        'priority'    => 'normal',
                        'url'         => SHOP_URL . '/support',
                    ]);

                    if (($result['status'] ?? '') === 'ok') {
                        $formSubmitted = true;
                        $formSuccess = true;
                        $ticketNumber = $result['ticket_number'] ?? '';
                    } else {
                        // API returned an error
                        error_log('Shop support API error: ' . json_encode($result));
                        $formSubmitted = true;
                        $formError = 'Unable to submit your ticket. Please try again later.';
                    }
                } catch (\Throwable $e) {
                    error_log('Shop support API exception: ' . $e->getMessage());
                    $formSubmitted = true;
                    $formError = 'Unable to submit your ticket. Please try again later.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../../includes/head.php'; ?>
  <style>
    .support-page { padding: 60px 0 100px; min-height: 60vh; }
    .support-page__inner { max-width: 700px; margin: 0 auto; }

    .support-page__header {
      text-align: center;
      margin-bottom: 48px;
    }

    .support-page__header h1 { font-size: 36px; margin-bottom: 12px; }
    .support-page__header p { color: var(--gray-400); font-size: 15px; max-width: 500px; margin: 0 auto; }

    /* Form Styles */
    .support-form { position: relative; }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--gray-200);
    }

    .form-input,
    .form-select,
    .form-textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--rule);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.04);
      color: var(--text);
      font-family: inherit;
      font-size: 14px;
      transition: all 0.15s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
      outline: none;
      border-color: var(--green);
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-textarea {
      resize: vertical;
      min-height: 140px;
      line-height: 1.5;
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
      color: var(--gray-500);
    }

    /* Honeypot (hidden) */
    .honeypot {
      position: absolute;
      top: -9999px;
      left: -9999px;
      opacity: 0;
      pointer-events: none;
    }

    /* Button */
    .btn-submit {
      width: 100%;
      padding: 12px 20px;
      background: var(--green);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-submit:hover {
      background: var(--green-dark);
      transform: translateY(-1px);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    /* Success Message */
    .form-success {
      padding: 16px 18px;
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid var(--green);
      border-radius: 8px;
      color: var(--green);
      font-size: 14px;
    }

    .form-success__title {
      font-weight: 600;
      margin-bottom: 6px;
    }

    .form-success__detail {
      font-size: 13px;
      color: var(--gray-300);
    }

    /* Error Message */
    .form-error {
      padding: 16px 18px;
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid #dc2626;
      border-radius: 8px;
      color: #fca5a5;
      font-size: 14px;
    }

    /* Hidden form after success */
    .support-form.success {
      display: none;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="breadcrumb">
  <div class="breadcrumb__inner">
    <a href="<?= SHOP_URL ?>">Shop</a>
    <span class="breadcrumb__sep">/</span>
    <span class="breadcrumb__current">Support</span>
  </div>
</div>

<section class="support-page">
  <div class="support-page__inner">
    <div class="support-page__header">
      <p class="section-label fade-up" style="color: var(--green-rule);">Get Help</p>
      <h1 class="fade-up">Support</h1>
      <p class="fade-up" style="color: var(--gray-400);">Experiencing an issue or need to ask a question? Submit a support ticket and we'll get back to you as soon as possible.</p>
    </div>

    <?php if ($formSubmitted && $formSuccess): ?>
      <!-- Success State -->
      <div class="form-success fade-up">
        <div class="form-success__title">✓ Ticket Submitted</div>
        <div class="form-success__detail">
          Thank you! Your support ticket has been created.
          <?php if ($ticketNumber && $ticketNumber !== 'TKT-BOT-CHECK'): ?>
            Your reference number is <strong><?= htmlspecialchars($ticketNumber) ?></strong>. We'll respond within 24 business hours.
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- Form -->
      <form id="support-form" method="post" class="support-form<?php if ($formSubmitted && !$formSuccess) echo ' error'; ?>">
        <?= csrf_field() ?>

        <!-- Honeypot -->
        <div class="honeypot">
          <label for="website">Website</label>
          <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
        </div>

        <?php if ($formSubmitted && $formError): ?>
          <div class="form-error fade-up" style="margin-bottom: 20px;">
            <?= htmlspecialchars($formError) ?>
          </div>
        <?php endif; ?>

        <div class="form-group fade-up">
          <label class="form-label" for="name">Full Name *</label>
          <input type="text" name="name" id="name" class="form-input" placeholder="Your name" value="<?= $prefillName ?>" required>
        </div>

        <div class="form-group fade-up stagger-1">
          <label class="form-label" for="email">Email Address *</label>
          <input type="email" name="email" id="email" class="form-input" placeholder="you@example.com" value="<?= $prefillEmail ?>" required>
        </div>

        <div class="form-group fade-up stagger-2">
          <label class="form-label" for="topic">Topic *</label>
          <select name="topic" id="topic" class="form-select" required>
            <?php foreach ($topicOptions as $value => $label): ?>
              <option value="<?= htmlspecialchars($value) ?>" <?php if ($value === $selectedTopic) echo 'selected'; ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group fade-up stagger-3">
          <label class="form-label" for="subject">Subject *</label>
          <input type="text" name="subject" id="subject" class="form-input" placeholder="Brief description of your issue" value="<?= $prefillSubject ?>" maxlength="255" required>
        </div>

        <div class="form-group fade-up stagger-4">
          <label class="form-label" for="message">Message *</label>
          <textarea name="message" id="message" class="form-textarea" placeholder="Please provide details about your issue or question." maxlength="5000" required></textarea>
        </div>

        <button type="submit" class="btn-submit fade-up stagger-5">Submit Ticket</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
