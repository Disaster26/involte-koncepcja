<?php
// podstrona: naglowek z okruszkami, tresc z edytora, boczna kolumna jak w projekcie
defined('ABSPATH') || exit;
get_header();
the_post();
$id = get_the_ID();
$slug = get_post_field('post_name', $id);
$m = iv_meta($id);
if ($slug === 'kontakt') {
    echo iv_glowka(iv_okr($id), get_the_title(), $m['lead'], '');
    ?>
<section class="kont-p" id="kontakt">
  <div class="wrap kont">
    <div class="dk">
      <?php the_content(); ?>
      <?php iv_kontakt_dl(); iv_opinie_link(); ?>
    </div>
    <div id="formularz">
      <h2 style="font-size:clamp(24px,2vw,30px);margin-bottom:20px">Formularz zapytania</h2>
      <?php iv_formularz(); ?>
      <?php iv_mapa(); ?>
    </div>
  </div>
</section>
<?php
} else {
    echo iv_glowka(iv_okr($id), get_the_title(), $m['lead'], $m['foto']);
    $tresc = apply_filters('the_content', get_the_content());
    $boczna = '';
    if ($m['grupa'] === 'foto' || $m['grupa'] === 'oferta') {
        $lista = iv_lista($m['grupa']);
        $tresc .= iv_dalej($slug, $lista);
        $boczna = iv_bok($slug, $lista, $m['grupa'] === 'foto' ? 'Fotowoltaika' : 'Oferta');
    } elseif (!$m['szer']) {
        $boczna = iv_bok($slug, iv_lista('oferta'), 'Oferta');
    }
    echo "\n" . '<div class="wrap uklad' . ($boczna ? '' : ' szer') . '"><article class="tresc">' . $tresc . '</article>' . $boczna . '</div>';
}
get_footer();
