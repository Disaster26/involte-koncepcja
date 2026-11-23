<?php defined('ABSPATH') || exit; $k = iv_kontekst(); ?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?php echo $k['noindex'] ? 'noindex,follow' : 'index,follow,max-image-preview:large'; ?>">
<title><?php echo esc_html($k['title']); ?></title>
<meta name="description" content="<?php echo esc_attr($k['desc']); ?>">
<?php if ($k['canon']) : ?><link rel="canonical" href="<?php echo esc_url($k['canon']); ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:site_name" content="inVolte">
<meta property="og:title" content="<?php echo esc_attr($k['title']); ?>">
<meta property="og:description" content="<?php echo esc_attr($k['desc']); ?>">
<?php if ($k['canon']) : ?><meta property="og:url" content="<?php echo esc_url($k['canon']); ?>">
<?php endif; ?>
<meta property="og:image" content="<?php echo esc_url(home_url('/img/og.jpg')); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Dom z instalacją fotowoltaiczną - inVolte">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/img/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/img/ikona-96.png" sizes="96x96" type="image/png">
<link rel="apple-touch-icon" href="/img/ikona-180.png">
<link rel="manifest" href="data:application/manifest+json,<?php echo rawurlencode(wp_json_encode(['name' => 'inVolte - fotowoltaika Swarzędz', 'short_name' => 'inVolte', 'start_url' => home_url('/'), 'display' => 'browser', 'background_color' => '#ffffff', 'theme_color' => '#ffffff', 'icons' => [['src' => home_url('/img/ikona-192.png'), 'sizes' => '192x192', 'type' => 'image/png'], ['src' => home_url('/img/ikona-512.png'), 'sizes' => '512x512', 'type' => 'image/png']]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>">
<?php echo $k['pre']; ?><link rel="preload" as="font" type="font/woff2" href="/fonts/archivo-rdzen.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/archivo-pl.woff2" crossorigin>
<style>
<?php echo IV_CSS; ?></style>
<?php foreach ($k['ld'] as $x) echo '<script type="application/ld+json">' . wp_json_encode($x, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n"; ?>
<?php wp_head(); ?>
</head>
<body>
<?php echo IV_DEFS; ?>


<header class="top" id="top">
  <div class="wrap">
    <a class="logo" href="<?php echo esc_url(iv_u()); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?> aria-label="inVolte - strona główna"><img src="/img/logo.webp" srcset="/img/logo-300.webp 300w, /img/logo.webp 600w" sizes="150px" width="600" height="237" alt="inVolte"></a>
    <?php echo iv_nav($k['slug']); ?>
    <a class="tel" href="<?php echo esc_attr(iv_tel_href()); ?>"><?php echo IV_SVGTEL . esc_html(iv_dane('tel')); ?></a>
    <button class="burg" id="burg" aria-label="Otwórz menu" aria-expanded="false" aria-controls="nav"><span></span><span></span><span></span></button>
  </div>
</header>

<main>
