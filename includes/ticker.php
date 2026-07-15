<?php
$macroItems = array();
$tickerCacheFile = dirname(__DIR__) . '/storage/cache/ticker.json';
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
?>
<div aria-label="Daily Intel market ticker" class="ticker" id="intel" role="region">
<div class="ticker-label">DAILY INTEL</div>
<div aria-live="off" class="ticker-window">
<div class="ticker-track">
<div class="ticker-group">
<span class="market-item"><b class="market-symbol">ES</b><i aria-hidden="true" class="market-arrow dn">&#9660;</i><span class="market-price">6,231</span><b class="market-change dn">&minus;0.2%</b></span>
<span class="market-item"><b class="market-symbol">NQ</b><i aria-hidden="true" class="market-arrow up">&#9650;</i><span class="market-price">21,847</span><b class="market-change up">+0.6%</b></span>
<span class="market-item"><b class="market-symbol">GOLD</b><i aria-hidden="true" class="market-arrow up">&#9650;</i><span class="market-price">3,412</span><b class="market-change up">+0.4%</b></span>
<span class="market-item"><b class="market-symbol">EUR/USD</b><i aria-hidden="true" class="market-arrow up">&#9650;</i><span class="market-price">1.0912</span></span>
<?php foreach ($macroItems as $macroItem): ?>
<a class="market-item market-note" href="<?= htmlspecialchars($macroItem['url'], ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer" target="_blank"><b><?= htmlspecialchars($macroItem['label'], ENT_QUOTES, 'UTF-8') ?>:</b> <?= htmlspecialchars($macroItem['text'], ENT_QUOTES, 'UTF-8') ?></a>
<?php endforeach; ?>
<span class="market-item market-note"><b>BK LIVE:</b> WEEKDAYS 9:00 AM ET ON YOUTUBE</span>
</div>
</div>
</div>
</div>
