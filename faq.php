<?php
$current_page = 'faq';
$page_title = 'Frequently Asked Questions';
$page_description = 'Common questions about ClarityLabs USA research peptides — ordering, shipping, testing, COAs, and more.';
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
    <span class="breadcrumb__current">FAQ</span>
  </div>
</div>

<section class="faq">
  <div class="faq__inner">
    <div class="faq__header">
      <p class="section-label fade-up">Support</p>
      <h1 class="fade-up stagger-1" style="font-size:42px;">Frequently Asked Questions</h1>
      <hr class="teal-rule teal-rule--wide teal-rule--center fade-up stagger-2" style="margin:16px auto;">
    </div>

    <!-- Category Tabs -->
    <div class="faq__tabs fade-up">
      <button class="faq-tab active" data-category="all">All</button>
      <button class="faq-tab" data-category="ordering">Ordering</button>
      <button class="faq-tab" data-category="shipping">Shipping</button>
      <button class="faq-tab" data-category="quality">Quality</button>
      <button class="faq-tab" data-category="testing">Testing &amp; COAs</button>
      <button class="faq-tab" data-category="products">Products</button>
    </div>

    <!-- FAQ Items -->
    <div class="faq-list">
      <!-- ORDERING -->
      <div class="faq-item fade-up" data-category="ordering">
        <button class="faq-question">
          <span class="faq-question__text">How do I place an order?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Browse our shop page, select the compound and size you need, and follow the checkout process on our ecommerce store. All orders are processed securely.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="ordering">
        <button class="faq-question">
          <span class="faq-question__text">What payment methods do you accept?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">We accept all major credit and debit cards, as well as select digital payment options. All transactions are encrypted and processed securely.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="ordering">
        <button class="faq-question">
          <span class="faq-question__text">Can I cancel or modify my order?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Orders can be modified or cancelled within 2 hours of placement. Once an order enters processing, modifications are no longer possible. Contact us as soon as possible if you need to make changes.</div>
        </div>
      </div>

      <!-- SHIPPING -->
      <div class="faq-item fade-up" data-category="shipping">
        <button class="faq-question">
          <span class="faq-question__text">Where do you ship from?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">All orders ship from our US-based fulfillment center. We offer domestic shipping throughout the United States with tracking on every order.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="shipping">
        <button class="faq-question">
          <span class="faq-question__text">How long does shipping take?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Standard shipping typically takes 3-5 business days. Expedited options are available at checkout. You'll receive a tracking number once your order ships.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="shipping">
        <button class="faq-question">
          <span class="faq-question__text">Do you ship internationally?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">At this time, we ship exclusively within the United States. International shipping may be available in the future.</div>
        </div>
      </div>

      <!-- QUALITY -->
      <div class="faq-item fade-up" data-category="quality">
        <button class="faq-question">
          <span class="faq-question__text">What does "research grade" mean?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Research grade refers to compounds manufactured and tested to meet the purity and identity standards required for legitimate scientific research. Every batch undergoes HPLC purity testing, identity verification, and contaminant screening by independent third-party laboratories.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="quality">
        <button class="faq-question">
          <span class="faq-question__text">How do you ensure compound quality?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Every lot undergoes independent third-party laboratory testing before distribution. This includes identity verification (mass spectrometry), purity analysis (HPLC), heavy metals screening, microbial testing, and residual solvent analysis. Lot-specific COAs are available for every batch.</div>
        </div>
      </div>

      <!-- TESTING & COAs -->
      <div class="faq-item fade-up" data-category="testing">
        <button class="faq-question">
          <span class="faq-question__text">What is a Certificate of Analysis (COA)?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">A Certificate of Analysis is a document from an independent testing laboratory that confirms the identity, purity, and safety of a specific batch of a compound. It includes test results for purity (HPLC), identity (MS), heavy metals, microbials, and residual solvents.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="testing">
        <button class="faq-question">
          <span class="faq-question__text">Can I request a COA for my order?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Absolutely. Every lot has a corresponding COA. You can request the specific COA for your batch by contacting us with your order number and lot number. We believe in full transparency.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="testing">
        <button class="faq-question">
          <span class="faq-question__text">Who performs the third-party testing?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Our compounds are tested by independent, accredited analytical laboratories. We use third-party facilities specifically to ensure unbiased, objective results. The testing facility is separate from our production and fulfillment operations.</div>
        </div>
      </div>

      <!-- PRODUCTS -->
      <div class="faq-item fade-up" data-category="products">
        <button class="faq-question">
          <span class="faq-question__text">Are these compounds for human consumption?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">No. All ClarityLabs compounds are sold strictly for research and laboratory use only. They are not intended for human consumption, and by purchasing you agree to use them only for legitimate research purposes.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="products">
        <button class="faq-question">
          <span class="faq-question__text">What sizes are available?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Most compounds are available in three sizes corresponding to research phases: Entry Phase (smaller quantity), Standard Phase (most popular, mid-range), and Extended Phase (larger quantity for extended research). Specific sizes vary by compound.</div>
        </div>
      </div>

      <div class="faq-item fade-up" data-category="products">
        <button class="faq-question">
          <span class="faq-question__text">How should compounds be stored?</span>
          <span class="faq-question__icon">+</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer__inner">Most peptide compounds should be stored in a cool, dry environment away from direct sunlight. Reconstituted compounds should be refrigerated. Specific storage guidelines are included with each product. Proper storage ensures compound stability and research integrity.</div>
        </div>
      </div>
    </div>

    <!-- Bottom CTA -->
    <div class="faq__cta fade-up">
      <p style="font-size:16px;color:var(--navy);font-weight:500;margin-bottom:8px;">Still have questions?</p>
      <p style="color:var(--gray-600);margin-bottom:20px;">Our team is here to help with any research-related inquiries.</p>
      <a href="contact.php" class="btn btn--navy">Contact Us &rarr;</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
