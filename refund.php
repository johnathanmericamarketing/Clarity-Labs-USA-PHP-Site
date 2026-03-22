<?php
$current_page = 'refund';
$page_title = 'Refund & Return Policy';
$page_description = 'Refund and return policy for ClarityLabs USA — learn about our policy on returns, damaged shipments, and refund eligibility.';
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
    <span class="breadcrumb__current">Refund &amp; Return Policy</span>
  </div>
</div>

<section class="legal">
  <div class="legal__inner">
    <div class="legal__header">
      <p class="section-label fade-up">Legal</p>
      <h1 class="fade-up stagger-1">Refund &amp; Return Policy</h1>
      <hr class="teal-rule teal-rule--wide teal-rule--center fade-up stagger-2" style="margin:16px auto;">
      <p class="legal__updated fade-up stagger-2">Last Updated: March 22, 2026</p>
    </div>

    <div class="legal__callout fade-up">
      <p>All sales are final. By completing a purchase, you acknowledge and agree to this policy.</p>
    </div>

    <h2>1. All Sales Are Final</h2>
    <p><?= COMPANY_NAME ?> sells research-grade compounds that require strict handling, storage, and chain-of-custody protocols. Because we cannot verify product integrity once an order leaves our facility, <strong>we do not accept returns, exchanges, or issue refunds for any reason</strong>. Every purchase is considered final at the time of checkout.</p>
    <p>We encourage all customers to carefully review product details, sizes, and quantities before completing their order.</p>

    <h2>2. Quality You Can Trust</h2>
    <p>Every product we sell is backed by independent third-party lab testing with a Certificate of Analysis (COA) available on each product page. We take pride in delivering exactly what we promise — verified purity, accurate labeling, and secure packaging on every order.</p>
    <p>If you have concerns about a product you received, you are welcome to reach out to our team. While we do not offer refunds or returns, we value your feedback and will address any legitimate quality concerns on a case-by-case basis.</p>

    <h2>3. Order Accuracy</h2>
    <p>Please double-check the following before placing your order:</p>
    <ul>
      <li>Correct compound and size selected</li>
      <li>Accurate shipping address</li>
      <li>Desired quantity</li>
    </ul>
    <p>Once an order is placed and payment is processed, it cannot be modified or cancelled. Orders are typically prepared and shipped promptly, and we are unable to intercept packages once they are in transit.</p>

    <h2>4. Shipping Responsibility</h2>
    <p>Risk of loss and title for all items pass to the buyer upon delivery to the shipping carrier. <?= COMPANY_NAME ?> is not responsible for packages that are lost, stolen, or damaged by the carrier after shipment.</p>
    <p>We recommend using a secure delivery location and tracking your package using the tracking number provided in your shipping confirmation email.</p>

    <div class="legal__contact">
      <h3>Questions?</h3>
      <p>If you have questions about this policy or need assistance with an order, feel free to reach out:</p>
      <p><strong>Email:</strong> <a href="mailto:<?= COMPANY_EMAIL_SUPPORT ?>"><?= COMPANY_EMAIL_SUPPORT ?></a></p>
      <p><strong>Address:</strong> <?= COMPANY_ADDRESS ?>, <?= COMPANY_CITY ?>, <?= COMPANY_STATE ?> <?= COMPANY_ZIP ?></p>
      <p>We aim to respond to all inquiries within 1 business day.</p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
