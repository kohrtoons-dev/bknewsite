<?php
$siteBase = '../';
$legalTitle = $legalTitle ?? 'Legal';
$legalDescription = $legalDescription ?? 'BK Traders legal information.';
$legalKeywords = $legalKeywords ?? 'BK Traders, trading terms, trading risk disclosure, privacy policy';
$legalCanonical = $legalCanonical ?? '';
$legalContent = $legalContent ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= htmlspecialchars($legalTitle, ENT_QUOTES, 'UTF-8') ?> | BK Traders</title>
<meta content="<?= htmlspecialchars($legalDescription, ENT_QUOTES, 'UTF-8') ?>" name="description"/>
<meta content="<?= htmlspecialchars($legalKeywords, ENT_QUOTES, 'UTF-8') ?>" name="keywords"/>
<meta content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" name="robots"/>
<?php if ($legalCanonical): ?><link href="<?= htmlspecialchars($legalCanonical, ENT_QUOTES, 'UTF-8') ?>" rel="canonical"/><?php endif; ?>
<link href="../favicon.ico" rel="icon" sizes="any"/>
<link href="../images/favicon.png" rel="icon" type="image/png" sizes="512x512"/>
<link href="../images/favicon-192.png" rel="icon" type="image/png" sizes="192x192"/>
<link href="../images/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="../assets/css/styles.css" rel="stylesheet"/>
<?php require __DIR__ . '/analytics.php'; ?>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php require __DIR__ . '/header.php'; ?>
<?php require __DIR__ . '/ticker.php'; ?>
<main class="legal-page" id="main-content">
<section class="legal-hero">
<div class="wrap">
<div class="eyebrow">BK Traders Policies</div>
<h1><?= htmlspecialchars($legalTitle, ENT_QUOTES, 'UTF-8') ?></h1>
</div>
</section>
<section class="legal-section">
<div class="wrap">
<article class="legal-content"><?= $legalContent ?></article>
</div>
</section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<script src="../assets/js/site.js"></script>
</body>
</html>
