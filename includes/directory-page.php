<?php
$siteBase = '../';
$pageTitle = $pageTitle ?? 'Broker & Prop Firm Directory | BK Traders';
$pageDescription = $pageDescription ?? '';
$canonical = $canonical ?? '';
$pageEyebrow = $pageEyebrow ?? 'BK Traders Directory';
$pageIntro = $pageIntro ?? '';
$cards = $cards ?? [];
$backLabel = $backLabel ?? 'Back to directory';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" name="description"/>
<?php if ($canonical): ?><link href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>" rel="canonical"/><?php endif; ?>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="../assets/css/styles.css" rel="stylesheet"/>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php require __DIR__ . '/header.php'; ?>
<?php require __DIR__ . '/ticker.php'; ?>

<main class="broker-page directory-page" id="main-content">
<section class="broker-hero directory-hero">
<div class="wrap broker-hero-grid">
<div>
<div class="eyebrow"><?= htmlspecialchars($pageEyebrow, ENT_QUOTES, 'UTF-8') ?></div>
<h1><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
<p><?= htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8') ?></p>
<div class="broker-hero-actions">
<a class="btn btn-outline" href="../find-the-best-broker/"><?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?></a>
</div>
</div>
<aside class="broker-disclosure">
<strong>Compare the complete offer</strong>
<p>Promotions, payout terms, rules and eligibility can change. Use these highlights as a starting point and verify current terms directly with the provider.</p>
</aside>
</div>
</section>

<section class="broker-section">
<div class="wrap firm-list">
<?php foreach ($cards as $card): ?>
<article class="firm-card">
<header class="firm-card-heading">
<div>
<span class="firm-category"><?= htmlspecialchars($card['category'], ENT_QUOTES, 'UTF-8') ?></span>
<h2><?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?></h2>
<div aria-label="5 out of 5 stars" class="firm-stars">★★★★★</div>
</div>
<span class="firm-logo-wrap"><img alt="<?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?> logo" class="firm-logo" src="../images/firm-logos/<?= htmlspecialchars($card['logo'], ENT_QUOTES, 'UTF-8') ?>"/></span>
</header>
<p class="firm-summary"><?= htmlspecialchars($card['summary'], ENT_QUOTES, 'UTF-8') ?></p>
<div class="firm-value-grid">
<div>
<h3>VALUE PROPOSITION</h3>
<ul class="firm-points">
<?php foreach ($card['values'] as $value): ?><li><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
</ul>
</div>
<div>
<h3>MARKETS SHOWN</h3>
<ul class="firm-assets">
<?php foreach ($card['markets'] as $market): ?><li><?= htmlspecialchars($market, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
</ul>
</div>
</div>
<?php if (!empty($card['note'])): ?><p class="firm-note"><?= htmlspecialchars($card['note'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<a class="btn btn-gold" href="<?= htmlspecialchars($card['url'], ENT_QUOTES, 'UTF-8') ?>" rel="noopener sponsored" target="_blank"><?= htmlspecialchars($card['cta'], ENT_QUOTES, 'UTF-8') ?></a>
</article>
<?php endforeach; ?>

<div class="affiliate-note"><strong>Affiliate disclosure:</strong> BK Traders may receive compensation when you open an account or purchase through links on this page. This does not change your price. Trading, CFDs and prop-firm evaluations involve substantial risk; no provider or program guarantees profitability, funding or payouts.</div>
</div>
</section>
</main>

<?php require __DIR__ . '/footer.php'; ?>
<script src="../assets/js/site.js"></script>
</body>
</html>
