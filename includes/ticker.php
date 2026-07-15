<?php
$macroItems = array();
$marketQuotes = array();
$tickerCacheFile = dirname(__DIR__) . '/storage/cache/ticker.json';
$marketQuoteCacheFile = dirname(__DIR__) . '/storage/cache/market-quotes.json';
$tickerCacheMaximumAge = 7 * 86400;

if (is_readable($tickerCacheFile) && filemtime($tickerCacheFile) >= time() - $tickerCacheMaximumAge) {
    $tickerCache = json_decode((string) file_get_contents($tickerCacheFile), true);

    if (is_array($tickerCache) && isset($tickerCache['items']) && is_array($tickerCache['items'])) {
        foreach (array_slice($tickerCache['items'], 0, 4) as $cachedItem) {
            if (!is_array($cachedItem)) {
                continue;
            }

            $label = isset($cachedItem['label']) && is_string($cachedItem['label']) ? trim($cachedItem['label']) : '';
            $text = isset($cachedItem['text']) && is_string($cachedItem['text']) ? trim($cachedItem['text']) : '';
            $url = isset($cachedItem['url']) && is_string($cachedItem['url']) ? trim($cachedItem['url']) : '';

            if ($label === '' || $text === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                continue;
            }

            $macroItems[] = array('label' => $label, 'text' => $text, 'url' => $url);
        }
    }
}

if (is_readable($marketQuoteCacheFile)) {
    $marketQuoteCache = json_decode((string) file_get_contents($marketQuoteCacheFile), true);
    if (is_array($marketQuoteCache) && isset($marketQuoteCache['quotes']) && is_array($marketQuoteCache['quotes'])) {
        foreach ($marketQuoteCache['quotes'] as $quote) {
            if (!is_array($quote) || !isset($quote['label'], $quote['price'], $quote['change_percent'])) {
                continue;
            }
            $marketQuotes[] = $quote;
        }
    }
}

if (count($marketQuotes) === 0) {
    foreach (array('ES1!', 'NQ1!', 'GC1!', 'CL1!', 'EUR/USD', 'USD/JPY') as $fallbackLabel) {
        $marketQuotes[] = array('label' => $fallbackLabel, 'price' => null, 'change_percent' => 0, 'decimals' => 2);
    }
}
?>
<div aria-label="Daily Intel market ticker" class="ticker" id="intel" role="region">
<div class="ticker-label">DAILY INTEL</div>
<div aria-label="Delayed market quotes and latest central bank headlines" class="ticker-content-window">
<div class="ticker-track">
<div class="ticker-group">
<?php foreach ($marketQuotes as $quote): ?>
<?php
$price = $quote['price'];
$percent = (float) $quote['change_percent'];
$decimals = isset($quote['decimals']) ? max(0, min(5, (int) $quote['decimals'])) : 2;
$directionClass = $percent < 0 ? 'dn' : 'up';
?>
<span class="market-item market-quote" title="Delayed market data">
<b class="market-symbol"><?= htmlspecialchars((string) $quote['label'], ENT_QUOTES, 'UTF-8') ?></b>
<?php if (is_numeric($price)): ?>
<span class="market-price"><?= number_format((float) $price, $decimals) ?></span>
<span class="market-change <?= $directionClass ?>"><i aria-hidden="true" class="market-arrow"></i> <?= ($percent >= 0 ? '+' : '') . number_format($percent, 2) ?>%</span>
<?php else: ?>
<span class="market-pending">UPDATING</span>
<?php endif; ?>
</span>
<?php endforeach; ?>
<?php foreach ($macroItems as $macroItem): ?>
<a class="market-item market-note" href="<?= htmlspecialchars($macroItem['url'], ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer" target="_blank"><b><?= htmlspecialchars($macroItem['label'], ENT_QUOTES, 'UTF-8') ?>:</b> <?= htmlspecialchars($macroItem['text'], ENT_QUOTES, 'UTF-8') ?></a>
<?php endforeach; ?>
<span class="market-item market-note"><b>BK LIVE:</b> WEEKDAYS 9:00 AM ET ON YOUTUBE</span>
</div>
</div>
</div>
</div>
