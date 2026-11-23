<?php
// panel administracyjny: dane firmy, teksty strony glownej, ustawienia podstron
defined('ABSPATH') || exit;

const IV_TEMATY = ['Instalacja na domu', 'Instalacja dla firmy lub farma', 'Projekt i dokumentacja', 'Serwis instalacji'];

add_action('admin_menu', function () {
    add_menu_page('inVolte - dane firmy', 'inVolte', 'edit_pages', 'iv-dane', 'iv_strona_dane', 'dashicons-lightbulb', 3);
    add_submenu_page('iv-dane', 'Dane firmy', 'Dane firmy', 'edit_pages', 'iv-dane', 'iv_strona_dane');
    add_submenu_page('iv-dane', 'Strona główna - teksty', 'Strona główna - teksty', 'edit_pages', 'iv-home', 'iv_strona_home');
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
});
add_action('admin_bar_menu', function ($b) { $b->remove_node('comments'); $b->remove_node('new-post'); $b->remove_node('wp-logo'); }, 999);

add_action('admin_init', function () {
    register_setting('iv_dane', 'iv_dane', ['type' => 'array', 'sanitize_callback' => function ($v) {
        $o = [];
        foreach (iv_dane_domyslne() as $k => $d) {
            $x = isset($v[$k]) ? trim(wp_unslash($v[$k])) : '';
            if (in_array($k, ['email', 'email_form'], true)) $x = sanitize_email($x);
            elseif ($k === 'opinie') $x = esc_url_raw($x);
            else $x = sanitize_text_field($x);
            $o[$k] = $x;
        }
        return $o;
    }]);
    register_setting('iv_home', 'iv_home', ['type' => 'array', 'sanitize_callback' => function ($v) {
        $o = [];
        foreach (IV_POLA as $k => $p) {
            $x = isset($v[$k]) ? trim(wp_kses_post(wp_unslash($v[$k]))) : '';
            if ($x !== '' && $x !== $p[2]) $o[$k] = $x;
        }
        return $o;
    }]);
});

function iv_strona_dane() {
    $d = iv_dane();
    $pola = ['firma' => ['Nazwa firmy (do danych dla Google)', ''], 'tel' => ['Telefon', 'Wyświetlany w nagłówku, stopce i kontakcie, np. 570 083 207'],
        'email' => ['E-mail kontaktowy', 'Wyświetlany na stronie'], 'email_form' => ['E-mail do zapytań z formularza', 'Na ten adres przychodzą wiadomości z formularza'],
        'adres1' => ['Adres główny', 'Np. Gwiaździsta 57, Swarzędz - pierwszy na mapie'], 'adres2' => ['Drugi adres', 'Np. Akacjowa 8, 63-012 Dominowo; puste = brak'],
        'nip' => ['NIP', ''], 'opinie' => ['Link do opinii Google', 'Wklej link z Map Google (Udostępnij). Pojawi się na stronie głównej, w realizacjach i w kontakcie.'],
        'opis_stopka' => ['Opis w stopce', '']];
    echo '<div class="wrap"><h1>Dane firmy</h1><p>Zmiany są widoczne na całej stronie od razu po zapisaniu.</p><form method="post" action="options.php">';
    settings_fields('iv_dane');
    echo '<table class="form-table" role="presentation">';
    foreach ($pola as $k => $p) {
        echo '<tr><th scope="row"><label for="iv-' . $k . '">' . esc_html($p[0]) . '</label></th><td><input class="regular-text" style="width:100%;max-width:560px" id="iv-' . $k . '" name="iv_dane[' . $k . ']" value="' . esc_attr($d[$k]) . '">'
            . ($p[1] ? '<p class="description">' . esc_html($p[1]) . '</p>' : '') . '</td></tr>';
    }
    echo '</table>';
    submit_button('Zapisz dane');
    echo '</form></div>';
}

function iv_strona_home() {
    $o = (array)get_option('iv_home', []);
    echo '<div class="wrap"><h1>Strona główna - teksty</h1><p>Teksty kolejnych sekcji strony głównej, od góry. Puste pole przywraca tekst domyślny. Zmiany są widoczne od razu po zapisaniu.</p>'
        . '<style>.iv-s{background:#fff;border:1px solid #dcdcde;padding:8px 20px 16px;margin:16px 0;max-width:900px}.iv-s h2{margin:12px 0}.iv-p{margin:10px 0}.iv-p label{display:block;font-weight:600;margin-bottom:4px;color:#50575e}.iv-p textarea{width:100%}</style>'
        . '<form method="post" action="options.php">';
    settings_fields('iv_home');
    $nazwy = ['h1' => 'Główny nagłówek', 'h2' => 'Nagłówek sekcji', 'h3' => 'Podtytuł', 'p' => 'Tekst', 'li' => 'Punkt listy', 'summary' => 'Pytanie',
        'figcaption' => 'Podpis zdjęcia', 'dt' => 'Etykieta', 'footer' => 'Podpis cytatu', 'przycisk' => 'Napis na przycisku lub odnośniku', 'cytat' => 'Cytat'];
    $sek = '';
    foreach (IV_POLA as $k => $p) {
        if ($p[0] !== $sek) {
            if ($sek !== '') echo '</div>';
            $sek = $p[0];
            echo '<div class="iv-s"><h2>' . esc_html(IV_SEKCJE[$sek] ?? $sek) . '</h2>';
        }
        $v = $o[$k] ?? $p[2];
        $wys = strlen($p[2]) > 90 ? 3 : 1;
        $etyk = $p[1] === 'p' && $sek === 'pytania' ? 'Odpowiedź' : ($nazwy[$p[1]] ?? 'Tekst');
        echo '<div class="iv-p"><label for="iv-' . esc_attr($k) . '">' . esc_html($etyk) . '</label><textarea id="iv-' . esc_attr($k) . '" name="iv_home[' . esc_attr($k) . ']" rows="' . $wys . '">' . esc_textarea($v) . '</textarea></div>';
    }
    echo '</div>';
    submit_button('Zapisz teksty');
    echo '</form></div>';
}

/* ---------- ustawienia podstrony (Google, naglowek, boczna kolumna) ---------- */
add_action('add_meta_boxes_page', function () {
    add_meta_box('iv-strona', 'Wygląd i opis w Google', 'iv_metabox_strona', 'page', 'normal', 'high');
});
function iv_metabox_strona($post) {
    wp_nonce_field('iv_strona', 'iv_strona_n');
    $m = iv_meta($post->ID);
    $f = fn($k, $l, $v, $opis = '', $dl = false) => '<p><label for="iv-' . $k . '"><b>' . esc_html($l) . '</b></label><br>'
        . ($dl ? '<textarea id="iv-' . $k . '" name="iv_' . $k . '" rows="2" style="width:100%">' . esc_textarea($v) . '</textarea>' : '<input id="iv-' . $k . '" name="iv_' . $k . '" value="' . esc_attr($v) . '" style="width:100%">')
        . ($opis ? '<span class="description">' . esc_html($opis) . '</span>' : '') . '</p>';
    echo $f('seo', 'Tytuł w Google', $m['seo'], 'Najlepiej do 60 znaków, z usługą i miejscowością.');
    echo $f('opis', 'Opis w Google', $m['opis'], 'Najlepiej 120-160 znaków.', true);
    if ((int)get_option('page_on_front') === $post->ID) { echo '<p class="description">Teksty strony głównej edytuje się w menu inVolte &gt; Strona główna - teksty.</p>'; return; }
    echo $f('lead', 'Wstęp pod tytułem', $m['lead'], 'Krótkie zdanie pod nagłówkiem strony.', true);
    echo $f('krotki', 'Krótka nazwa (menu i okruszki)', $m['krotki']);
    echo '<p><label for="iv-foto"><b>Zdjęcie w nagłówku</b></label><br><select id="iv-foto" name="iv_foto">';
    foreach (['' => 'bez zdjęcia (znak inVolte)', 'dom' => 'dom z instalacją', 'farma' => 'farma fotowoltaiczna', 'projekt' => 'moduły o zachodzie słońca', 'serwis' => 'serwisant przy modułach'] as $k => $l)
        echo '<option value="' . $k . '"' . selected($m['foto'], $k, false) . '>' . esc_html($l) . '</option>';
    echo '</select></p><p><label for="iv-grupa"><b>Boczna kolumna</b></label><br><select id="iv-grupa" name="iv_grupa">';
    $g = $m['grupa'] ?: ($m['szer'] ? 'szer' : '');
    foreach (['' => 'Oferta (lista usług i kontakt)', 'foto' => 'Fotowoltaika - artykuły', 'oferta' => 'Oferta - podstrony oferty', 'szer' => 'brak, treść na całą szerokość'] as $k => $l)
        echo '<option value="' . $k . '"' . selected($g, $k, false) . '>' . esc_html($l) . '</option>';
    echo '</select></p>';
}
add_action('save_post_page', function ($id) {
    if (!isset($_POST['iv_strona_n']) || !wp_verify_nonce($_POST['iv_strona_n'], 'iv_strona') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_page', $id)) return;
    foreach (['seo', 'krotki'] as $k) if (isset($_POST['iv_' . $k])) update_post_meta($id, '_iv_' . $k, sanitize_text_field(wp_unslash($_POST['iv_' . $k])));
    foreach (['opis', 'lead'] as $k) if (isset($_POST['iv_' . $k])) update_post_meta($id, '_iv_' . $k, sanitize_textarea_field(wp_unslash($_POST['iv_' . $k])));
    if (isset($_POST['iv_foto'])) update_post_meta($id, '_iv_foto', in_array($_POST['iv_foto'], ['dom', 'farma', 'projekt', 'serwis'], true) ? $_POST['iv_foto'] : '');
    if (isset($_POST['iv_grupa'])) {
        $g = sanitize_key($_POST['iv_grupa']);
        update_post_meta($id, '_iv_grupa', in_array($g, ['foto', 'oferta'], true) ? $g : '');
        update_post_meta($id, '_iv_szer', $g === 'szer' ? 1 : 0);
    }
});

/* ---------- pulpit: skrot dla klienta ---------- */
add_action('wp_dashboard_setup', function () {
    foreach (['dashboard_primary', 'dashboard_quick_press', 'dashboard_activity', 'dashboard_site_health', 'dashboard_right_now'] as $w) { remove_meta_box($w, 'dashboard', 'side'); remove_meta_box($w, 'dashboard', 'normal'); }
    remove_action('welcome_panel', 'wp_welcome_panel');
    wp_add_dashboard_widget('iv_start', 'Strona inVolte - szybki start', function () {
        echo '<ul style="list-style:disc;padding-left:18px;line-height:1.7">'
            . '<li><a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">Strony</a> - teksty podstron (O nas, artykuły, oferta, cennik, polityka).</li>'
            . '<li><a href="' . esc_url(admin_url('post-new.php?post_type=realizacja')) . '">Dodaj realizację</a> - zdjęcia instalacji z opisem, miejscowością i mocą.</li>'
            . '<li><a href="' . esc_url(admin_url('admin.php?page=iv-home')) . '">Strona główna - teksty</a> - nagłówki i opisy sekcji strony głównej.</li>'
            . '<li><a href="' . esc_url(admin_url('admin.php?page=iv-dane')) . '">Dane firmy</a> - telefon, e-mail, adresy, link do opinii Google.</li>'
            . '</ul><p>Zmiany są widoczne na stronie od razu po zapisaniu. W razie pytań: Wylinka, tel. 888 316 564.</p>';
    });
});
add_filter('admin_footer_text', fn() => 'Strona inVolte - wykonanie: <a href="https://wylinka.pl" target="_blank" rel="noopener">Wylinka</a>');
