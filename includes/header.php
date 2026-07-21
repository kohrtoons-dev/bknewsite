<?php $siteBase = isset($siteBase) ? $siteBase : ''; ?>
<header class="site-header">
<div class="wrap nav">
<a class="nav-logo" href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>#home">
<img alt="BK Traders" src="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>images/bk-traders-logo.png"/>
</a>
<button aria-controls="primary-navigation" aria-expanded="false" aria-label="Open menu" class="burger" type="button">
<span aria-hidden="true" class="burger-icon"></span>
</button>
<nav aria-label="Primary navigation" class="nav-links" id="primary-navigation">
<a href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>#home">Home</a>
<a href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>#indicators">Indicators</a>
<a href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>#education">Education</a>
<a href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>find-the-best-broker/">Best Brokers</a>
<a href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>find-the-best-broker/#prop-firms">Prop Firms</a>
<a href="https://bktradertools.com/intel">Daily Intel</a>
<a class="nav-get-started" href="<?= htmlspecialchars($siteBase, ENT_QUOTES, 'UTF-8') ?>#pricing">Get Started</a>
<a class="nav-cta" href="https://members.bktraders.com">Members Login</a>
</nav>
</div>
</header>
