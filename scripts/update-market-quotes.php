<?php
declare(strict_types=1);

/**
 * Refreshes the delayed market quote cache used by the Daily Intel bar.
 * Recommended cPanel cron interval: every five minutes.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$cacheDirectory = dirname(__DIR__) . '/storage/cache';
$cacheFile = $cacheDirectory . '/market-quotes.json';
$lockFile = $cacheDirectory . '/market-quotes-update.lock';

$instruments = array(
    array('symbol' => 'ES=F', 'label' => 'ES1!', 'decimals' => 2),
    array('symbol' => 'NQ=F', 'label' => 'NQ1!', 'decimals' => 2),
    array('symbol' => 'GC=F', 'label' => 'GC1!', 'decimals' => 2),
    array('symbol' => 'CL=F', 'label' => 'CL1!', 'decimals' => 2),
    array('symbol' => 'EURUSD=X', 'label' => 'EUR/USD', 'decimals' => 4),
    array('symbol' => 'JPY=X', 'label' => 'USD/JPY', 'decimals' => 3),
);

if (!is_dir($cacheDirectory) && !mkdir($cacheDirectory, 0755, true) && !is_dir($cacheDirectory)) {
    fwrite(STDERR, "Unable to create quote cache directory.\n");
    exit(1);
}

$lockHandle = fopen($lockFile, 'c');
if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "Market quote update already running.\n");
    exit(0);
}

$quotes = array();
$errors = array();

foreach ($instruments as $instrument) {
    try {
        $encodedSymbol = rawurlencode($instrument['symbol']);
        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . $encodedSymbol . '?interval=5m&range=1d';
        $response = json_decode(fetchQuoteUrl($url), true);
        $result = $response['chart']['result'][0] ?? null;
        $meta = is_array($result) && isset($result['meta']) && is_array($result['meta']) ? $result['meta'] : null;

        if ($meta === null || !isset($meta['regularMarketPrice'])) {
            throw new RuntimeException('No current price was returned.');
        }

        $price = (float) $meta['regularMarketPrice'];
        $previousClose = isset($meta['chartPreviousClose'])
            ? (float) $meta['chartPreviousClose']
            : (isset($meta['previousClose']) ? (float) $meta['previousClose'] : 0.0);
        $change = $previousClose > 0 ? $price - $previousClose : 0.0;
        $changePercent = $previousClose > 0 ? ($change / $previousClose) * 100 : 0.0;

        $quotes[] = array(
            'symbol' => $instrument['symbol'],
            'label' => $instrument['label'],
            'price' => $price,
            'previous_close' => $previousClose,
            'change' => $change,
            'change_percent' => $changePercent,
            'decimals' => $instrument['decimals'],
            'market_time' => isset($meta['regularMarketTime']) ? (int) $meta['regularMarketTime'] : time(),
        );
    } catch (Throwable $exception) {
        $errors[] = $instrument['label'] . ': ' . $exception->getMessage();
    }
}

if (count($quotes) !== count($instruments)) {
    fwrite(STDERR, "Not all market quotes were available; the previous cache was preserved.\n");
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

$payload = array(
    'provider' => 'Yahoo Finance',
    'delayed' => true,
    'updated_at' => gmdate('c'),
    'updated_unix' => time(),
    'quotes' => $quotes,
);

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "Unable to encode market quote cache.\n");
    exit(1);
}

$temporaryFile = $cacheFile . '.tmp-' . getmypid();
if (file_put_contents($temporaryFile, $json . PHP_EOL, LOCK_EX) === false || !rename($temporaryFile, $cacheFile)) {
    @unlink($temporaryFile);
    fwrite(STDERR, "Unable to publish market quote cache.\n");
    exit(1);
}

@chmod($cacheFile, 0644);
fwrite(STDOUT, 'Market quote cache updated with ' . count($quotes) . " instruments.\n");
flock($lockHandle, LOCK_UN);
fclose($lockHandle);

function fetchQuoteUrl(string $url): string
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('cURL could not be initialized.');
        }

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; BKTraders-DailyIntel/1.0)',
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
        ));

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if (!is_string($body) || $body === '' || $status < 200 || $status >= 300) {
            throw new RuntimeException('HTTP ' . $status . ($error !== '' ? ' - ' . $error : ''));
        }

        return $body;
    }

    $context = stream_context_create(array('http' => array(
        'method' => 'GET',
        'timeout' => 15,
        'header' => "User-Agent: Mozilla/5.0 (compatible; BKTraders-DailyIntel/1.0)\r\nAccept: application/json\r\n",
    )));
    $body = @file_get_contents($url, false, $context);

    if (!is_string($body) || $body === '') {
        throw new RuntimeException('The quote feed could not be downloaded.');
    }

    return $body;
}
