<?php
// motyw inVolte: wyglad 1:1 z projektu, tresci w panelu, bez wtyczek
defined('ABSPATH') || exit;
define('IV_WERSJA', '1.0.0');
require_once __DIR__ . '/inc/dane.php';
require_once __DIR__ . '/inc/panel.php';
require_once __DIR__ . '/inc/realizacje.php';
require_once __DIR__ . '/inc/formularz.php';
require_once __DIR__ . '/inc/cache.php';

/* ---------- ustawienia i porzadki ---------- */
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails', ['realizacja']);
    add_theme_support('html5', ['gallery', 'caption', 'style', 'script']);
    remove_post_type_support('page', 'comments');
    remove_post_type_support('page', 'trackbacks');
});
foreach ([['wp_head', 'print_emoji_detection_script', 7], ['wp_print_styles', 'print_emoji_styles', 10], ['admin_print_scripts', 'print_emoji_detection_script', 10],
    ['admin_print_styles', 'print_emoji_styles', 10], ['wp_head', 'rsd_link', 10], ['wp_head', 'wlwmanifest_link', 10], ['wp_head', 'wp_generator', 10],
    ['wp_head', 'wp_shortlink_wp_head', 10], ['wp_head', 'rest_output_link_wp_head', 10], ['wp_head', 'wp_oembed_add_discovery_links', 10],
    ['wp_head', 'feed_links', 2], ['wp_head', 'feed_links_extra', 3], ['wp_head', 'rel_canonical', 10], ['wp_head', 'wp_robots', 1],
    ['wp_head', 'wp_site_icon', 99], ['wp_head', 'wp_print_auto_sizes_contain_css_fix', 1], ['wp_head', 'wp_resource_hints', 2],
    ['wp_enqueue_scripts', 'wp_enqueue_global_styles', 10], ['wp_footer', 'wp_enqueue_global_styles', 1], ['wp_body_open', 'wp_global_styles_render_svg_filters', 10],
    ['wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles', 10], ['template_redirect', 'rest_output_link_header', 11], ['template_redirect', 'wp_shortlink_header', 11]] as $x) {
    remove_action($x[0], $x[1], $x[2]);
}
add_filter('emoji_svg_url', '__return_false');
add_action('wp_enqueue_scripts', function () {
    foreach (['wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports'] as $s) wp_dequeue_style($s);
}, 100);
add_filter('should_load_separate_core_block_assets', '__return_true');
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_sitemaps_enabled', '__return_false');
add_filter('comments_open', '__return_false');
add_filter('pings_open', '__return_false');
add_filter('wp_headers', function ($h) { unset($h['X-Pingback']); return $h; });
add_filter('auto_core_update_send_email', '__return_false');
add_filter('auto_plugin_update_send_email', '__return_false');
add_filter('auto_theme_update_send_email', '__return_false');
add_filter('rest_endpoints', function ($e) {
    if (!is_user_logged_in()) { unset($e['/wp/v2/users'], $e['/wp/v2/users/(?P<id>[\d]+)']); }
    return $e;
});
// zdjecia z panelu zapisywane jako WebP
add_filter('image_editor_output_format', function ($f) { $f['image/jpeg'] = 'image/webp'; $f['image/png'] = 'image/webp'; return $f; });
add_filter('wp_editor_set_quality', function ($q, $mime) { return $mime === 'image/webp' ? 80 : $q; }, 10, 2);

// archiwa autorow, zalacznikow, kanaly RSS i wyszukiwarka - przekierowanie (strona firmowa bez bloga)
add_action('template_redirect', function () {
    if (is_author() || is_feed() || is_search() || is_date() || is_category() || is_tag() || (is_home() && !is_front_page())) { wp_safe_redirect(home_url('/'), 301); exit; }
    if (is_attachment()) { $p = wp_get_post_parent_id(get_the_ID()); wp_safe_redirect($p && get_post_type($p) === 'page' ? get_permalink($p) : home_url('/'), 301); exit; }
    if (is_singular('post')) { wp_safe_redirect(home_url('/'), 301); exit; }
}, 1);

/* ---------- edytor: klasyczny, zachowuje uklad HTML ---------- */
add_filter('use_block_editor_for_post', '__return_false');
add_filter('use_block_editor_for_post_type', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');
add_filter('wp_editor_settings', function ($s) { $s['wpautop'] = false; return $s; });
add_filter('tiny_mce_before_init', function ($i) {
    $i['wpautop'] = false;
    $i['verify_html'] = false;
    $i['valid_children'] = '+a[div|img|figure|span],+dl[div],+div[dl|dt|dd]';
    $i['extended_valid_elements'] = 'svg[*],use[*],details[*],summary[*],dl[*],dt[*],dd[*]';
    $i['block_formats'] = 'Akapit=p;Nagłówek sekcji=h2;Podtytuł=h3';
    return $i;
});
remove_filter('the_content', 'wpautop');
remove_filter('the_content', 'wptexturize');
remove_filter('the_content', 'convert_smilies');
remove_filter('the_title', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');
add_filter('the_content', function ($c) {
    // edytor wizualny owija kody galerii w akapit - zdejmujemy go, zeby galeria nie lądowala w <p>
    return preg_replace('~<p>\s*((?:\[(?:galeria_realizacji|opinie_google)\]\s*(?:<br\s*/?>|&nbsp;)?\s*)+)</p>~', '$1', $c);
}, 9);
add_filter('the_content', function ($c) {
    $c = preg_replace('~<p>(?:\s|&nbsp;|<br\s*/?>)*</p>~', '', $c);
    // linki wewnetrzne zapisane od korzenia (/kontakt/) prowadza na adres strony
    $c = preg_replace('~(href=")/(?!/|img/|fonts/)~', '$1' . untrailingslashit(home_url()) . '/', $c);
    // znak z logo w przyciskach
    $c = preg_replace('~(<a\s[^>]*class="btn[^"]*"[^>]*>)(?!\s*<svg)~', '$1' . IV_MK, $c);
    return $c;
}, 20);

/* ---------- pomocnicze ---------- */
function iv_u($sciezka = '') { return home_url('/' . ltrim($sciezka, '/')); }
function iv_e($s) { return esc_html($s); }
function iv_dane($k = null) {
    static $d = null;
    if ($d === null) $d = wp_parse_args((array)get_option('iv_dane', []), iv_dane_domyslne());
    return $k === null ? $d : ($d[$k] ?? '');
}
function iv_dane_domyslne() {
    return ['firma' => 'inVolte Arkadiusz Witt', 'tel' => '570 083 207', 'email' => 'involte@involte.pl', 'email_form' => 'involte@involte.pl',
        'adres1' => 'Gwiaździsta 57, Swarzędz', 'adres2' => 'Akacjowa 8, 63-012 Dominowo', 'nip' => '783-001-76-60', 'opinie' => '',
        'opis_stopka' => 'Fotowoltaika dla domów i firm: projekt, dokumentacja, montaż, doradztwo i serwis instalacji.'];
}
function iv_tel_href() { $t = preg_replace('~[^0-9+]~', '', iv_dane('tel')); if ($t !== '' && $t[0] !== '+') $t = '+48' . $t; return 'tel:' . $t; }
function iv_adres_krotki($a) { return trim(preg_replace('~\s*\d{2}-\d{3}\s*~', ' ', $a)); }
function iv_adresy() { return array_values(array_filter([iv_dane('adres1'), iv_dane('adres2')])); }
function iv_miejscowosc($a) { $p = explode(',', $a); return trim(preg_replace('~^\d{2}-\d{3}\s*~', '', trim(end($p)))); }
function iv_t($k) {
    static $o = null;
    if ($o === null) $o = (array)get_option('iv_home', []);
    $v = isset($o[$k]) && trim($o[$k]) !== '' ? $o[$k] : (IV_POLA[$k][2] ?? '');
    echo wp_kses_post($v);
}
// krotkie nazwy podstron (menu, okruszki) z panelu
function iv_krotki($slug, $domyslna = '') {
    static $mapa = null;
    if ($mapa === null) {
        $mapa = [];
        foreach (get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'meta_key' => '_iv_krotki']) as $p) {
            $k = get_post_meta($p->ID, '_iv_krotki', true);
            if ($k !== '') $mapa[$p->post_name] = $k;
        }
    }
    return $mapa[$slug] ?? $domyslna;
}
function iv_lista($grupa) {
    $l = $grupa === 'foto' ? IV_FOTO : IV_OFERTA;
    return array_map(fn($x) => [$x[0], iv_krotki($x[0], $x[1])], $l);
}
function iv_meta($id) {
    return ['lead' => (string)get_post_meta($id, '_iv_lead', true), 'foto' => (string)get_post_meta($id, '_iv_foto', true),
        'grupa' => (string)get_post_meta($id, '_iv_grupa', true), 'szer' => (bool)get_post_meta($id, '_iv_szer', true),
        'seo' => (string)get_post_meta($id, '_iv_seo', true), 'opis' => (string)get_post_meta($id, '_iv_opis', true),
        'krotki' => (string)get_post_meta($id, '_iv_krotki', true)];
}
function iv_okr($id) {
    $m = iv_meta($id);
    $slug = get_post_field('post_name', $id);
    $o = [];
    if ($m['grupa'] === 'foto') $o[] = ['Fotowoltaika', null];
    if ($m['grupa'] === 'oferta') $o[] = ['Oferta', null];
    $o[] = [$m['krotki'] !== '' ? $m['krotki'] : get_the_title($id), $slug];
    return $o;
}

/* ---------- fragmenty ukladu ---------- */
function iv_nav($slug) {
    $a = function ($s, $t) use ($slug) { return '<a href="' . esc_url(iv_u($s . '/')) . '"' . ($s === $slug ? ' aria-current="page"' : '') . '>' . iv_e($t) . '</a>'; };
    $grp = function ($nazwa, $lista, $idg) use ($slug, $a) {
        $akt = false; $links = '';
        foreach ($lista as $x) { if ($x[0] === $slug) $akt = true; $links .= $a($x[0], $x[1]); }
        return '<div class="grp' . ($akt ? ' akt' : '') . '"><button class="grp-b" type="button" aria-expanded="false" aria-controls="' . $idg . '">' . $nazwa . '</button>'
            . '<div class="sub" id="' . $idg . '">' . $links . '</div></div>';
    };
    return '<nav class="nav" id="nav" aria-label="Menu">' . $a('o-nas', 'O nas') . $grp('Fotowoltaika', iv_lista('foto'), 'm-foto') . $grp('Oferta', iv_lista('oferta'), 'm-oferta')
        . $a('nasze-realizacje', 'Realizacje') . $a('cennik', 'Cennik') . $a('kontakt', 'Kontakt')
        . '<a class="mtel" href="' . esc_attr(iv_tel_href()) . '">Zadzwoń: ' . iv_e(iv_dane('tel')) . '</a></nav>';
}

function iv_glowka($okr, $h1, $lead, $foto) {
    $lis = '<li><a href="' . esc_url(iv_u()) . '">Strona główna</a></li>';
    foreach (array_slice($okr, 0, -1) as $x) $lis .= $x[1] ? '<li><a href="' . esc_url(iv_u($x[1] . '/')) . '">' . iv_e($x[0]) . '</a></li>' : '<li>' . iv_e($x[0]) . '</li>';
    $lis .= '<li aria-current="page">' . iv_e(end($okr)[0]) . '</li>';
    $tx = '<div class="gl-tx"><nav class="okr" aria-label="Okruszki"><ol>' . $lis . '</ol></nav><h1>' . $h1 . '</h1>' . ($lead !== '' ? '<p class="lead">' . $lead . '</p>' : '') . '</div>';
    if ($foto && isset(IV_FOTY[$foto])) {
        $f = IV_FOTY[$foto];
        $srcset = implode(', ', array_map(fn($r) => '/img/' . $f['n'] . '-' . $r[0] . '.webp ' . $r[0] . 'w', $f['rozm']));
        [$w0, $h0] = $f['rozm'][1];
        $fot = '<div class="gl-fot"><img src="/img/' . $f['n'] . '-' . $w0 . '.webp" srcset="' . $srcset . '" sizes="(max-width:980px) 100vw, 42vw" width="' . $w0 . '" height="' . $h0 . '" '
            . 'alt="' . esc_attr($f['alt']) . '" style="object-position:' . $f['pos'] . '" fetchpriority="high" decoding="async"></div>'
            . '<div class="gl-cut" aria-hidden="true"><svg viewBox="0 0 105 100" preserveAspectRatio="none"><line x1="105" y1="0" x2="0" y2="100" stroke="#FF2400" stroke-width="4" vector-effect="non-scaling-stroke"/></svg></div>';
        return '<section class="glowka zf" data-mob-od>' . $fot . '<div class="wrap">' . $tx . '</div></section>';
    }
    return '<section class="glowka" data-mob-od><svg class="duzy-mk" viewBox="0 0 110 200" aria-hidden="true"><use href="#mk"/></svg><div class="wrap">' . $tx . '</div></section>';
}

function iv_bok($slug, $lista, $tytul) {
    $links = '';
    foreach ($lista as $x) $links .= '<a href="' . esc_url(iv_u($x[0] . '/')) . '"' . ($x[0] === $slug ? ' aria-current="page"' : '') . '>' . iv_e($x[1]) . '</a>';
    return '<aside class="bok">
  <h2>' . $tytul . '</h2>
  <nav aria-label="' . $tytul . '">' . $links . '</nav>
  <div class="kbox">
    <p>Zapytaj o wycenę instalacji albo serwis - odpowiemy z wstępną mocą i orientacyjną ceną.</p>
    <a class="t" href="' . esc_attr(iv_tel_href()) . '">' . iv_e(iv_dane('tel')) . '</a>
    <a class="m" href="mailto:' . esc_attr(iv_dane('email')) . '">' . iv_e(iv_dane('email')) . '</a>
    <a class="btn" href="' . esc_url(iv_u('kontakt/#formularz')) . '">' . IV_MK . 'Napisz do nas</a>
  </div>
</aside>';
}

function iv_dalej($slug, $lista) {
    $s = array_column($lista, 0);
    $i = array_search($slug, $s, true);
    if ($i === false) return '';
    $h = '<nav class="dalej" aria-label="Kolejne artykuły">';
    if ($i > 0) $h .= '<a href="' . esc_url(iv_u($lista[$i - 1][0] . '/')) . '"><span>Poprzedni</span>' . iv_e($lista[$i - 1][1]) . '</a>';
    if ($i < count($lista) - 1) $h .= '<a class="nast" href="' . esc_url(iv_u($lista[$i + 1][0] . '/')) . '"><span>Następny</span>' . iv_e($lista[$i + 1][1]) . '</a>';
    return $h . '</nav>';
}

function iv_lista_art() {
    foreach (iv_lista('foto') as $x) echo '<li><a href="' . esc_url(iv_u($x[0] . '/')) . '">' . iv_e($x[1]) . '</a></li>';
}

function iv_kontakt_dl($krotkie = false) {
    $adr = array_map(fn($a) => iv_e($krotkie ? iv_adres_krotki($a) : $a), iv_adresy());
    echo '<dl>
        <div><dt>Telefon</dt><dd><a href="' . esc_attr(iv_tel_href()) . '">' . iv_e(iv_dane('tel')) . '</a></dd></div>
        <div><dt>E-mail</dt><dd><a href="mailto:' . esc_attr(iv_dane('email')) . '">' . iv_e(iv_dane('email')) . '</a></dd></div>
        <div><dt>Adresy</dt><dd>' . implode('<br>', $adr) . '</dd></div>
      </dl>';
}

function iv_opinie_link() {
    $u = iv_dane('opinie');
    if ($u) echo '<p><a class="link-j" href="' . esc_url($u) . '" target="_blank" rel="noopener">Opinie klientów w Google</a></p>';
}

function iv_mapa() {
    $adr = iv_adresy();
    if (!$adr) return;
    $q1 = iv_adres_krotki($adr[0]);
    $u = fn($q) => 'https://www.google.com/maps/search/?api=1&amp;query=' . str_replace([' ', ','], ['+', '%2C'], $q);
    $przyc = '<button class="btn" type="button" data-mapa="' . esc_attr($q1) . '">' . IV_MK . 'Pokaż mapę: ' . iv_e(iv_miejscowosc($adr[0])) . '</button>';
    $trasy = '<a href="' . $u($q1) . '" target="_blank" rel="noopener">Trasa: ' . iv_e($q1) . '</a>';
    if (isset($adr[1])) {
        $przyc .= '<button class="btn jasny" type="button" data-mapa="' . esc_attr($adr[1]) . '">' . iv_e(iv_miejscowosc($adr[1])) . '</button>';
        $trasy .= '<a href="' . $u($adr[1]) . '" target="_blank" rel="noopener">Trasa: ' . iv_e(iv_adres_krotki($adr[1])) . '</a>';
    }
    echo '<div class="mapa" id="mapa">
        <svg class="duzy-mk" viewBox="0 0 110 200" aria-hidden="true"><use href="#mk"/></svg>
        <div class="mapa-z">
          <b>Mapa dojazdu</b>
          <div class="przyc">' . $przyc . '</div>
          <p class="uwaga">Mapa Google wczytuje się po zgodzie na pliki cookies map lub po kliknięciu. <a href="' . esc_url(iv_u('polityka-prywatnosci/#cookies')) . '">Polityka prywatności</a></p>
        </div>
      </div>
      <p class="mapa-l">' . $trasy . '</p>';
}

function iv_formularz() {
    // akcja w adresie (pole o nazwie "action" przeslanialoby form.action w skrypcie)
    echo '<form id="form" action="' . esc_url(admin_url('admin-ajax.php?action=iv_formularz')) . '" method="post" novalidate>
        <label>Imię i nazwisko<input name="n" autocomplete="name" required></label>
        <label>Telefon lub e-mail<input name="k" autocomplete="email" required></label>
        <label class="cala">Czego dotyczy zapytanie
          <select name="t">' . implode('', array_map(fn($t) => '<option>' . iv_e($t) . '</option>', IV_TEMATY)) . '</select></label>
        <label class="cala">Wiadomość<textarea name="w" placeholder="Np. dach dwuspadowy, dachówka, ok. 4000 kWh rocznie"></textarea></label>
        <p class="zgoda cala">Dane z formularza wykorzystamy tylko do odpowiedzi na zapytanie. Więcej w <a href="' . esc_url(iv_u('polityka-prywatnosci/')) . '">polityce prywatności</a>.</p>
        <label class="hp" aria-hidden="true">Strona www<input name="www" tabindex="-1" autocomplete="off"></label>
        <input type="hidden" name="ts" value="">
        <button class="btn cala" type="submit">' . IV_MK . 'Wyślij zapytanie</button>
        <p class="uwaga cala" id="komunikat" role="status" aria-live="polite"></p>
      </form>';
}

function iv_stopka() {
    $fl = ''; foreach (array_slice(iv_lista('foto'), 0, 5) as $x) $fl .= '<a href="' . esc_url(iv_u($x[0] . '/')) . '">' . iv_e($x[1]) . '</a>';
    $ol = ''; foreach (array_slice(iv_lista('oferta'), 0, 4) as $x) $ol .= '<a href="' . esc_url(iv_u($x[0] . '/')) . '">' . iv_e($x[1]) . '</a>';
    $adr = implode('<br>', array_map(fn($a) => iv_e(iv_adres_krotki($a)), iv_adresy()));
    return '<footer class="stopka">
  <div class="wrap">
    <div><img src="/img/logo-biale.webp" srcset="/img/logo-biale-150.webp 150w, /img/logo-biale-300.webp 300w, /img/logo-biale.webp 600w" sizes="150px" width="600" height="237" alt="inVolte" loading="lazy"><p style="margin-top:14px;max-width:34ch">' . iv_e(iv_dane('opis_stopka')) . '</p>
      <p style="margin-top:14px"><a href="' . esc_attr(iv_tel_href()) . '">' . iv_e(iv_dane('tel')) . '</a><br><a href="mailto:' . esc_attr(iv_dane('email')) . '">' . iv_e(iv_dane('email')) . '</a></p></div>
    <nav aria-label="Fotowoltaika"><b>Fotowoltaika</b>' . $fl . '<a href="' . esc_url(iv_u('pytania-i-odpowiedzi/')) . '">' . iv_e(iv_krotki('pytania-i-odpowiedzi', 'Pytania i odpowiedzi')) . '</a></nav>
    <nav aria-label="Oferta"><b>Oferta</b>' . $ol . '<a href="' . esc_url(iv_u('cennik/')) . '">Cennik</a></nav>
    <nav aria-label="Firma"><b>Firma</b><a href="' . esc_url(iv_u('o-nas/')) . '">O nas</a><a href="' . esc_url(iv_u('nasze-realizacje/')) . '">Nasze realizacje</a><a href="' . esc_url(iv_u('kontakt/')) . '">Kontakt</a><a href="' . esc_url(iv_u('polityka-prywatnosci/')) . '">Polityka prywatności</a></nav>
    <div><b>Adresy</b>' . $adr . '</div>
    <div class="dol"><span>© ' . wp_date('Y') . ' inVolte <button class="c-ust" type="button" data-c="otworz">Ustawienia cookies</button></span><span class="wb">' . IV_BADGE . '</span></div>
  </div>
</footer>';
}

function iv_ciastka() {
    return '<div class="ciastka" id="ciastka" role="dialog" aria-label="Pliki cookies" hidden>
  <p>Strona używa tylko niezbędnych plików cookies. Za Twoją zgodą wczytamy też mapę Google, która zapisuje własne pliki cookies. <a href="' . esc_url(iv_u('polityka-prywatnosci/#cookies')) . '">Polityka prywatności</a></p>
  <div class="c-kat" id="c-kat" hidden>
    <label><input type="checkbox" checked disabled> Niezbędne - zapamiętanie wyboru</label>
    <label><input type="checkbox" id="c-mapy"> Mapy Google - mapa dojazdu</label>
  </div>
  <div class="c-p"><button class="btn" type="button" data-c="wszystkie">Akceptuję wszystkie</button><button class="btn jasny" type="button" data-c="niezbedne">Tylko niezbędne</button><button class="c-lnk" type="button" data-c="ustawienia">Ustawienia</button><button class="btn jasny" type="button" data-c="zapisz" hidden>Zapisz wybór</button></div>
</div>
<script>(function(){var z=null;try{z=JSON.parse(localStorage.getItem(\'involte-zgoda\'))}catch(e){}if(!z||Date.now()-z.t>31536000000)document.getElementById(\'ciastka\').hidden=false})();</script>';
}

/* ---------- SEO: tytul, opis, dane strukturalne ---------- */
function iv_kontekst() {
    static $k = null;
    if ($k !== null) return $k;
    $k = ['title' => 'inVolte', 'desc' => '', 'canon' => '', 'ld' => [], 'slug' => '', 'mob' => iv_u('kontakt/#formularz'), 'noindex' => !get_option('blog_public'), 'pre' => ''];
    $dom = trailingslashit(home_url());
    if (is_front_page()) {
        $id = (int)get_option('page_on_front');
        $m = iv_meta($id);
        $k['title'] = $m['seo'] ?: get_bloginfo('name');
        $k['desc'] = $m['opis'];
        $k['canon'] = $dom;
        $k['ld'] = [iv_ld_firma(), ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'inVolte', 'alternateName' => ['inVolte.pl', 'inVolte Arkadiusz Witt'], 'url' => $dom, 'inLanguage' => 'pl-PL']];
        $k['mob'] = '#kontakt';
        $k['pre'] = '<link rel="preload" as="image" href="/img/dom-mob-480.webp" imagesrcset="/img/dom-mob-480.webp 480w, /img/dom-mob-640.webp 640w, /img/dom-mob-780.webp 780w" imagesizes="100vw" media="(max-width:760px)" fetchpriority="high">' . "\n"
            . '<link rel="preload" as="image" href="/img/dom-1614.webp" imagesrcset="/img/dom-1024.webp 1024w, /img/dom-1360.webp 1360w, /img/dom-1614.webp 1614w" imagesizes="100vw" media="(min-width:761px)" fetchpriority="high">' . "\n";
    } elseif (is_page()) {
        $id = get_queried_object_id();
        $m = iv_meta($id);
        $k['slug'] = get_post_field('post_name', $id);
        $k['title'] = $m['seo'] ?: get_the_title($id) . ' | inVolte';
        $k['desc'] = $m['opis'];
        $k['canon'] = get_permalink($id);
        $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Strona główna', 'item' => $dom]];
        foreach (iv_okr($id) as $x) if ($x[1]) $items[] = ['@type' => 'ListItem', 'position' => count($items) + 1, 'name' => $x[0], 'item' => get_permalink($id)];
        $k['ld'][] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
        if ($k['slug'] === 'kontakt') { $k['ld'][] = iv_ld_firma(); $k['mob'] = '#formularz'; }
    } elseif (is_404()) {
        $k['title'] = 'Nie znaleziono strony | inVolte';
        $k['desc'] = 'Strona nie istnieje.';
        $k['slug'] = '404';
        $k['noindex'] = true;
    }
    return $k;
}
function iv_ld_firma() {
    $dom = trailingslashit(home_url());
    $adr = [];
    foreach (iv_adresy() as $a) {
        $p = array_map('trim', explode(',', $a, 2));
        $x = ['@type' => 'PostalAddress', 'streetAddress' => $p[0]];
        $rest = $p[1] ?? '';
        if (preg_match('~^(\d{2}-\d{3})\s+(.+)$~', $rest, $mm)) { $x['postalCode'] = $mm[1]; $rest = $mm[2]; }
        $x['addressLocality'] = $rest;
        $x['addressCountry'] = 'PL';
        $adr[] = $x;
    }
    $f = ['@context' => 'https://schema.org', '@type' => 'HomeAndConstructionBusiness', 'name' => iv_dane('firma'), 'url' => $dom,
        'logo' => ['@type' => 'ImageObject', 'url' => $dom . 'img/ikona-512.png', 'width' => 512, 'height' => 512], 'image' => $dom . 'img/og.jpg',
        'telephone' => preg_replace('~^tel:~', '', iv_tel_href()), 'email' => iv_dane('email'), 'address' => $adr, 'areaServed' => 'Wielkopolska',
        'description' => 'Instalacje fotowoltaiczne dla domów i firm: projekt, dokumentacja, montaż i serwis.'];
    if (iv_dane('opinie')) $f['sameAs'] = [iv_dane('opinie')];
    return $f;
}

// mapa strony /sitemap.xml (tylko opublikowane podstrony)
add_action('init', function () {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?iv_sitemap=1', 'top');
});
add_filter('query_vars', function ($v) { $v[] = 'iv_sitemap'; return $v; });
add_filter('redirect_canonical', function ($r) { return get_query_var('iv_sitemap') ? false : $r; });
add_action('template_redirect', function () {
    if (!get_query_var('iv_sitemap')) return;
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    $front = (int)get_option('page_on_front');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $p = get_post($front);
    echo '  <url><loc>' . esc_url(trailingslashit(home_url())) . '</loc><lastmod>' . get_post_modified_time('Y-m-d', true, $p) . "</lastmod></url>\n";
    foreach (get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC', 'exclude' => [$front], 'has_password' => false]) as $p) {
        if (get_post_meta($p->ID, '_iv_noindex', true)) continue;
        echo '  <url><loc>' . esc_url(get_permalink($p)) . '</loc><lastmod>' . get_post_modified_time('Y-m-d', true, $p) . "</lastmod></url>\n";
    }
    echo "</urlset>\n";
    exit;
}, 0);
