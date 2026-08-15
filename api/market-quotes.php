<?php
declare(strict_types=1);

/*
 * Public, read-only market feed for the BK Traders OBS ticker.
 *
 * Yahoo and the central-bank feeds are contacted only by the existing cron
 * scripts. This endpoint serves their last successful cache, keeping page and
 * OBS requests fast while avoiding duplicate upstream requests.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Accept');
header('Cache-Control: public, max-age=30, stale-while-revalidate=120');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, OPTIONS');
    respond(array('status' => 'error', 'message' => 'Method not allowed.'), 405);
}

$cacheDirectory = dirname(__DIR__) . '/storage/cache';
$quoteCacheFile = $cacheDirectory . '/market-quotes.json';
$newsCacheFile = $cacheDirectory . '/ticker.json';
$allowedSymbols = array('ES=F', 'NQ=F', 'GC=F', 'CL=F', 'EURUSD=X', 'JPY=X');

$quoteCache = readJsonFile($quoteCacheFile);
if ($quoteCache === null || !isset($quoteCache['quotes']) || !is_array($quoteCache['quotes'])) {
    respond(array('status' => 'error', 'message' => 'Market quote cache is unavailable.'), 503);
}

$quotes = array();
foreach ($quoteCache['quotes'] as $quote) {
    if (!is_array($quote) || !isset($quote['symbol'], $quote['label'], $quote['price'])) {
        continue;
    }

    $symbol = strtoupper(trim((string) $quote['symbol']));
    if (!in_array($symbol, $allowedSymbols, true) || !is_numeric($quote['price'])) {
        continue;
    }

    $quotes[] = array(
        'symbol' => $symbol,
        'label' => trim((string) $quote['label']),
        'price' => (float) $quote['price'],
        'previous_close' => isset($quote['previous_close']) && is_numeric($quote['previous_close']) ? (float) $quote['previous_close'] : null,
        'change' => isset($quote['change']) && is_numeric($quote['change']) ? (float) $quote['change'] : null,
        'change_percent' => isset($quote['change_percent']) && is_numeric($quote['change_percent']) ? (float) $quote['change_percent'] : null,
        'decimals' => isset($quote['decimals']) ? max(0, min(5, (int) $quote['decimals'])) : 2,
        'market_time' => isset($quote['market_time']) ? (int) $quote['market_time'] : null,
    );
}

if (count($quotes) === 0) {
    respond(array('status' => 'error', 'message' => 'Market quote cache contains no usable quotes.'), 503);
}

$news = array();
$newsCache = readJsonFile($newsCacheFile);
if ($newsCache !== null && isset($newsCache['items']) && is_array($newsCache['items'])) {
    foreach (array_slice($newsCache['items'], 0, 6) as $item) {
        if (!is_array($item) || !isset($item['label'], $item['text'])) {
            continue;
        }

        $label = trim((string) $item['label']);
        $text = trim((string) $item['text']);
        if ($label === '' || $text === '') {
            continue;
        }

        $news[] = array(
            'label' => $label,
            'text' => $text,
            'url' => isset($item['url']) && filter_var($item['url'], FILTER_VALIDATE_URL) !== false ? (string) $item['url'] : null,
            'published_at' => isset($item['published_at']) ? (string) $item['published_at'] : null,
        );
    }
}

respond(array(
    'status' => 'ok',
    'provider' => isset($quoteCache['provider']) ? (string) $quoteCache['provider'] : 'Yahoo Finance',
    'delayed' => true,
    'updated_at' => isset($quoteCache['updated_at']) ? (string) $quoteCache['updated_at'] : null,
    'updated_unix' => isset($quoteCache['updated_unix']) ? (int) $quoteCache['updated_unix'] : null,
    'stale' => filemtime($quoteCacheFile) < time() - 900,
    'quotes' => $quotes,
    'news' => $news,
));

function readJsonFile(string $path): ?array
{
    if (!is_readable($path)) {
        return null;
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    return is_array($decoded) ? $decoded : null;
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit;
}
