<?php
/* ============================================================
   ClarityLabsUSA — AI Product Page Template
   Renders an AI-generated product page from the ops API.
   Expects $aiPage (the 'data' blob from /api/v1/products/{sku}/page)
   and the global $canonical_url + shop context.
   ============================================================ */

if (!isset($aiPage) || empty($aiPage)) {
    return;
}

$hero         = $aiPage['hero'] ?? [];
$whyCards     = $aiPage['why_cards'] ?? [];
$audience     = $aiPage['audience'] ?? ['intro' => '', 'profiles' => []];
$researchTxt  = $aiPage['research_overview'] ?? '';
$sizes        = $aiPage['sizes'] ?? [];
$comparison   = $aiPage['comparison'] ?? null;
$brandPhil    = $aiPage['brand_philosophy'] ?? '';
$disclaimer   = $aiPage['disclaimer'] ?? '';
$variants     = $aiPage['variants'] ?? [];
$compoundName = $aiPage['compound'] ?? '';
$category     = $aiPage['category'] ?? '';
$purity       = $aiPage['purity'] ?? '';

// Parse the research overview into paragraphs + callouts
$researchParagraphs = [];
$researchCallouts = [];
if ($researchTxt) {
    $parts = preg_split('/\n\n+/', trim($researchTxt));
    foreach ($parts as $p) {
        if (preg_match('/\{\{CALLOUT:\s*(.+?)\}\}/s', $p, $m)) {
            $researchCallouts[] = trim($m[1]);
            $p = trim(preg_replace('/\{\{CALLOUT:.+?\}\}/s', '', $p));
        }
        if ($p !== '') $researchParagraphs[] = $p;
    }
}
?>

<section class="ai-breadcrumb">
  <div class="ai-breadcrumb__inner">
    <a href="<?= SITE_URL ?>/">ClarityLabs USA</a>
    <span> / </span>
    <a href="<?= SHOP_URL ?>/shop.php">Shop</a>
    <span> / </span>
    <span><?= htmlspecialchars($compoundName) ?></span>
  </div>
</section>

<section class="ai-hero">
  <div class="ai-hero__inner">
    <div class="ai-hero__left">
      <?php if (!empty($hero['category_label'])): ?>
        <div class="ai-hero__eyebrow"><?= htmlspecialchars($hero['category_label']) ?></div>
      <?php endif; ?>
      <h1 class="ai-hero__title"><?= htmlspecialchars($hero['title'] ?? $compoundName) ?></h1>
      <?php if (!empty($hero['subtitle'])): ?>
        <p class="ai-hero__subtitle"><?= htmlspecialchars($hero['subtitle']) ?></p>
      <?php endif; ?>
      <?php if (!empty($hero['intro'])): ?>
        <p class="ai-hero__intro"><?= htmlspecialchars($hero['intro']) ?></p>
      <?php endif; ?>
      <?php if (!empty($hero['trust_bullets']) && is_array($hero['trust_bullets'])): ?>
        <ul class="ai-hero__trust">
          <?php foreach ($hero['trust_bullets'] as $bullet): ?>
            <li>&#10003; <?= htmlspecialchars($bullet) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if (!empty($hero['micro_note'])): ?>
        <p class="ai-hero__micro"><?= htmlspecialchars($hero['micro_note']) ?></p>
      <?php endif; ?>
    </div>
    <div class="ai-hero__right">
      <div class="ai-hero__product-card">
        <div class="ai-hero__product-image">
          <div class="ai-hero__placeholder"><?= htmlspecialchars($compoundName) ?></div>
        </div>
        <?php if (!empty($variants)): ?>
          <div class="ai-hero__sizes">
            <div class="ai-hero__size-label">Select Size</div>
            <?php foreach ($variants as $v): ?>
              <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($v['sku']) ?>" class="ai-hero__size-option">
                <div class="ai-hero__size-mg"><?= htmlspecialchars($v['mg'] ?? '') ?></div>
                <?php if (!empty($v['purity'])): ?>
                  <div class="ai-hero__size-purity"><?= htmlspecialchars($v['purity']) ?>% Pure</div>
                <?php endif; ?>
                <div class="ai-hero__size-price">$<?= number_format((float) ($v['sale_price'] ?? 0), 2) ?></div>
                <?php if (!$v['in_stock']): ?>
                  <div class="ai-hero__size-stock out">Out of Stock</div>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <p class="ai-hero__disclaimer-micro">Research use only. Not for human consumption.</p>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($whyCards)): ?>
<section class="ai-why">
  <div class="ai-why__inner">
    <div class="ai-why__eyebrow">Why Researchers Choose This</div>
    <h2 class="ai-why__heading">Research Positioning</h2>
    <div class="ai-why__grid">
      <?php foreach ($whyCards as $card): ?>
        <div class="ai-why__card">
          <div class="ai-why__icon">&#9678;</div>
          <h3 class="ai-why__card-title"><?= htmlspecialchars($card['title'] ?? '') ?></h3>
          <p class="ai-why__card-desc"><?= htmlspecialchars($card['description'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($audience) && !empty($audience['intro'])): ?>
<section class="ai-audience">
  <div class="ai-audience__inner">
    <div class="ai-audience__grid">
      <div class="ai-audience__left">
        <div class="ai-audience__eyebrow">Designed For</div>
        <h2 class="ai-audience__heading">Who This Is For</h2>
        <p class="ai-audience__intro"><?= htmlspecialchars($audience['intro']) ?></p>
      </div>
      <div class="ai-audience__right">
        <ol class="ai-audience__list">
          <?php foreach (($audience['profiles'] ?? []) as $i => $profile): ?>
            <li>
              <span class="ai-audience__num"><?= $i + 1 ?></span>
              <span class="ai-audience__text"><?= htmlspecialchars($profile) ?></span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($researchParagraphs)): ?>
<section class="ai-overview">
  <div class="ai-overview__inner">
    <div class="ai-overview__eyebrow">Research Overview</div>
    <h2 class="ai-overview__heading">General Research Context</h2>
    <?php foreach ($researchParagraphs as $p): ?>
      <p class="ai-overview__para"><?= htmlspecialchars($p) ?></p>
    <?php endforeach; ?>
    <?php foreach ($researchCallouts as $c): ?>
      <div class="ai-overview__callout">
        <strong>Research Note:</strong> <?= htmlspecialchars($c) ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($sizes) && !empty($variants)): ?>
<section class="ai-sizes">
  <div class="ai-sizes__inner">
    <div class="ai-sizes__eyebrow">Available Sizes</div>
    <h2 class="ai-sizes__heading">Phase Selection</h2>
    <div class="ai-sizes__grid">
      <?php foreach ($sizes as $size):
        // Find the matching variant by SKU
        $matchSku = $size['sku'] ?? null;
        $variant = null;
        foreach ($variants as $v) {
          if ($v['sku'] === $matchSku) { $variant = $v; break; }
        }
        if (!$variant) continue;
      ?>
        <div class="ai-sizes__card <?= !empty($size['popular']) ? 'is-popular' : '' ?>">
          <?php if (!empty($size['popular'])): ?>
            <div class="ai-sizes__badge">Most Popular</div>
          <?php endif; ?>
          <div class="ai-sizes__phase"><?= htmlspecialchars($size['phase_label'] ?? '') ?></div>
          <div class="ai-sizes__title"><?= htmlspecialchars($size['size_title'] ?? $variant['mg']) ?></div>
          <div class="ai-sizes__price">$<?= number_format((float) ($variant['sale_price'] ?? 0), 2) ?></div>
          <p class="ai-sizes__desc"><?= htmlspecialchars($size['description'] ?? '') ?></p>
          <a href="<?= SHOP_URL ?>/product?sku=<?= urlencode($variant['sku']) ?>" class="ai-sizes__cta">
            <?php if ($variant['in_stock']): ?>
              Select &mdash; <?= htmlspecialchars($variant['mg'] ?? '') ?>
            <?php else: ?>
              Out of Stock
            <?php endif; ?>
          </a>
          <?php if (!empty($size['supporting_note'])): ?>
            <p class="ai-sizes__note"><?= htmlspecialchars($size['supporting_note']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($brandPhil)): ?>
<section class="ai-philosophy">
  <div class="ai-philosophy__inner">
    <div class="ai-philosophy__eyebrow">Brand Philosophy</div>
    <h2 class="ai-philosophy__heading">Our Approach</h2>
    <p class="ai-philosophy__text"><?= htmlspecialchars($brandPhil) ?></p>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($disclaimer)): ?>
<section class="ai-disclaimer">
  <div class="ai-disclaimer__inner">
    <p><?= htmlspecialchars($disclaimer) ?></p>
  </div>
</section>
<?php endif; ?>

<style>
/* ============================================================
   AI PRODUCT PAGE STYLES — matches ClarityLabs editorial aesthetic
   ============================================================ */
.ai-breadcrumb { background: #F8F9FA; border-bottom: 1px solid #E4E6EB; padding: 14px 24px; }
.ai-breadcrumb__inner { max-width: 1200px; margin: 0 auto; font-family: 'DM Mono', monospace; font-size: 11px; color: #6B7185; text-transform: uppercase; letter-spacing: 0.5px; }
.ai-breadcrumb a { color: #6B7185; text-decoration: none; }
.ai-breadcrumb a:hover { color: #0B1E3F; }

.ai-hero { background: #fff; padding: 64px 24px 80px; }
.ai-hero__inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 52% 48%; gap: 60px; align-items: start; }
.ai-hero__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; padding: 8px 14px; background: #EDF6F5; border: 1px solid #C8E7E3; border-radius: 6px; display: inline-block; margin-bottom: 20px; }
.ai-hero__title { font-family: 'DM Serif Display', Georgia, serif; font-size: 62px; line-height: 1.05; color: #0B1E3F; margin: 0 0 16px; }
.ai-hero__subtitle { font-family: 'DM Mono', monospace; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #6B7185; margin: 0 0 24px; }
.ai-hero__intro { font-size: 16px; line-height: 1.7; color: #4A4F5E; margin-bottom: 24px; }
.ai-hero__trust { list-style: none; padding: 0; margin: 0 0 20px; }
.ai-hero__trust li { font-size: 13px; color: #2A9D8F; margin-bottom: 8px; font-weight: 600; }
.ai-hero__micro { font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9BA3B5; }

.ai-hero__product-card { background: #F8F9FA; border: 1px solid #E4E6EB; border-radius: 12px; padding: 24px; }
.ai-hero__product-image { background: #fff; border: 1px solid #E4E6EB; border-radius: 8px; aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
.ai-hero__placeholder { font-family: 'DM Mono', monospace; font-size: 18px; color: #C8E7E3; text-transform: uppercase; letter-spacing: 2px; }
.ai-hero__size-label { font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #9BA3B5; margin-bottom: 12px; }
.ai-hero__size-option { display: block; background: #fff; border: 1px solid #E4E6EB; border-radius: 8px; padding: 14px 16px; margin-bottom: 8px; text-decoration: none; transition: all 0.15s; }
.ai-hero__size-option:hover { border-color: #2A9D8F; transform: translateY(-1px); }
.ai-hero__size-mg { font-family: 'DM Mono', monospace; font-size: 14px; color: #0B1E3F; font-weight: 700; text-transform: uppercase; }
.ai-hero__size-purity { font-family: 'DM Mono', monospace; font-size: 10px; color: #6B7185; margin: 2px 0; }
.ai-hero__size-price { font-family: 'DM Serif Display', serif; font-size: 22px; color: #0B1E3F; margin-top: 4px; }
.ai-hero__size-stock.out { font-size: 10px; color: #C44; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
.ai-hero__disclaimer-micro { font-size: 10px; color: #9BA3B5; text-align: center; margin: 16px 0 0; font-style: italic; }

.ai-why { background: #F8F9FA; padding: 72px 24px; }
.ai-why__inner { max-width: 1200px; margin: 0 auto; }
.ai-why__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; margin-bottom: 12px; }
.ai-why__heading { font-family: 'DM Serif Display', Georgia, serif; font-size: 42px; color: #0B1E3F; margin: 0 0 40px; }
.ai-why__grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.ai-why__card { background: #fff; border: 1px solid #E4E6EB; border-radius: 10px; padding: 24px; }
.ai-why__icon { color: #2A9D8F; font-size: 22px; margin-bottom: 12px; }
.ai-why__card-title { font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; color: #0B1E3F; margin: 0 0 10px; }
.ai-why__card-desc { font-size: 13px; color: #6B7185; line-height: 1.6; margin: 0; }

.ai-audience { background: #fff; padding: 72px 24px; }
.ai-audience__inner { max-width: 1200px; margin: 0 auto; }
.ai-audience__grid { display: grid; grid-template-columns: 40% 60%; gap: 60px; }
.ai-audience__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; margin-bottom: 12px; }
.ai-audience__heading { font-family: 'DM Serif Display', Georgia, serif; font-size: 38px; color: #0B1E3F; margin: 0 0 20px; }
.ai-audience__intro { font-size: 15px; color: #4A4F5E; line-height: 1.7; }
.ai-audience__list { list-style: none; padding: 0; margin: 0; }
.ai-audience__list li { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid #E4E6EB; }
.ai-audience__list li:last-child { border-bottom: none; }
.ai-audience__num { font-family: 'DM Serif Display', serif; font-size: 24px; color: #2A9D8F; line-height: 1; flex-shrink: 0; width: 30px; }
.ai-audience__text { font-size: 14px; color: #4A4F5E; line-height: 1.6; padding-top: 4px; }

.ai-overview { background: #F8F9FA; padding: 72px 24px; }
.ai-overview__inner { max-width: 820px; margin: 0 auto; }
.ai-overview__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; margin-bottom: 12px; }
.ai-overview__heading { font-family: 'DM Serif Display', Georgia, serif; font-size: 38px; color: #0B1E3F; margin: 0 0 28px; }
.ai-overview__para { font-size: 15px; color: #4A4F5E; line-height: 1.8; margin-bottom: 18px; }
.ai-overview__callout { background: #FFFBF0; border-left: 3px solid #C9A227; padding: 16px 20px; margin: 24px 0; font-size: 13px; color: #6B5810; line-height: 1.6; }

.ai-sizes { background: #fff; padding: 72px 24px; }
.ai-sizes__inner { max-width: 1200px; margin: 0 auto; }
.ai-sizes__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; margin-bottom: 12px; }
.ai-sizes__heading { font-family: 'DM Serif Display', Georgia, serif; font-size: 42px; color: #0B1E3F; margin: 0 0 40px; }
.ai-sizes__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.ai-sizes__card { background: #fff; border: 1px solid #E4E6EB; border-radius: 12px; padding: 28px; position: relative; transition: all 0.15s; }
.ai-sizes__card.is-popular { border-color: #2A9D8F; border-width: 2px; transform: translateY(-4px); box-shadow: 0 10px 30px rgba(42,157,143,0.12); }
.ai-sizes__badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #2A9D8F; color: #fff; font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 4px 14px; border-radius: 20px; }
.ai-sizes__phase { font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #6B7185; margin-bottom: 8px; }
.ai-sizes__title { font-family: 'DM Serif Display', serif; font-size: 32px; color: #0B1E3F; line-height: 1; margin-bottom: 8px; }
.ai-sizes__price { font-family: 'DM Serif Display', serif; font-size: 28px; color: #2A9D8F; margin-bottom: 16px; }
.ai-sizes__desc { font-size: 13px; color: #6B7185; line-height: 1.6; min-height: 60px; }
.ai-sizes__cta { display: block; background: #0B1E3F; color: #fff; text-align: center; padding: 14px; border-radius: 8px; font-family: 'DM Mono', monospace; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; text-decoration: none; margin-top: 20px; transition: all 0.15s; }
.ai-sizes__cta:hover { background: #1a2f52; }
.ai-sizes__note { font-size: 11px; color: #9BA3B5; text-align: center; margin: 10px 0 0; font-style: italic; }

.ai-philosophy { background: #0B1E3F; color: #fff; padding: 72px 24px; }
.ai-philosophy__inner { max-width: 820px; margin: 0 auto; text-align: center; }
.ai-philosophy__eyebrow { font-family: 'DM Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #2A9D8F; margin-bottom: 12px; }
.ai-philosophy__heading { font-family: 'DM Serif Display', Georgia, serif; font-size: 38px; margin: 0 0 24px; color: #fff; }
.ai-philosophy__text { font-size: 17px; color: rgba(255,255,255,0.85); line-height: 1.8; }

.ai-disclaimer { background: #F8F9FA; padding: 32px 24px; border-top: 1px solid #E4E6EB; }
.ai-disclaimer__inner { max-width: 900px; margin: 0 auto; text-align: center; font-size: 11px; color: #9BA3B5; line-height: 1.6; font-style: italic; }

@media (max-width: 900px) {
  .ai-hero__inner { grid-template-columns: 1fr; gap: 40px; }
  .ai-hero__title { font-size: 42px; }
  .ai-why__grid { grid-template-columns: repeat(2, 1fr); }
  .ai-audience__grid { grid-template-columns: 1fr; gap: 32px; }
  .ai-sizes__grid { grid-template-columns: 1fr; }
  .ai-sizes__card.is-popular { transform: none; }
}
</style>
