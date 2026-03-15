<?php
$current_page = 'contact';
$page_title = 'Contact Us';
$page_description = 'Get in touch with ClarityLabs USA. Questions about research compounds, testing, or orders? We\'re here to help.';
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
    <span class="breadcrumb__current">Contact</span>
  </div>
</div>

<section class="contact">
  <div class="contact__inner">
    <div style="text-align:center;margin-bottom:48px;">
      <p class="section-label fade-up">Get in Touch</p>
      <h1 class="fade-up stagger-1" style="font-size:42px;">Contact Us</h1>
      <hr class="teal-rule teal-rule--wide teal-rule--center fade-up stagger-2" style="margin:16px auto;">
      <p class="fade-up stagger-2" style="max-width:500px;margin:0 auto;color:var(--gray-600);">Have questions about our compounds, testing standards, or need help with an order? We'd love to hear from you.</p>
    </div>

    <div class="contact__row">
      <!-- Form -->
      <div class="contact__form-wrap slide-left">
        <form id="contact-form" method="post" style="position:relative;">
          <!-- Honeypot -->
          <div class="honeypot">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="form-group">
            <label class="form-label" for="name">Full Name *</label>
            <input type="text" name="name" id="name" class="form-input" placeholder="Your name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" name="email" id="email" class="form-input" placeholder="you@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" class="form-input" placeholder="(Optional)">
          </div>
          <div class="form-group">
            <label class="form-label" for="subject">Subject *</label>
            <select name="subject" id="subject" class="form-select" required>
              <option value="">Select a topic...</option>
              <option value="general">General Inquiry</option>
              <option value="products">Product Question</option>
              <option value="orders">Order Support</option>
              <option value="testing">Testing & COAs</option>
              <option value="wholesale">Wholesale / Bulk</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="message">Message *</label>
            <textarea name="message" id="message" class="form-textarea" placeholder="How can we help?" required></textarea>
          </div>
          <button type="submit" class="btn btn--navy btn--full">Send Message</button>
          <div id="form-success" class="form-success" style="margin-top:16px;"></div>
          <div id="form-error" class="form-error" style="margin-top:16px;display:none;font-size:13px;"></div>
        </form>
      </div>

      <!-- Info Panel -->
      <div class="contact__info slide-right">
        <div class="contact-info__card">
          <h4 class="contact-info__title">Research Support</h4>
          <p class="contact-info__text">Our team is available to answer questions about our compounds, provide COA documentation, and assist with research-related inquiries.</p>
        </div>
        <div class="contact-info__card">
          <h4 class="contact-info__title">Business Hours</h4>
          <p class="contact-info__text">
            Monday &ndash; Friday: 9:00 AM &ndash; 5:00 PM EST<br>
            Saturday &ndash; Sunday: Closed
          </p>
        </div>
        <div class="contact-info__card">
          <h4 class="contact-info__title">Response Time</h4>
          <p class="contact-info__text">We typically respond to all inquiries within 24 business hours. For urgent matters, please note "URGENT" in your subject line.</p>
        </div>
        <div class="contact-info__card" style="background:var(--navy);">
          <h4 class="contact-info__title" style="color:var(--white);">Quality Promise</h4>
          <p class="contact-info__text" style="color:rgba(255,255,255,.6);">Every compound we sell is independently tested and ships with lot-specific documentation. Your research deserves that standard.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
