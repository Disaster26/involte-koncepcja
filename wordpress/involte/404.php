<?php
defined('ABSPATH') || exit;
status_header(404);
get_header();
echo iv_glowka([['Nie znaleziono strony', null]], 'Nie znaleziono strony', 'Ten adres nie istnieje albo strona została przeniesiona.', '');
?>

<div class="wrap uklad szer"><article class="tresc"><p>Przejdź na <a href="<?php echo esc_url(iv_u()); ?>">stronę główną</a>, zobacz <a href="<?php echo esc_url(iv_u('mikro-i-male-instalacje/')); ?>">instalacje dla domów</a>, <a href="<?php echo esc_url(iv_u('cennik/')); ?>">cennik</a> albo <a href="<?php echo esc_url(iv_u('kontakt/')); ?>">napisz do nas</a>. Telefon: <a href="<?php echo esc_attr(iv_tel_href()); ?>"><?php echo esc_html(iv_dane('tel')); ?></a>.</p></article></div>
<?php get_footer();
