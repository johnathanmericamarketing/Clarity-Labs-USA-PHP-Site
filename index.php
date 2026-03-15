<?php
$current_page = 'home';
$page_title = 'Research-Grade Peptides';
$page_description = 'ClarityLabs USA provides high-purity research peptides with transparent Certificates of Analysis and independent lab verification. Trusted since 2018.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- ═══════════════════════════════════════════
     HERO
     ═══════════════════════════════════════════ -->
<section class="hero" id="hero">
  <div class="hero__inner">
    <div class="hero__badges fade-up">
      <span class="hero__badge">&#10003; Third-Party Tested</span>
      <span class="hero__badge">&#10003; COA on Every Batch</span>
      <span class="hero__badge">&#10003; US-Based Fulfillment</span>
      <span class="hero__badge">&#10003; Est. 2018</span>
    </div>
    <h1 class="fade-up stagger-1">Research-Grade Peptides.<br>Third-Party Tested.</h1>
    <hr class="teal-rule teal-rule--center fade-up stagger-2">
    <p class="hero__text fade-up stagger-2">ClarityLabs provides high-purity research peptides with transparent Certificates of Analysis and independent lab verification. Trusted by the research community since 2018.</p>
    <div class="hero__ctas fade-up stagger-3">
      <a href="shop.php" class="btn btn--green">Browse Research Peptides</a>
      <a href="#testing" class="btn btn--ghost">View Testing Standards &rarr;</a>
    </div>
    <p class="hero__micro fade-up stagger-4">All compounds sold for research use only.</p>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     AUTHORITY BAND
     ═══════════════════════════════════════════ -->
<section class="authority" id="authority">
  <div class="authority__inner">
    <div class="authority__grid">
      <div class="stat-card fade-up stagger-1">
        <div class="stat-card__rule"></div>
        <div class="stat-card__value" data-count="2100">2,100+</div>
        <div class="stat-card__label">Researchers Worldwide</div>
        <div class="stat-card__sub">Active community members</div>
      </div>
      <div class="stat-card fade-up stagger-2">
        <div class="stat-card__rule"></div>
        <div class="stat-card__value" data-count="7">7+</div>
        <div class="stat-card__label">Years Operating</div>
        <div class="stat-card__sub">Established since 2018</div>
      </div>
      <div class="stat-card fade-up stagger-3">
        <div class="stat-card__rule"></div>
        <div class="stat-card__value">100%</div>
        <div class="stat-card__label">Independently Verified</div>
        <div class="stat-card__sub">Every batch, every lot</div>
      </div>
      <div class="stat-card fade-up stagger-4">
        <div class="stat-card__rule"></div>
        <div class="stat-card__value">US</div>
        <div class="stat-card__label">Domestic Fulfillment</div>
        <div class="stat-card__sub">From the United States</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     EDUCATION
     ═══════════════════════════════════════════ -->
<section class="education" id="education">
  <div class="education__inner">
    <div class="education__row">
      <div class="education__left slide-left">
        <p class="section-label">Understanding Peptides</p>
        <h2>Why Researchers Study Peptides</h2>
        <hr class="teal-rule teal-rule--wide">
        <p>Peptides are short chains of amino acids that act as signaling molecules throughout the body. Researchers study peptides for their potential roles in numerous biological processes &mdash; from cellular repair to immune regulation.</p>
        <p>As scientific literature has grown, so has demand for research-grade compounds with verifiable purity and documented sourcing.</p>
        <a href="shop.php" class="education__cta-link">Explore Available Compounds &rarr;</a>
      </div>
      <div class="education__accent slide-left stagger-2"></div>
      <div class="education__right slide-right">
        <p class="section-label">Areas of Active Research</p>
        <div class="education__pills-row">
          <div class="education__pill fade-up stagger-1">
            <span class="education__pill-icon">&#9678;</span>
            <span>Tissue Repair &amp; Healing</span>
          </div>
          <div class="education__pill fade-up stagger-2">
            <span class="education__pill-icon">&#9678;</span>
            <span>Immune System Signaling</span>
          </div>
        </div>
        <div class="education__pills-row">
          <div class="education__pill fade-up stagger-3">
            <span class="education__pill-icon">&#9678;</span>
            <span>Metabolic Regulation</span>
          </div>
          <div class="education__pill fade-up stagger-4">
            <span class="education__pill-icon">&#9678;</span>
            <span>Cellular Communication</span>
          </div>
        </div>
        <div class="education__pills-row">
          <div class="education__pill fade-up stagger-5">
            <span class="education__pill-icon">&#9678;</span>
            <span>Cognitive Function</span>
          </div>
          <div class="education__pill fade-up stagger-6">
            <span class="education__pill-icon">&#9678;</span>
            <span>Anti-Aging Pathways</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     VIDEO — HOW PEPTIDES ARE MADE
     ═══════════════════════════════════════════ -->
<section class="video-section" id="video">
  <div class="video-section__inner">
    <div class="video-section__header fade-up">
      <p class="section-label section-label--light">Video</p>
      <h2 class="video-section__title">How Peptides Are Made</h2>
      <hr class="teal-rule teal-rule--center">
      <p class="video-section__desc">Peptides and proteins are the building blocks of life, but how does the body create them? This video breaks down the fascinating process of peptide synthesis inside human cells.</p>
    </div>

    <div class="video-section__row">
      <!-- Left: Video Player -->
      <div class="video-section__player fade-up stagger-1">
        <div class="video-wrap" id="videoWrap">
          <div class="video-poster" id="videoPoster">
            <img src="images/video/video-thumb.webp" alt="How Peptides Are Made" loading="lazy">
            <button class="video-play-btn" id="videoPlayBtn" aria-label="Play video">
              <span class="video-play-pulse"></span>
              <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="40" cy="40" r="39" stroke="#2A9D8F" stroke-width="2.5" fill="#ffffff"/>
                <polygon points="34,24 58,40 34,56" fill="#2A9D8F"/>
              </svg>
            </button>
          </div>
          <div class="video-iframe-wrap" id="videoIframeWrap" style="display:none;">
            <iframe
              id="videoIframe"
              src=""
              loading="lazy"
              style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;"
              allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
              allowfullscreen="true"
            ></iframe>
          </div>
        </div>
        <p class="video-section__caption">From DNA transcription to finished peptide — explained in plain terms.</p>
      </div>

      <!-- Right: What You'll Learn -->
      <div class="video-section__learn fade-up stagger-2">
        <p class="video-section__learn-label">What You'll Learn</p>
        <div class="video-section__learn-list">
          <div class="learn-item">
            <span class="learn-check">&#10003;</span>
            <span>How DNA in the nucleus contains genetic instructions</span>
          </div>
          <div class="learn-item">
            <span class="learn-check">&#10003;</span>
            <span>The role of transcription in creating messenger RNA (mRNA)</span>
          </div>
          <div class="learn-item">
            <span class="learn-check">&#10003;</span>
            <span>How ribosomes read mRNA to assemble amino acid chains</span>
          </div>
          <div class="learn-item">
            <span class="learn-check">&#10003;</span>
            <span>The difference between peptides (short chains) and proteins (long, complex structures)</span>
          </div>
          <div class="learn-item">
            <span class="learn-check">&#10003;</span>
            <span>An example of a peptide — BPC-157, which consists of 15 amino acids</span>
          </div>
        </div>
        <a href="shop.php" class="video-section__cta">Browse Our Compounds &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     LAB TESTING / COA
     ═══════════════════════════════════════════ -->
<section class="lab" id="testing">
  <div class="lab__inner">
    <div class="lab__row">

      <!-- Left: COA Document -->
      <div class="lab__left slide-left">
        <div class="coa-wrap">
          <div class="coa-purity">
            <div class="coa-purity__label">Purity</div>
            <div class="coa-purity__value">99.1%</div>
          </div>
          <div class="coa-doc">
            <div class="coa-header">
              <div class="coa-header__name">Precision Analytical Labs</div>
              <div class="coa-header__sub">Independent Third-Party Testing Facility</div>
              <div class="coa-header__num">COA-2024-0891</div>
            </div>
            <div class="coa-compound">
              <div>
                <div class="coa-compound__name">BPC-157</div>
                <div class="coa-compound__sub">Body Protective Compound-157</div>
              </div>
              <div>
                <div class="coa-lot__label">Lot Number</div>
                <div class="coa-lot__value">CL-BPC157-2403</div>
              </div>
            </div>
            <div class="coa-results">
              <div class="coa-results__label">Test Results</div>
              <table class="coa-table">
                <thead><tr><th>Test</th><th>Result</th><th>Status</th></tr></thead>
                <tbody>
                  <tr><td>Purity (HPLC)</td><td class="coa-result-hi">99.1%</td><td><span class="coa-pass">Pass</span></td></tr>
                  <tr><td>Identity (MS)</td><td>Confirmed</td><td><span class="coa-pass">Pass</span></td></tr>
                  <tr><td>Heavy Metals</td><td>&lt; 0.1 ppm</td><td><span class="coa-pass">Pass</span></td></tr>
                  <tr><td>Microbials</td><td>Not Detected</td><td><span class="coa-pass">Pass</span></td></tr>
                  <tr><td>Residual Solvents</td><td>Not Detected</td><td><span class="coa-pass">Pass</span></td></tr>
                </tbody>
              </table>
            </div>
            <div class="coa-sig">
              <div>
                <div class="coa-sig__name">Dr. M. Reinholt</div>
                <div class="coa-sig__role">Senior Analytical Chemist</div>
              </div>
              <div>
                <div class="coa-sig__date-label">Analysis Date</div>
                <div class="coa-sig__date">March 04, 2024</div>
              </div>
            </div>
            <div class="coa-foot">
              <span class="coa-foot__disc">Sample document. Real lot COAs available on request.</span>
              <a href="contact.php" class="coa-foot__link">Request COA &rarr;</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Navy Proof Panel -->
      <div class="lab__right slide-right">
        <div class="lab-panel">
          <p class="lab-panel__eyebrow">Lab Testing</p>
          <h2>Independent Testing.<br>Every Batch.<br>No Exceptions.</h2>
          <hr class="teal-rule teal-rule--wide">
          <p class="lab-panel__body">Every compound undergoes independent third-party laboratory analysis before distribution. The COA on the left is real &mdash; every lot has one.</p>
          <div class="lab-panel__checks">
            <div class="check-item">
              <span class="check-icon">&#10003;</span>
              <span><strong class="check-title">Identity Verification</strong><span class="check-body">Confirms the compound is exactly what it claims to be. No substitutions, no mislabeling.</span></span>
            </div>
            <div class="check-item">
              <span class="check-icon">&#10003;</span>
              <span><strong class="check-title">Purity Testing</strong><span class="check-body">HPLC verification of research-grade purity per lot before release.</span></span>
            </div>
            <div class="check-item">
              <span class="check-icon">&#10003;</span>
              <span><strong class="check-title">Contaminant Screening</strong><span class="check-body">Heavy metals, solvents, and microbials tested before distribution.</span></span>
            </div>
            <div class="check-item">
              <span class="check-icon">&#10003;</span>
              <span><strong class="check-title">Lot-Specific Documentation</strong><span class="check-body">Every lot gets its own COA &mdash; traceable and available on request.</span></span>
            </div>
          </div>
          <div class="lab-panel__badges">
            <span class="lab-badge">&#10003; Identity Verified</span>
            <span class="lab-badge">&#10003; Purity Tested</span>
            <span class="lab-badge">&#10003; Contaminant Screened</span>
          </div>
          <a href="shop.php" class="lab-panel__cta">Browse Batch Certificates &rarr;</a>
          <p class="lab-panel__micro">Every lot tested independently before distribution.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     COMPOUNDS GRID
     ═══════════════════════════════════════════ -->
<section class="compounds" id="compounds">
  <div class="compounds__inner">
    <div class="compounds__header fade-up">
      <div class="compounds__header-left">
        <p class="section-label">Research Compounds</p>
        <h2>Available Compounds</h2>
      </div>
      <a href="shop.php" class="compounds__link">View All Compounds &rarr;</a>
    </div>
    <div class="compounds__grid compounds__grid--collapsed" id="compoundsGrid">
      <?php
      include 'includes/product-data.php';
      $i = 0;
      foreach ($products as $pslug => $p):
        if (!empty($p['hidden'])) continue;
        $stagger = ($i % 4) + 1; ?>
      <a href="products/index.php?product=<?php echo $pslug; ?>" class="compound-card fade-up stagger-<?php echo $stagger; ?>">
        <span class="compound-card__cat"><?php echo htmlspecialchars($p['category']); ?></span>
        <span class="compound-card__name"><?php echo htmlspecialchars($p['name']); ?></span>
        <span class="compound-card__desc"><?php echo htmlspecialchars($p['short_desc']); ?></span>
        <span class="compound-card__link">View Compound &rarr;</span>
      </a>
      <?php $i++; endforeach; ?>
    </div>
    <div class="compounds__toggle fade-up">
      <button class="compounds__see-all" id="compoundsToggle">See All Compounds</button>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
(function() {
  var poster = document.getElementById('videoPoster');
  var iframeWrap = document.getElementById('videoIframeWrap');
  var iframe = document.getElementById('videoIframe');
  var streamSrc = 'https://customer-rm7csfkunwywpekp.cloudflarestream.com/74f7b1c9b593567cc1ff8fce578540d8/iframe?autoplay=true&poster=https%3A%2F%2Fcustomer-rm7csfkunwywpekp.cloudflarestream.com%2F74f7b1c9b593567cc1ff8fce578540d8%2Fthumbnails%2Fthumbnail.jpg%3Ftime%3D1h12m47s%26height%3D600';

  if (poster && iframe && iframeWrap) {
    poster.addEventListener('click', function() {
      iframe.src = streamSrc;
      poster.style.display = 'none';
      iframeWrap.style.display = 'block';
    });
  }

  // Compounds grid toggle
  var grid = document.getElementById('compoundsGrid');
  var toggleBtn = document.getElementById('compoundsToggle');
  if (grid && toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      var isCollapsed = grid.classList.contains('compounds__grid--collapsed');
      grid.classList.toggle('compounds__grid--collapsed');
      toggleBtn.textContent = isCollapsed ? 'Show Less' : 'See All Compounds';
    });
  }
})();
</script>

</body>
</html>
