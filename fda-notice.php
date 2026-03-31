<?php
$current_page = 'fda-notice';
$page_title = 'FDA Notice & Research Use Disclaimer';
$page_description = 'ClarityLabs USA FDA compliance notice. All products are for in vitro research and laboratory use only. Not for human or veterinary consumption.';
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="legal-page">
  <div class="legal-page__inner">
    <h1>FDA Notice & Research Use Disclaimer</h1>
    <p class="legal-page__updated">Effective Date: March 30, 2026</p>

    <section>
      <h2>1. FDA Compliance Notice</h2>
      <p>The products sold by ClarityLabs USA have <strong>not been evaluated by the United States Food and Drug Administration (FDA)</strong>. They are not approved, endorsed, or regulated as drugs, biologics, medical devices, or dietary supplements by the FDA or any other regulatory agency.</p>
      <p>These products are <strong>not intended to diagnose, treat, cure, mitigate, or prevent any disease, condition, or ailment</strong> in humans or animals.</p>
    </section>

    <section>
      <h2>2. Research Use Only</h2>
      <p>All compounds sold by ClarityLabs USA are intended <strong>exclusively for in vitro research and laboratory use</strong> by qualified professionals. "In vitro" refers to studies performed outside of a living organism, typically in a controlled laboratory setting.</p>
      <p>Our products are designed for use by:</p>
      <ul>
        <li>Academic and institutional researchers</li>
        <li>Licensed laboratory professionals</li>
        <li>Qualified scientists conducting lawful research</li>
      </ul>
      <p>Products are <strong>not intended for</strong>:</p>
      <ul>
        <li>Human or veterinary consumption</li>
        <li>Clinical or therapeutic use</li>
        <li>Self-administration in any form</li>
        <li>Use as food, food additives, or dietary supplements</li>
        <li>Compounding, dispensing, or prescribing by unlicensed practitioners</li>
      </ul>
    </section>

    <section>
      <h2>3. Product Form & Handling</h2>
      <p>All products are sold in <strong>lyophilized (freeze-dried) powder form</strong> and require reconstitution with a suitable diluent for research applications. Research supplies such as syringes, bacteriostatic water, and reconstitution materials are not included with any order.</p>
      <p>No dosing instructions, administration guidance, or therapeutic protocols are provided by ClarityLabs USA. Any information presented on this website regarding compound properties, mechanisms, or research applications is derived from published scientific literature and is provided for informational purposes only.</p>
    </section>

    <section>
      <h2>4. Age Requirement</h2>
      <p>All purchasers must be <strong>21 years of age or older</strong>. By creating an account and placing an order, you confirm that you meet this age requirement and are a qualified research professional acting within applicable laws and regulations.</p>
    </section>

    <section>
      <h2>5. No Health Claims</h2>
      <p>ClarityLabs USA makes no health claims regarding any product. References to published research, preclinical studies, or scientific literature on this website or in product descriptions do not constitute medical advice, health claims, or therapeutic recommendations.</p>
      <p>Any customer reviews, testimonials, or user-generated content on this website reflect individual experiences and opinions. They are not endorsed by ClarityLabs USA as medical or health claims and should not be interpreted as such.</p>
    </section>

    <section>
      <h2>6. Third-Party Testing & Certificates of Analysis</h2>
      <p>All products undergo independent third-party purity testing. A <strong>Certificate of Analysis (COA)</strong> is available for every batch, verifying identity and purity via HPLC (High-Performance Liquid Chromatography) and other validated analytical methods.</p>
      <p>COAs are provided for transparency and quality assurance in a research context. They do not certify suitability for human use.</p>
    </section>

    <section>
      <h2>7. State & Jurisdictional Restrictions</h2>
      <p>Some states, counties, or jurisdictions may have additional regulations governing the sale, possession, or use of research compounds. It is the buyer's sole responsibility to understand and comply with all applicable local, state, and federal laws before placing an order.</p>
      <p>ClarityLabs USA reserves the right to refuse or cancel orders to any jurisdiction where the sale of our products may be restricted or prohibited.</p>
    </section>

    <section>
      <h2>8. Export Restrictions</h2>
      <p>Products sold by ClarityLabs USA are intended for domestic (United States) delivery only. We do not ship internationally. Any attempt to re-export products purchased from ClarityLabs USA is the sole responsibility of the purchaser and may be subject to U.S. export control laws.</p>
    </section>

    <section>
      <h2>9. Limitation of Liability</h2>
      <p>ClarityLabs USA, its officers, employees, and affiliates shall not be held liable for any damages, injuries, or legal consequences arising from the misuse, improper handling, or unauthorized application of any product purchased from this website. By purchasing, the buyer assumes all responsibility for the lawful and appropriate use of all products.</p>
    </section>

    <section>
      <h2>10. Contact</h2>
      <p>For questions regarding this notice or our compliance practices, please contact us at <a href="mailto:<?= COMPANY_EMAIL_SUPPORT ?>"><?= COMPANY_EMAIL_SUPPORT ?></a>.</p>
    </section>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
