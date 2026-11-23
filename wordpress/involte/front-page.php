<?php
// strona glowna: uklad z podgladu, teksty edytowalne w panelu (inVolte > Strona główna - teksty)
defined('ABSPATH') || exit;
get_header();
?>

<section class="hero" id="start">
  <picture>
    <source media="(max-width:760px)" srcset="/img/dom-mob-480.webp 480w, /img/dom-mob-640.webp 640w, /img/dom-mob-780.webp 780w" sizes="100vw" width="780" height="1080">
    <img src="/img/dom-1614.webp" srcset="/img/dom-1024.webp 1024w, /img/dom-1360.webp 1360w, /img/dom-1614.webp 1614w" sizes="100vw" width="1614" height="1080" alt="Dom jednorodzinny z instalacją fotowoltaiczną na dachu" fetchpriority="high" decoding="async">
  </picture>
  <div class="wrap hero-in">
    <h1><?php iv_t('start_01'); ?></h1>
    <p class="lead"><?php iv_t('start_02'); ?></p>
    <a class="btn" href="#kontakt" data-mob-od><svg aria-hidden="true"><use href="#mk" style="color:#fff"/></svg><?php iv_t('start_04'); ?></a>
    <p class="gdzie"><?php iv_t('start_03'); ?></p>
  </div>
</section>

<section class="sek" id="oferta">
  <div class="wrap">
    <div class="glowa">
      <h2><?php iv_t('oferta_01'); ?></h2>
      <p><?php iv_t('oferta_02'); ?></p>
    </div>
    <div class="pas" id="pas">
      <article class="karta">
        <div class="fot"><img src="/img/montaz-500.webp" srcset="/img/montaz-400.webp 400w, /img/montaz-500.webp 500w" sizes="(max-width:760px) 84vw, (max-width:1180px) 46vw, 370px" width="500" height="500" loading="lazy" decoding="async" alt="Montaż modułów fotowoltaicznych na dachu"></div>
        <div class="tx"><h3><a href="<?php echo esc_url(iv_u('mikro-i-male-instalacje/')); ?>"><?php iv_t('oferta_03'); ?></a></h3><p><?php iv_t('oferta_04'); ?></p>
          <ul><li><?php iv_t('oferta_05'); ?></li><li><?php iv_t('oferta_06'); ?></li></ul></div>
      </article>
      <article class="karta">
        <div class="fot"><img src="/img/farma-600.webp" srcset="/img/farma-400.webp 400w, /img/farma-600.webp 600w, /img/farma-680.webp 680w, /img/farma-800.webp 800w, /img/farma-1200.webp 1200w" sizes="(max-width:760px) 84vw, (max-width:1180px) 46vw, 370px" width="600" height="407" loading="lazy" decoding="async" alt="Instalacja fotowoltaiczna na gruncie"></div>
        <div class="tx"><h3><a href="<?php echo esc_url(iv_u('duze-instalacje-fotowoltaiczne/')); ?>"><?php iv_t('oferta_07'); ?></a></h3><p><?php iv_t('oferta_08'); ?></p>
          <ul><li><?php iv_t('oferta_09'); ?></li><li><?php iv_t('oferta_10'); ?></li></ul></div>
      </article>
      <article class="karta">
        <div class="fot"><img src="/img/projekt-600.webp" srcset="/img/projekt-400.webp 400w, /img/projekt-600.webp 600w, /img/projekt-680.webp 680w, /img/projekt-800.webp 800w, /img/projekt-1200.webp 1200w" sizes="(max-width:760px) 84vw, (max-width:1180px) 46vw, 370px" width="600" height="369" loading="lazy" decoding="async" alt="Rząd modułów fotowoltaicznych o zachodzie słońca"></div>
        <div class="tx"><h3><a href="<?php echo esc_url(iv_u('projektowanie-i-przygotowanie-dokumentacji/')); ?>"><?php iv_t('oferta_11'); ?></a></h3><p><?php iv_t('oferta_12'); ?></p>
          <ul><li><?php iv_t('oferta_13'); ?></li><li><?php iv_t('oferta_14'); ?></li></ul></div>
      </article>
      <article class="karta">
        <div class="fot"><img src="/img/serwis-600.webp" srcset="/img/serwis-400.webp 400w, /img/serwis-600.webp 600w, /img/serwis-680.webp 680w, /img/serwis-800.webp 800w, /img/serwis-1200.webp 1200w" sizes="(max-width:760px) 84vw, (max-width:1180px) 46vw, 370px" width="600" height="400" loading="lazy" decoding="async" alt="Serwisant przy rzędzie modułów fotowoltaicznych"></div>
        <div class="tx"><h3><a href="<?php echo esc_url(iv_u('serwis-instalacji-fotowoltaicznych/')); ?>"><?php iv_t('oferta_15'); ?></a></h3><p><?php iv_t('oferta_16'); ?></p>
          <ul><li><?php iv_t('oferta_17'); ?></li><li><?php iv_t('oferta_18'); ?></li></ul></div>
      </article>
    </div>
  </div>
</section>

<section class="sek ciemna" id="serwis">
  <svg class="duzy-mk" viewBox="0 0 110 200" aria-hidden="true"><use href="#mk"/></svg>
  <div class="wrap pom">
    <div class="opis">
      <h2><?php iv_t('serwis_01'); ?></h2>
      <p><?php iv_t('serwis_02'); ?></p>
      <p class="inst"><?php iv_t('serwis_03'); ?></p>
      <a class="link-j" href="<?php echo esc_url(iv_u('serwis-instalacji-fotowoltaicznych/')); ?>"><?php iv_t('serwis_12'); ?></a>
    </div>
    <ul class="lista-pom">
      <li><span class="sl"></span><div><h3><?php iv_t('serwis_04'); ?></h3><p><?php iv_t('serwis_05'); ?></p></div></li>
      <li><span class="sl"></span><div><h3><?php iv_t('serwis_06'); ?></h3><p><?php iv_t('serwis_07'); ?></p></div></li>
      <li><span class="sl"></span><div><h3><?php iv_t('serwis_08'); ?></h3><p><?php iv_t('serwis_09'); ?></p></div></li>
      <li><span class="sl"></span><div><h3><?php iv_t('serwis_10'); ?></h3><p><?php iv_t('serwis_11'); ?></p></div></li>
    </ul>
  </div>
</section>

<section class="sek szara" id="jak">
  <div class="wrap">
    <div class="glowa">
      <h2><?php iv_t('jak_01'); ?></h2>
      <p><?php iv_t('jak_02'); ?></p>
    </div>
    <div class="kroki">
      <div class="krok"><h3><?php iv_t('jak_03'); ?></h3><p><?php iv_t('jak_04'); ?></p></div>
      <div class="krok"><h3><?php iv_t('jak_05'); ?></h3><p><?php iv_t('jak_06'); ?></p></div>
      <div class="krok"><h3><?php iv_t('jak_07'); ?></h3><p><?php iv_t('jak_08'); ?></p></div>
    </div>
    <div class="etapy">
      <div><h3><?php iv_t('jak_09'); ?></h3><a class="link-j" href="<?php echo esc_url(iv_u('projektowanie-i-przygotowanie-dokumentacji/')); ?>"><?php iv_t('jak_17'); ?></a></div>
      <ol>
        <li><?php iv_t('jak_10'); ?></li>
        <li><?php iv_t('jak_11'); ?></li>
        <li><?php iv_t('jak_12'); ?></li>
        <li><?php iv_t('jak_13'); ?></li>
        <li><?php iv_t('jak_14'); ?></li>
        <li><?php iv_t('jak_15'); ?></li>
        <li><?php iv_t('jak_16'); ?></li>
      </ol>
    </div>
  </div>
</section>

<section class="sek" id="liczymy">
  <div class="wrap licz">
    <blockquote class="cytat"><?php iv_t('liczymy_cytat'); ?>
      <footer><?php iv_t('liczymy_01'); ?></footer></blockquote>
    <div class="dane">
      <h3><?php iv_t('liczymy_02'); ?></h3>
      <ul>
        <li><?php iv_t('liczymy_03'); ?></li>
        <li><?php iv_t('liczymy_04'); ?></li>
        <li><?php iv_t('liczymy_05'); ?></li>
      </ul>
      <p class="fakt"><?php iv_t('liczymy_06'); ?></p>
      <a class="btn" href="#kontakt"><svg aria-hidden="true"><use href="#mk" style="color:#fff"/></svg><?php iv_t('liczymy_07'); ?></a>
    </div>
  </div>
</section>

<section class="sek szara" id="o-firmie">
  <div class="wrap mlyn">
    <figure>
      <img src="/img/mlyn-600.webp" width="600" height="401" loading="lazy" decoding="async" alt="Dawny młyn w Jaraczu nad Wełną">
      <figcaption><?php iv_t('o-firmie_01'); ?></figcaption>
    </figure>
    <div class="tekst">
      <h2><?php iv_t('o-firmie_02'); ?></h2>
      <p><?php iv_t('o-firmie_03'); ?></p>
      <p><?php iv_t('o-firmie_04'); ?></p>
      <p class="podpis"><?php iv_t('o-firmie_05'); ?></p>
      <a class="link-j" href="<?php echo esc_url(iv_u('o-nas/')); ?>"><?php iv_t('o-firmie_06'); ?></a>
    </div>
  </div>
</section>

<?php iv_realizacje_glowna(); ?>

<section class="sek" id="pytania">
  <div class="wrap faq">
    <div><h2><?php iv_t('pytania_01'); ?></h2><p class="wst"><?php iv_t('pytania_02'); ?></p><ul class="art"><?php iv_lista_art(); ?></ul></div>
    <div>
      <details open><summary><?php iv_t('pytania_03'); ?></summary><p><?php iv_t('pytania_04'); ?></p></details>
      <details><summary><?php iv_t('pytania_05'); ?></summary><p><?php iv_t('pytania_06'); ?></p></details>
      <details><summary><?php iv_t('pytania_07'); ?></summary><p><?php iv_t('pytania_08'); ?></p></details>
      <details><summary><?php iv_t('pytania_09'); ?></summary><p><?php iv_t('pytania_10'); ?></p></details>
      <details><summary><?php iv_t('pytania_11'); ?></summary><p><?php iv_t('pytania_12'); ?></p></details>
    </div>
  </div>
</section>

<section class="sek szara" id="kontakt">
  <div class="wrap kont">
    <div class="dk">
      <h2><?php iv_t('kontakt_01'); ?></h2>
      <p><?php iv_t('kontakt_02'); ?></p>
      <?php iv_kontakt_dl(true); iv_opinie_link(); ?>
    </div>
    <div id="formularz">
      <?php iv_formularz(); ?>
      <?php iv_mapa(); ?>
    </div>
  </div>
</section>

<?php get_footer();
