<?php
declare(strict_types=1);

/**
 * BK Traders Daily Intel cache updater.
 *
 * Run from cPanel cron every five minutes. The public site only reads the
 * generated JSON file; it never waits for these remote feeds during a request.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$cacheDirectory = dirname(__DIR__) . '/storage/cache';
$cacheFile = $cacheDirectory . '/ticker.json';
$lockFile = $cacheDirectory . '/ticker-update.lock';
$maximumItems = 4;
$maximumAgeDays = 30;

$feeds = array(
    array(
        'label' => 'FED',
        'url' => 'https://www.federalreserve.gov/feeds/press_monetary.xml',
        'keywords' => array(),
    ),
    array(
        'label' => 'FED SPEECH',
        'url' => 'https://www.federalreserve.gov/feeds/speeches_and_testimony.xml',
        'keywords' => array(
            'monetary', 'inflation', 'interest rate', 'economic', 'economy',
            'employment', 'labor market', 'financial stability', 'outlook',
        ),
    ),
    array(
        'label' => 'ECB',
        'url' => 'https://www.ecb.europa.eu/rss/press.html',
        'keywords' => array(
            'monetary', 'inflation', 'interest rate', 'economic', 'economy',
            'financial stability', 'exchange rate', 'euro area', 'outlook',
        ),
    ),
);

if (!is_dir($cacheDirectory) && !mkdir($cacheDirectory, 0755, true) && !is_dir($cacheDirectory)) {
    fwrite(STDERR, "Unable to create ticker cache directory.\n");
    exit(1);
}

$lockHandle = fopen($lockFile, 'c');
if ($lockHandle === false) {
    fwrite(STDERR, "Unable to open ticker update lock.\n");
    exit(1);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "Ticker update already running.\n");
    fclose($lockHandle);
    exit(0);
}

$items = array();
$errors = array();

foreach ($feeds as $feed) {
    try {
        $xml = fetchUrl($feed['url']);
        $feedItems = parseFeed($xml, $feed['url'], $feed['label']);

        foreach ($feedItems as $item) {
            if (!headlineMatches($item['text'], $feed['keywords'])) {
                continue;
            }

            if ($item['published_unix'] > 0 && $item['published_unix'] < time() - ($maximumAgeDays * 86400)) {
                continue;
            }

            $items[] = $item;
        }
    } catch (Throwable $exception) {
        $errors[] = $feed['label'] . ': ' . $exception->getMessage();
    }
}

$items = deduplicateItems($items);
usort($items, static function (array $left, array $right): int {
    return $right['published_unix'] <=> $left['published_unix'];
});
$items = array_slice($items, 0, $maximumItems);

if (count($items) === 0) {
    fwrite(STDERR, "No current macro headlines were available; the previous cache was preserved.\n");
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

$payload = array(
    'updated_at' => gmdate('c'),
    'updated_unix' => time(),
    'items' => array_map(static function (array $item): array {
        unset($item['published_unix']);
        return $item;
    }, $items),
);

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "Unable to encode ticker cache.\n");
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

$temporaryFile = $cacheFile . '.tmp-' . getmypid();
if (file_put_contents($temporaryFile, $json . PHP_EOL, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write temporary ticker cache.\n");
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

@chmod($temporaryFile, 0644);
if (!rename($temporaryFile, $cacheFile)) {
    @unlink($temporaryFile);
    fwrite(STDERR, "Unable to publish ticker cache.\n");
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

fwrite(STDOUT, 'Ticker cache updated with ' . count($items) . " macro headlines.\n");
foreach ($errors as $error) {
    fwrite(STDERR, $error . "\n");
}

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

function fetchUrl(string $url): string
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
            CURLOPT_TIMEOUT => 12,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_USERAGENT => 'BKTraders-DailyIntel/1.0 (+https://bktraders.com)',
            CURLOPT_HTTPHEADER => array('Accept: application/rss+xml, application/atom+xml, application/xml, text/xml'),
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

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'timeout' => 12,
            'header' => "User-Agent: BKTraders-DailyIntel/1.0 (+https://bktraders.com)\r\nAccept: application/rss+xml, application/atom+xml, application/xml, text/xml\r\n",
        ),
    ));
    $body = @file_get_contents($url, false, $context);

    if (!is_string($body) || $body === '') {
        throw new RuntimeException('The feed could not be downloaded.');
    }

    return $body;
}

function parseFeed(string $xml, string $sourceUrl, string $label): array
{
    $previousSetting = libxml_use_internal_errors(true);
    $document = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previousSetting);

    if ($document === false) {
        throw new RuntimeException('The downloaded feed was not valid XML.');
    }

    $nodes = $document->xpath('//*[local-name()="item"] | //*[local-name()="entry"]');
    if (!is_array($nodes)) {
        return array();
    }

    $items = array();
    foreach ($nodes as $node) {
        $titleNodes = $node->xpath('./*[local-name()="title"]');
        $linkNodes = $node->xpath('./*[local-name()="link"]');
        $dateNodes = $node->xpath('./*[local-name()="pubDate"] | ./*[local-name()="published"] | ./*[local-name()="updated"] | ./*[local-name()="date"]');

        $title = isset($titleNodes[0]) ? cleanText((string) $titleNodes[0]) : '';
        $link = '';
        if (isset($linkNodes[0])) {
            $attributes = $linkNodes[0]->attributes();
            $link = isset($attributes['href']) ? (string) $attributes['href'] : (string) $linkNodes[0];
        }
        $link = resolveUrl(trim($link), $sourceUrl);

        $dateText = isset($dateNodes[0]) ? trim((string) $dateNodes[0]) : '';
        $publishedUnix = $dateText !== '' ? strtotime($dateText) : false;
        $publishedUnix = $publishedUnix === false ? 0 : $publishedUnix;

        if ($title === '' || $link === '') {
            continue;
        }

        $items[] = array(
            'label' => $label,
            'text' => truncateText($title, 150),
            'url' => $link,
            'published_at' => $publishedUnix > 0 ? gmdate('c', $publishedUnix) : null,
            'published_unix' => $publishedUnix,
        );
    }

    return $items;
}

function cleanText(string $text): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string) preg_replace('/\s+/u', ' ', $text));
}

function truncateText(string $text, int $maximumLength): string
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text, 'UTF-8') > $maximumLength
            ? rtrim(mb_substr($text, 0, $maximumLength - 1, 'UTF-8')) . '…'
            : $text;
    }

    return strlen($text) > $maximumLength
        ? rtrim(substr($text, 0, $maximumLength - 3)) . '...'
        : $text;
}

function resolveUrl(string $url, string $sourceUrl): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $url) === 1) {
        return $url;
    }

    $source = parse_url($sourceUrl);
    if (!isset($source['scheme'], $source['host'])) {
        return '';
    }

    $origin = $source['scheme'] . '://' . $source['host'];
    if (substr($url, 0, 1) === '/') {
        return $origin . $url;
    }

    $path = isset($source['path']) ? dirname($source['path']) : '';
    return $origin . rtrim(str_replace('\\', '/', $path), '/') . '/' . $url;
}

function headlineMatches(string $headline, array $keywords): bool
{
    if (count($keywords) === 0) {
        return true;
    }

    foreach ($keywords as $keyword) {
        if (stripos($headline, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

function deduplicateItems(array $items): array
{
    $unique = array();
    foreach ($items as $item) {
        $key = strtolower($item['label'] . '|' . $item['text']);
        if (!isset($unique[$key])) {
            $unique[$key] = $item;
        }
    }

    return array_values($unique);
}
