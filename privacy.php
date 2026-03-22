<?php
$current_page = 'privacy';
$page_title = 'Privacy Policy';
$page_description = 'Privacy Policy for ClarityLabs USA — how we collect, use, and protect your personal information.';
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="breadcrumb">
  <div class="breadcrumb__inner">
    <a href="index.php">ClarityLabsUSA</a>
    <span class="breadcrumb__sep">/</span>
    <span class="breadcrumb__current">Privacy Policy</span>
  </div>
</div>

<section class="legal">
  <div class="legal__inner">
    <div class="legal__header">
      <p class="section-label fade-up">Legal</p>
      <h1 class="fade-up stagger-1">Privacy Policy</h1>
      <hr class="teal-rule teal-rule--wide teal-rule--center fade-up stagger-2" style="margin:16px auto;">
      <p class="legal__updated fade-up stagger-2">Last Updated: March 22, 2026</p>
    </div>

    <p class="fade-up"><?= COMPANY_NAME ?> ("we," "us," or "our") respects your privacy and is committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our websites at <a href="<?= SITE_URL ?>"><?= SITE_URL ?></a> and <a href="<?= SHOP_URL ?>"><?= SHOP_URL ?></a> (collectively, the "Site") or place an order with us.</p>

    <h2>1. Information We Collect</h2>

    <h3>Information You Provide</h3>
    <p>When you create an account, place an order, or contact us, we may collect:</p>
    <ul>
      <li><strong>Account information:</strong> first name, last name, email address, date of birth (month and year), and password</li>
      <li><strong>Shipping information:</strong> shipping address, phone number</li>
      <li><strong>Order information:</strong> products purchased, order history, and transaction details</li>
      <li><strong>Communication data:</strong> messages you send through our contact form or support system</li>
    </ul>

    <h3>Information Collected Automatically</h3>
    <p>When you visit our Site, we may automatically collect:</p>
    <ul>
      <li><strong>Device information:</strong> browser type, operating system, and device identifiers</li>
      <li><strong>Usage data:</strong> pages visited, time spent on pages, and navigation patterns</li>
      <li><strong>IP address</strong> and approximate geographic location</li>
    </ul>

    <h2>2. Cookies &amp; Tracking Technologies</h2>
    <p>We use the following cookies on our Site:</p>
    <ul>
      <li><strong>Age verification cookie</strong> (<code>age_verified</code>): Stores your age confirmation for 30 days so you do not need to re-verify on each visit. This cookie is essential to access the shop.</li>
      <li><strong>Session cookie</strong> (<code>PHPSESSID</code>): Maintains your login state and shopping cart during your browsing session. This cookie expires when you close your browser or after a period of inactivity.</li>
    </ul>
    <p>These cookies are essential to the functionality of our Site. We do not use advertising or third-party tracking cookies unless otherwise disclosed.</p>

    <h2>3. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
      <li>Process and fulfill your orders</li>
      <li>Create and manage your account</li>
      <li>Verify your age and eligibility to purchase research compounds</li>
      <li>Communicate with you about your orders, account, and support requests</li>
      <li>Send transactional emails (order confirmations, shipping updates, password resets)</li>
      <li>Improve our Site, products, and customer experience</li>
      <li>Detect and prevent fraud or unauthorized activity</li>
      <li>Comply with legal obligations</li>
    </ul>

    <h2>4. How We Share Your Information</h2>
    <p>We do not sell, rent, or trade your personal information. We may share your information with the following third-party service providers who assist us in operating our business:</p>
    <ul>
      <li><strong>Payment processor:</strong> To securely process your payment transactions</li>
      <li><strong>Shipping carrier (EasyPost/USPS):</strong> To calculate shipping rates and deliver your orders</li>
      <li><strong>Email service (Zoho Mail):</strong> To send transactional and support emails</li>
      <li><strong>Cloud storage (Cloudflare R2):</strong> To host product images and certificates of analysis</li>
    </ul>
    <p>These service providers are contractually obligated to use your information only for the purposes of providing their services to us and to maintain appropriate security measures.</p>
    <p>We may also disclose your information if required by law, legal process, or government request, or to protect the rights, property, or safety of <?= COMPANY_NAME ?>, our users, or the public.</p>

    <h2>5. Data Security</h2>
    <p>We implement reasonable technical and organizational measures to protect your personal information, including:</p>
    <ul>
      <li>HTTPS encryption for all data transmitted between your browser and our servers</li>
      <li>Secure session management with HttpOnly and Secure cookie flags</li>
      <li>CSRF protection on all forms to prevent cross-site request forgery</li>
      <li>Server-side API communication — your sensitive data is never exposed to the browser</li>
      <li>Hashed passwords — we never store passwords in plain text</li>
    </ul>
    <p>While we strive to protect your information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security.</p>

    <h2>6. Data Retention</h2>
    <p>We retain your personal information for as long as your account is active or as needed to provide you services, fulfill orders, and comply with our legal obligations. If you wish to close your account, please contact us and we will delete or anonymize your personal data within a reasonable timeframe, except where we are required to retain it by law.</p>

    <h2>7. Your Rights</h2>
    <p>Depending on your location, you may have the following rights regarding your personal information:</p>
    <ul>
      <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
      <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
      <li><strong>Deletion:</strong> Request deletion of your personal information, subject to legal exceptions</li>
      <li><strong>Opt-out:</strong> Opt out of marketing communications at any time</li>
    </ul>
    <p>To exercise any of these rights, please contact us at <a href="mailto:<?= COMPANY_EMAIL_SUPPORT ?>"><?= COMPANY_EMAIL_SUPPORT ?></a>. We will respond to your request within 30 days.</p>

    <h2>8. Children's Privacy</h2>
    <p>Our Site is not intended for individuals under the age of 21. We do not knowingly collect personal information from anyone under 21. If we learn that we have collected information from a person under 21, we will delete that information promptly.</p>

    <h2>9. Third-Party Links</h2>
    <p>Our Site may contain links to third-party websites. We are not responsible for the privacy practices or content of those websites. We encourage you to review the privacy policies of any third-party sites you visit.</p>

    <h2>10. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated "Last Updated" date. Your continued use of the Site after changes are posted constitutes your acceptance of the updated policy.</p>

    <div class="legal__contact">
      <h3>Contact Us</h3>
      <p>If you have questions about this Privacy Policy or our data practices, please contact us:</p>
      <p><strong>Email:</strong> <a href="mailto:<?= COMPANY_EMAIL_SUPPORT ?>"><?= COMPANY_EMAIL_SUPPORT ?></a></p>
      <p><strong>Address:</strong> <?= COMPANY_ADDRESS ?>, <?= COMPANY_CITY ?>, <?= COMPANY_STATE ?> <?= COMPANY_ZIP ?></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
