<?php
// inVolte: podawanie gotowych stron z pamieci podrecznej (zapis i czyszczenie w motywie, inc/cache.php)
if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '')) return;
foreach ($_COOKIE as $k => $v) if (preg_match('~^(wordpress_logged_in|wp-postpass|comment_author|wordpress_sec)~', $k)) return;
$iv_u = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($iv_u) || preg_match('~/wp-(admin|login|json|cron)|\.php|/feed/|sitemap~', $iv_u)) return;
$iv_f = WP_CONTENT_DIR . '/cache/iv/' . md5(($_SERVER['HTTP_HOST'] ?? '') . $iv_u) . '.html';
if (is_file($iv_f)) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('X-IV-Cache: HIT');
    readfile($iv_f);
    exit;
}
define('IV_CACHE_PLIK', $iv_f);
