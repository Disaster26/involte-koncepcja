<?php
// realizacje: wpisy ze zdjeciami w panelu, galeria na /nasze-realizacje/ i wybor na stronie glownej
defined('ABSPATH') || exit;

add_action('init', function () {
    register_post_type('realizacja', [
        'labels' => ['name' => 'Realizacje', 'singular_name' => 'Realizacja', 'menu_name' => 'Realizacje', 'add_new' => 'Dodaj realizację', 'add_new_item' => 'Dodaj realizację',
            'edit_item' => 'Edytuj realizację', 'new_item' => 'Nowa realizacja', 'all_items' => 'Wszystkie realizacje', 'search_items' => 'Szukaj realizacji',
            'not_found' => 'Brak realizacji', 'featured_image' => 'Zdjęcie główne', 'set_featured_image' => 'Wybierz zdjęcie główne',
            'remove_featured_image' => 'Usuń zdjęcie główne', 'use_featured_image' => 'Użyj jako zdjęcie główne'],
        'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'show_in_rest' => false, 'menu_position' => 4, 'menu_icon' => 'dashicons-format-gallery',
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'], 'has_archive' => false, 'rewrite' => false, 'query_var' => false,
    ]);
    add_shortcode('galeria_realizacji', 'iv_galeria_realizacji');
    add_shortcode('opinie_google', function () { ob_start(); iv_opinie_link(); return ob_get_clean(); });
});

add_filter('enter_title_here', fn($t, $p) => $p->post_type === 'realizacja' ? 'Np. Instalacja na domu jednorodzinnym' : $t, 10, 2);
add_action('edit_form_after_title', function ($p) {
    if ($p->post_type !== 'realizacja') return;
    echo '<div class="notice notice-info inline" style="margin:12px 0"><p><b>Jak dodać zdjęcia:</b> po prawej "Wybierz zdjęcie główne", a pozostałe zdjęcia przyciskiem "Dodaj medium" nad polem opisu (można zaznaczyć kilka naraz). Wystarczy je wgrać - nie trzeba wstawiać do opisu. W opisie wpisz kilka zdań o instalacji.</p></div>';
});

add_action('add_meta_boxes_realizacja', function () {
    add_meta_box('iv-real', 'Miejscowość i moc', function ($p) {
        wp_nonce_field('iv_real', 'iv_real_n');
        echo '<p><label for="iv-miejsce"><b>Miejscowość</b></label><br><input id="iv-miejsce" name="iv_miejsce" style="width:100%" value="' . esc_attr(get_post_meta($p->ID, '_iv_miejsce', true)) . '" placeholder="np. Swarzędz"></p>'
            . '<p><label for="iv-moc"><b>Moc instalacji</b></label><br><input id="iv-moc" name="iv_moc" style="width:100%" value="' . esc_attr(get_post_meta($p->ID, '_iv_moc', true)) . '" placeholder="np. 9,9 kWp"></p>';
    }, 'realizacja', 'side', 'high');
});
add_action('save_post_realizacja', function ($id) {
    if (!isset($_POST['iv_real_n']) || !wp_verify_nonce($_POST['iv_real_n'], 'iv_real') || !current_user_can('edit_post', $id)) return;
    update_post_meta($id, '_iv_miejsce', sanitize_text_field(wp_unslash($_POST['iv_miejsce'] ?? '')));
    update_post_meta($id, '_iv_moc', sanitize_text_field(wp_unslash($_POST['iv_moc'] ?? '')));
});
add_filter('manage_realizacja_posts_columns', function ($c) { return ['cb' => $c['cb'], 'iv_fot' => 'Zdjęcie', 'title' => 'Realizacja', 'iv_inf' => 'Miejscowość, moc', 'iv_ile' => 'Zdjęć', 'date' => $c['date']]; });
add_action('manage_realizacja_posts_custom_column', function ($c, $id) {
    $z = iv_zdjecia_realizacji($id);
    if ($c === 'iv_fot' && $z) echo wp_get_attachment_image($z[0], [72, 54]);
    if ($c === 'iv_inf') echo esc_html(implode(', ', array_filter([get_post_meta($id, '_iv_miejsce', true), get_post_meta($id, '_iv_moc', true)])));
    if ($c === 'iv_ile') echo count($z);
}, 10, 2);

// zdjecia realizacji: glowne + wgrane przy edycji + wstawione do opisu
function iv_zdjecia_realizacji($id) {
    $ids = [];
    if ($t = get_post_thumbnail_id($id)) $ids[] = (int)$t;
    foreach (get_children(['post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order date', 'order' => 'ASC', 'fields' => 'ids']) as $a) $ids[] = (int)$a;
    $c = get_post_field('post_content', $id);
    if (preg_match_all('~wp-image-(\d+)~', $c, $m)) foreach ($m[1] as $a) $ids[] = (int)$a;
    if (preg_match_all('~ids="([\d,\s]+)"~', $c, $m)) foreach ($m[1] as $l) foreach (explode(',', $l) as $a) $ids[] = (int)trim($a);
    return array_values(array_unique(array_filter($ids, fn($a) => $a && wp_attachment_is_image($a))));
}
function iv_realizacje($ile = -1) {
    return get_posts(['post_type' => 'realizacja', 'post_status' => 'publish', 'numberposts' => $ile, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
}
function iv_img_real($a, $alt, $sizes) {
    $duzy = wp_get_attachment_image_url($a, 'large');
    return '<a class="pow" href="' . esc_url($duzy) . '">' . wp_get_attachment_image($a, 'medium_large', false, ['loading' => 'lazy', 'decoding' => 'async', 'alt' => $alt, 'sizes' => $sizes]) . '</a>';
}
function iv_opis_real($p) {
    return implode(', ', array_filter([get_post_meta($p->ID, '_iv_miejsce', true), get_post_meta($p->ID, '_iv_moc', true)]));
}
function iv_galeria_realizacji() {
    $r = iv_realizacje();
    if (!$r) return '';
    $h = '<h2>Galeria realizacji</h2>';
    foreach ($r as $p) {
        $z = iv_zdjecia_realizacji($p->ID);
        if (!$z) continue;
        $inf = iv_opis_real($p);
        $alt = get_the_title($p) . ($inf ? ' - ' . $inf : '');
        $op = trim(wp_strip_all_tags(preg_replace('~\[gallery[^\]]*\]~', '', get_post_field('post_content', $p->ID))));
        $h .= '<h3>' . esc_html(get_the_title($p)) . '</h3>' . ($inf || $op ? '<p>' . esc_html(trim($inf . ($inf && $op ? '. ' : '') . $op)) . '</p>' : '')
            . '<div class="galeria">' . implode('', array_map(fn($a) => iv_img_real($a, $alt, '(max-width:760px) 50vw, 300px'), $z)) . '</div>';
    }
    return $h;
}
function iv_realizacje_glowna() {
    $r = iv_realizacje(6);
    $kafle = '';
    foreach ($r as $p) {
        $z = iv_zdjecia_realizacji($p->ID);
        if (!$z) continue;
        $inf = iv_opis_real($p);
        $kafle .= iv_img_real($z[0], get_the_title($p) . ($inf ? ' - ' . $inf : ''), '(max-width:760px) 50vw, 380px');
    }
    if ($kafle === '') return;
    echo '<section class="sek" id="realizacje">
  <div class="wrap">
    <div class="glowa">
      <h2>Nasze realizacje</h2>
      <p>Wybrane instalacje wykonane przez inVolte.</p>
    </div>
    <div class="galeria">' . $kafle . '</div>
    <p style="margin-top:24px"><a class="link-j" href="' . esc_url(iv_u('nasze-realizacje/')) . '">Wszystkie realizacje</a></p>';
    iv_opinie_link();
    echo '
  </div>
</section>';
}
add_filter('post_updated_messages', function ($m) {
    $m['realizacja'] = [1 => 'Realizacja zapisana - jest już na stronie.', 4 => 'Realizacja zapisana.', 6 => 'Realizacja opublikowana - jest już na stronie.', 7 => 'Realizacja zapisana.', 10 => 'Szkic realizacji zapisany.'];
    return $m;
});
