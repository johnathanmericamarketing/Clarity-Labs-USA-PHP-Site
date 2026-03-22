<?php
$current_page = 'terms';
$page_title = 'Terms of Service';
$page_description = 'Terms of Service for ClarityLabs USA — research compound purchasing terms, age requirements, and usage policies.';
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
    <span class="breadcrumb__current">Terms of Service</span>
  </div>
</div>

<section class="legal">
  <div class="legal__inner">
    <div class="legal__header">
      <p class="section-label fade-up">Legal</p>
      <h1 class="fade-up stagger-1">Terms of Service</h1>
      <hr class="teal-rule teal-rule--wide teal-rule--center fade-up stagger-2" style="margin:16px auto;">
      <p class="legal__updated fade-up stagger-2">Last Updated: March 22, 2026</p>
    </div>

    <div class="legal__callout fade-up">
      <p><?= COMPANY_DISCLAIMER ?></p>
    </div>

    <p class="fade-up">Welcome to <?= COMPANY_NAME ?>. By accessing or using our website at <a href="<?= SITE_URL ?>"><?= SITE_URL ?></a> or <a href="<?= SHOP_URL ?>"><?= SHOP_URL ?></a> (collectively, the "Site"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, do not use the Site or purchase any products.</p>

    <h2>1. Eligibility &amp; Age Requirement</h2>
    <p>You must be at least <strong>21 years of age</strong> to access the shop, create an account, or purchase any products from <?= COMPANY_NAME ?>. By using our Site, you represent and warrant that you meet this age requirement. We verify age through our mandatory age gate and account registration process.</p>

    <h2>2. Research Use Only</h2>
    <p>All products sold by <?= COMPANY_NAME ?> are intended <strong>exclusively for in vitro research and laboratory use</strong> by qualified professionals. Our products are:</p>
    <ul>
      <li>Not intended for human or veterinary consumption</li>
      <li>Not evaluated by the Food and Drug Administration (FDA)</li>
      <li>Not intended to diagnose, treat, cure, or prevent any disease or condition</li>
      <li>Sold in powder (lyophilized) form requiring reconstitution with a suitable diluent</li>
    </ul>
    <p>Research supplies such as syringes, bacteriostatic water, and other reconstitution materials are not included with any product. No dosing instructions are provided. We adhere to all local and state laws regarding Research Only Chemical sales.</p>
    <p>By purchasing from <?= COMPANY_NAME ?>, you acknowledge and agree that you are a qualified research professional and that all products will be used solely for lawful research purposes.</p>

    <h2>3. Account Registration</h2>
    <p>To browse or purchase products, you must create an account. During registration, you agree to:</p>
    <ul>
      <li>Provide accurate and complete information, including your legal name, email address, and date of birth</li>
      <li>Verify your email address through our verification process</li>
      <li>Maintain the security of your account credentials</li>
      <li>Notify us immediately of any unauthorized use of your account</li>
      <li>Accept responsibility for all activity that occurs under your account</li>
    </ul>
    <p>We reserve the right to suspend or terminate accounts that violate these Terms or that we reasonably believe are being used for purposes inconsistent with legitimate research.</p>

    <h2>4. Orders &amp; Pricing</h2>
    <p>All prices are listed in U.S. dollars and are subject to change without notice. We reserve the right to:</p>
    <ul>
      <li>Modify or discontinue any product at any time</li>
      <li>Limit the quantity of any product available for purchase</li>
      <li>Refuse or cancel any order at our sole discretion</li>
      <li>Correct pricing errors, even after an order has been placed</li>
    </ul>
    <p>An order is not confirmed until you receive an order confirmation email from us. We reserve the right to verify your identity and eligibility before processing any order.</p>

    <h2>5. Payment</h2>
    <p>Payment is due at the time of purchase. By providing payment information, you represent that you are authorized to use the payment method provided. All transactions are processed securely. We do not store your full payment credentials on our servers.</p>

    <h2>6. Shipping &amp; Delivery</h2>
    <p>We ship to addresses within the United States. Shipping times and costs vary based on your location and selected shipping method. Once an order has shipped, you will receive a confirmation email with tracking information.</p>
    <p>Risk of loss and title for items purchased pass to you upon delivery to the carrier. We are not responsible for delays caused by the shipping carrier, weather, or other circumstances beyond our control.</p>

    <h2>7. All Sales Are Final</h2>
    <p><strong>All sales are final.</strong> Due to the nature of our research compounds and strict handling requirements, we do not accept returns, exchanges, or issue refunds. Please review our <a href="<?= SITE_URL ?>/refund">Refund &amp; Return Policy</a> for complete details.</p>

    <h2>8. Intellectual Property</h2>
    <p>All content on the Site — including text, graphics, logos, images, product descriptions, and software — is the property of <?= COMPANY_NAME ?> or its content suppliers and is protected by United States and international intellectual property laws. You may not reproduce, distribute, modify, or create derivative works from any content on the Site without our prior written consent.</p>

    <h2>9. Prohibited Uses</h2>
    <p>You agree not to:</p>
    <ul>
      <li>Use the Site for any unlawful purpose or in violation of any applicable laws</li>
      <li>Purchase products for human or animal consumption</li>
      <li>Resell products without prior written authorization</li>
      <li>Interfere with or disrupt the Site's functionality or security</li>
      <li>Attempt to gain unauthorized access to any part of the Site</li>
      <li>Use automated systems (bots, scrapers) to access the Site without our express permission</li>
    </ul>

    <h2>10. Disclaimer of Warranties</h2>
    <p>The Site and all products are provided <strong>"as is"</strong> and <strong>"as available"</strong> without warranties of any kind, whether express or implied. We disclaim all warranties, including but not limited to implied warranties of merchantability, fitness for a particular purpose, and non-infringement.</p>
    <p>We do not warrant that the Site will be uninterrupted, error-free, or free of viruses or other harmful components.</p>

    <h2>11. Limitation of Liability</h2>
    <p>To the fullest extent permitted by applicable law, <?= COMPANY_NAME ?>, its owners, officers, employees, and agents shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the Site or products, even if we have been advised of the possibility of such damages.</p>
    <p>Our total liability for any claim arising from or related to these Terms or your use of the Site shall not exceed the amount you paid for the specific product giving rise to the claim.</p>

    <h2>12. Indemnification</h2>
    <p>You agree to indemnify, defend, and hold harmless <?= COMPANY_NAME ?> and its owners, officers, employees, and agents from and against any claims, liabilities, damages, losses, and expenses arising from your use of the Site, violation of these Terms, or misuse of any product purchased from us.</p>

    <h2>13. Governing Law</h2>
    <p>These Terms shall be governed by and construed in accordance with the laws of the State of <?= COMPANY_STATE ?>, without regard to its conflict of law provisions. Any legal action or proceeding arising from these Terms shall be brought exclusively in the state or federal courts located in <?= COMPANY_STATE ?>.</p>

    <h2>14. Changes to These Terms</h2>
    <p>We reserve the right to update or modify these Terms at any time. Changes will be posted on this page with an updated "Last Updated" date. Your continued use of the Site after any changes constitutes your acceptance of the revised Terms.</p>

    <h2>15. Severability</h2>
    <p>If any provision of these Terms is found to be unenforceable or invalid, that provision shall be limited or eliminated to the minimum extent necessary, and the remaining provisions shall remain in full force and effect.</p>

    <div class="legal__contact">
      <h3>Contact Us</h3>
      <p>If you have questions about these Terms of Service, please contact us:</p>
      <p><strong>Email:</strong> <a href="mailto:<?= COMPANY_EMAIL_SUPPORT ?>"><?= COMPANY_EMAIL_SUPPORT ?></a></p>
      <p><strong>Address:</strong> <?= COMPANY_ADDRESS ?>, <?= COMPANY_CITY ?>, <?= COMPANY_STATE ?> <?= COMPANY_ZIP ?></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
