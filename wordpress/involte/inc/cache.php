<?php
// pamiec podreczna stron (pliki HTML w wp-content/cache/iv), czyszczona automatycznie po kazdym zapisie w panelu
defined('ABSPATH') || exit;

function iv_cache_dir() { return WP_CONTENT_DIR . '/cache/iv'; }
function iv_cache_czysc() {
    $d = iv_cache_dir();
    if (!is_dir($d)) return;
    foreach ((array)glob($d . '/*.html') as $f) @unlink($f);
}
add_action('template_redirect', function () {
    if (!defined('IV_CACHE_PLIK') || is_user_logged_in() || is_404() || is_preview() || is_feed() || get_query_var('iv_sitemap') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return;
    ob_start(function ($html) {
        if (http_response_code() === 200 && stripos($html, '</html>') !== false) {
            {
                $d = iv_cache_dir();
                if (!is_dir($d)) { wp_mkdir_p($d); @file_put_contents($d . '/index.html', ''); }
                @file_put_contents(IV_CACHE_PLIK . '.tmp', $html . "\n<!-- c " . gmdate('c') . ' -->', LOCK_EX);
                @rename(IV_CACHE_PLIK . '.tmp', IV_CACHE_PLIK);
            }
        }
        return $html;
    });
}, 99);
add_action('send_headers', function () {
    if (!is_admin()) header('Cache-Control: no-cache, must-revalidate');
});
foreach (['save_post', 'deleted_post', 'trashed_post', 'edit_attachment', 'delete_attachment', 'switch_theme', 'wp_update_nav_menu', 'customize_save_after', 'upgrader_process_complete'] as $h) add_action($h, 'iv_cache_czysc');
foreach (['iv_dane', 'iv_home', 'blogname', 'page_on_front', 'permalink_structure', 'blog_public', 'home', 'siteurl'] as $o) {
    add_action('update_option_' . $o, 'iv_cache_czysc');
    add_action('add_option_' . $o, 'iv_cache_czysc');
}
add_action('admin_bar_menu', function ($b) {
    if (!current_user_can('edit_pages')) return;
    $b->add_node(['id' => 'iv-cache', 'title' => 'Odśwież stronę (wyczyść pamięć)', 'href' => wp_nonce_url(admin_url('admin-post.php?action=iv_cache'), 'iv_cache')]);
}, 100);
add_action('admin_post_iv_cache', function () {
    check_admin_referer('iv_cache');
    if (current_user_can('edit_pages')) iv_cache_czysc();
    wp_safe_redirect(wp_get_referer() ?: admin_url());
    exit;
});
